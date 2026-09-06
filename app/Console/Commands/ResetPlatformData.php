<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubPost;
use App\Models\ClubPresidencyTransfer;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Commande de nettoyage à usage UNIQUE, à lancer manuellement en terminal
 * après le déploiement, avant le vrai lancement auprès des utilisateurs.
 *
 * Supprime : tous les utilisateurs SAUF les super admins, tous les clubs,
 * tous les événements, et toutes les données qui en dépendent (posts,
 * adhésions, demandes de participation, transferts de présidence,
 * notifications), ainsi que les fichiers physiques associés (avatars,
 * logos, bannières, images de posts/événements).
 *
 * Volontairement PAS accessible depuis l'interface web (pas de bouton
 * admin) : seule une personne ayant un accès au serveur/terminal peut
 * l'exécuter, avec confirmation explicite obligatoire.
 *
 * Usage : php artisan app:reset-platform-data
 */
class ResetPlatformData extends Command
{
    protected $signature = 'app:reset-platform-data';

    protected $description = 'Supprime tous les comptes (sauf super admin), tous les clubs et tous les événements. Irréversible.';

    public function handle(): int
    {
        // Garde-fou : on ne lance rien s'il n'y a aucun super admin en base,
        // sinon la plateforme se retrouverait totalement vide, sans personne
        // pour s'y reconnecter derrière.
        $superAdminCount = User::where('role', 'superAdmin')->count();

        if ($superAdminCount === 0) {
            $this->error('Aucun super admin trouvé en base. Opération annulée par sécurité.');

            return self::FAILURE;
        }

        $this->warn('⚠️  Cette action va supprimer DÉFINITIVEMENT :');
        $this->line('   - Tous les comptes utilisateurs SAUF les super admins ('.$superAdminCount.' conservé(s))');
        $this->line('   - Tous les clubs et leurs fichiers (logos, bannières)');
        $this->line('   - Tous les événements et leurs images');
        $this->line('   - Toutes les publications, adhésions, demandes de participation, transferts de présidence');
        $this->line('   - Toutes les notifications');
        $this->newLine();

        if (! $this->confirm('Es-tu sûre de vouloir continuer ? Cette action est IRRÉVERSIBLE.')) {
            $this->info('Opération annulée.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            // 1. Notifications : vidées entièrement. Après ce nettoyage, les
            // anciennes notifications pointeraient de toute façon vers des
            // clubs/utilisateurs/événements qui n'existent plus.
            DB::table('notifications')->truncate();
            $this->info('✓ Notifications supprimées.');

            // 2. Publications de club : on supprime d'abord les fichiers
            // image un par un (une suppression en masse par requête ne
            // permettrait pas de savoir quels fichiers physiques effacer).
            ClubPost::chunk(100, function ($posts) {
                foreach ($posts as $post) {
                    if ($post->image) {
                        Storage::disk('public')->delete($post->image);
                    }
                }
            });
            ClubPost::query()->delete();
            $this->info('✓ Publications de club supprimées.');

            // 3. Demandes de participation aux événements (pas de fichier associé)
            EventRegistration::query()->delete();
            $this->info('✓ Demandes de participation aux événements supprimées.');

            // 4. Adhésions aux clubs (pas de fichier associé)
            ClubMembership::query()->delete();
            $this->info('✓ Adhésions aux clubs supprimées.');

            // 5. Transferts de présidence en attente ou passés (pas de fichier associé)
            ClubPresidencyTransfer::query()->delete();
            $this->info('✓ Transferts de présidence supprimés.');

            // 6. Événements : soft delete activé, donc withTrashed() pour
            // inclure aussi ceux déjà soft-deleted, puis forceDelete() pour
            // une suppression réelle en base (un simple delete() laisserait
            // la ligne, juste marquée supprimée).
            Event::withTrashed()->chunk(100, function ($events) {
                foreach ($events as $event) {
                    if ($event->image) {
                        Storage::disk('public')->delete($event->image);
                    }
                    $event->forceDelete();
                }
            });
            $this->info('✓ Événements supprimés.');

            // 7. Clubs : même logique, soft delete + forceDelete().
            Club::withTrashed()->chunk(100, function ($clubs) {
                foreach ($clubs as $club) {
                    if ($club->logo) {
                        Storage::disk('public')->delete($club->logo);
                    }
                    if ($club->banner) {
                        Storage::disk('public')->delete($club->banner);
                    }
                    $club->forceDelete();
                }
            });
            $this->info('✓ Clubs supprimés.');

            // 8. Utilisateurs, sauf les super admins. User n'a pas de soft
            // delete (confirmé dans le modèle), donc delete() est déjà définitif.
            User::where('role', '!=', 'superAdmin')->chunk(100, function ($users) {
                foreach ($users as $user) {
                    if ($user->avatar) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $user->delete();
                }
            });
            $this->info('✓ Comptes utilisateurs supprimés (super admins conservés).');
        });

        $this->newLine();
        $this->info('✅ Nettoyage terminé. La plateforme ne contient plus que le(s) compte(s) super admin.');

        return self::SUCCESS;
    }
}