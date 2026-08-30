<?php

namespace App\Livewire\Profile;

use App\Livewire\Actions\Logout;
use App\Mail\VerificationCodeMail;
use App\Models\Club;
use App\Models\ClubPresidencyTransfer;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\ClubPresidencyTransferProposed;
use App\Support\DepartmentList;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Edit extends Component
{
    use WithFileUploads;

    // --- Section : informations de profil ---
    public string $name = '';
    public string $email = '';
    public ?string $department = '';
    public ?string $bio = '';

    // Texte libre saisi si $department vaut "Autre" (filière hors liste).
    public ?string $otherDepartment = '';

    // Liste des départements groupés par catégorie, pour générer les <optgroup>
    // dans la vue. Remplie une seule fois à l'initialisation (voir mount()).
    public array $departments = [];

    // Nouvel avatar en attente d'upload (temporaire, pas encore sauvegardé)
    public $avatar = null;

    // --- Section : changement d'email (vérification par code) ---
    // Nouvel email en attente de confirmation (null tant qu'aucun changement n'est en cours).
    public ?string $pendingEmail = null;

    // Code à 6 chiffres saisi par l'utilisatrice pour confirmer le nouvel email.
    public string $email_verification_code = '';

    // --- Section : mot de passe ---
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // --- Section : suppression de compte ---
    public string $delete_password = '';

    // Clubs présidés par l'utilisateur AVEC au moins un successeur éligible.
    // [club_id => ['club_name' => string, 'candidates' => Collection<User>]]
    public array $clubsNeedingSuccessorForDeletion = [];

    // Clubs présidés par l'utilisateur SANS aucun successeur éligible.
    // Purement informatif dans la modale : ces clubs passeront automatiquement
    // à president_id = NULL au moment de la suppression du compte (voir
    // submitAccountDeletion()).
    // [club_id => club_name]
    public array $clubsWithoutSuccessorForDeletion = [];

    // [club_id => id du candidat choisi] (uniquement pour les clubs de $clubsNeedingSuccessorForDeletion)
    public array $selectedSuccessorsForDeletion = [];

    /**
     * Pré-remplit le formulaire avec les données actuelles de l'utilisateur connecté.
     *
     * Cas particulier du département : si la valeur actuelle en base ne
     * correspond à aucune filière de la liste (config/departments.php),
     * on bascule automatiquement le select sur "Autre" et on pré-remplit
     * le champ texte libre avec cette valeur, pour ne pas la perdre.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->bio = $user->bio;

        $this->departments = DepartmentList::grouped();

        if ($user->department && ! in_array($user->department, DepartmentList::flat(), true)) {
            $this->department = DepartmentList::AUTRE;
            $this->otherDepartment = $user->department;
        } else {
            $this->department = $user->department;
        }
    }

    /**
     * Met à jour les informations générales du profil (nom, département, bio, avatar).
     *
     * L'email est traité à part : s'il a changé, on ne l'enregistre PAS
     * directement en base. On génère un code de vérification, on l'envoie
     * au NOUVEL email, et on affiche un formulaire de saisie du code
     * (voir verifyEmailChangeCode()). L'email réel n'est mis à jour que
     * si le code saisi est correct.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'department' => ['nullable', 'string', Rule::in(DepartmentList::flat())],
            'otherDepartment' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        // Si "Autre" est sélectionné, le texte libre est obligatoire.
        // On vérifie ceci AVANT de toucher à $user, pour ne rien enregistrer
        // partiellement si cette condition échoue.
        if ($validated['department'] === DepartmentList::AUTRE && empty($this->otherDepartment)) {
            $this->addError('otherDepartment', 'Veuillez préciser votre département.');

            return;
        }

        $user->name = $validated['name'];

        $user->department = $validated['department'] === DepartmentList::AUTRE
            ? $this->otherDepartment
            : $validated['department'];

        $user->bio = $validated['bio'];

        // Upload du nouvel avatar : on supprime l'ancien fichier avant d'enregistrer le nouveau
        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        $user->save();

        // On vide le champ d'upload temporaire après sauvegarde
        $this->avatar = null;

        // Si l'email n'a pas changé, on s'arrête là : rien à vérifier.
        if ($validated['email'] === $user->email) {
            $this->dispatch('profile-updated');

            return;
        }

        // L'email a changé : on lance la vérification, on n'enregistre rien
        // sur le champ email de $user pour l'instant.
        $this->pendingEmail = $validated['email'];
        $this->email_verification_code = '';

        $code = EmailVerificationCode::generateFor($user, $this->pendingEmail, 'profile_change');

        Mail::to($this->pendingEmail)->send(new VerificationCodeMail($code->code));

        $this->dispatch('profile-updated');
    }

    /**
     * Vérifie le code saisi pour confirmer le changement d'email. Si le
     * code est correct, met enfin à jour l'email réel de l'utilisatrice.
     */
    public function verifyEmailChangeCode(): void
    {
        $user = Auth::user();

        $code = EmailVerificationCode::activeFor($user, 'profile_change');

        if (! $code) {
            $this->pendingEmail = null;

            throw ValidationException::withMessages([
                'email_verification_code' => 'Cette demande a expiré, merci de recommencer le changement d\'email.',
            ]);
        }

        $result = $code->attempt($this->email_verification_code);

        if ($result === 'blocked') {
            throw ValidationException::withMessages([
                'email_verification_code' => 'Trop de tentatives incorrectes. Réessaie dans quelques minutes.',
            ]);
        }

        if ($result === 'expired') {
            throw ValidationException::withMessages([
                'email_verification_code' => 'Ce code a expiré. Clique sur "Renvoyer le code".',
            ]);
        }

        if ($result === 'invalid') {
            throw ValidationException::withMessages([
                'email_verification_code' => 'Code incorrect.',
            ]);
        }

        // Code correct : on met enfin à jour l'email réel.
        $user->email = $code->email;

        if ($user instanceof MustVerifyEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        $code->delete();

        $this->pendingEmail = null;
        $this->email = $user->email;
        $this->email_verification_code = '';

        $this->dispatch('email-updated');
    }

    /**
     * Renvoie un nouveau code pour le changement d'email en cours
     * (avec délai anti-spam de 60 secondes, géré par le modèle).
     */
    public function resendEmailChangeCode(): void
    {
        $user = Auth::user();

        if (! $this->pendingEmail) {
            return;
        }

        if (! EmailVerificationCode::canResend($user, 'profile_change')) {
            throw ValidationException::withMessages([
                'email_verification_code' => 'Merci de patienter avant de redemander un code.',
            ]);
        }

        $code = EmailVerificationCode::generateFor($user, $this->pendingEmail, 'profile_change');

        Mail::to($this->pendingEmail)->send(new VerificationCodeMail($code->code));
    }

    /**
     * Annule le changement d'email en cours : l'email réel reste inchangé.
     */
    public function cancelEmailChange(): void
    {
        $user = Auth::user();

        EmailVerificationCode::where('user_id', $user->id)
            ->where('purpose', 'profile_change')
            ->delete();

        $this->pendingEmail = null;
        $this->email_verification_code = '';
        $this->email = $user->email;
    }

    public function deleteAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        $this->dispatch('avatar-deleted');
    }

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    /**
     * Point d'entrée du formulaire de suppression de compte.
     *
     * Règle : la suppression du compte ne bloque JAMAIS, quel que soit
     * l'état des clubs présidés par l'utilisateur (même règle que côté
     * admin dans Admin\Dashboard::confirmUserBanToggle()).
     *
     * - Pas président d'un club : suppression immédiate (inchangé).
     * - Président d'au moins un club : ferme la modale de mot de passe et
     *   ouvre la modale récapitulative (choix de successeur pour les clubs
     *   qui en ont, information pour ceux qui n'en ont pas). La suppression
     *   réelle n'a lieu qu'à la validation de cette modale, dans
     *   submitAccountDeletion().
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'delete_password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();

        $clubsAsPresident = $user->clubs()->get();

        // Pas président d'un club : suppression immédiate, comme avant.
        if ($clubsAsPresident->isEmpty()) {
            tap($user, $logout(...))->delete();

            $this->redirect('/', navigate: false);

            return;
        }

        $needingSuccessor = [];
        $withoutSuccessor = [];

        foreach ($clubsAsPresident as $club) {
            $candidates = $this->eligibleSuccessors($club, $user);

            if ($candidates->isEmpty()) {
                $withoutSuccessor[$club->id] = $club->name;
            } else {
                $needingSuccessor[$club->id] = [
                    'club_name' => $club->name,
                    'candidates' => $candidates,
                ];
            }
        }

        $this->clubsNeedingSuccessorForDeletion = $needingSuccessor;
        $this->clubsWithoutSuccessorForDeletion = $withoutSuccessor;
        $this->selectedSuccessorsForDeletion = [];

        $this->dispatch('close-modal', 'confirm-user-deletion');
        $this->dispatch('open-modal', 'account-deletion-successor');
    }

    public function cancelAccountDeletionSuccessorSelection(): void
    {
        $this->clubsNeedingSuccessorForDeletion = [];
        $this->clubsWithoutSuccessorForDeletion = [];
        $this->selectedSuccessorsForDeletion = [];
        $this->reset('delete_password');

        $this->dispatch('close-modal', 'account-deletion-successor');
    }

    /**
     * Supprime immédiatement le compte, quel que soit l'état des clubs :
     * - Pour les clubs AVEC successeur choisi : crée une demande de transfert
     *   de présidence (pending) et notifie le successeur choisi. Il pourra
     *   l'accepter ou la refuser plus tard (voir Notifications\Bell) ; ça
     *   n'empêche pas la suppression du compte de se faire maintenant.
     * - Pour les clubs SANS successeur : passent directement à
     *   president_id = NULL, en attente d'intervention administrative.
     *
     * Logique jumelle de Admin\Dashboard::submitUserBan(), avec suppression
     * + déconnexion réelle au lieu d'un bannissement.
     */
    public function submitAccountDeletion(Logout $logout): void
    {
        $user = Auth::user();

        foreach ($this->clubsNeedingSuccessorForDeletion as $clubId => $data) {
            $selectedId = $this->selectedSuccessorsForDeletion[$clubId] ?? null;

            $validIds = $data['candidates']->pluck('id')->all();

            abort_if(! $selectedId || ! in_array((int) $selectedId, $validIds, true), 422);

            $transfer = ClubPresidencyTransfer::create([
                'club_id' => $clubId,
                'current_president_id' => $user->id,
                'proposed_president_id' => $selectedId,
                'status' => 'pending',
                'initiated_by' => 'self',
                'reason' => 'account_deletion',
            ]);

            User::find($selectedId)->notify(new ClubPresidencyTransferProposed($transfer));
        }

        foreach (array_keys($this->clubsWithoutSuccessorForDeletion) as $clubId) {
            Club::where('id', $clubId)->update(['president_id' => null]);
        }

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: false);
    }

    /**
     * Retourne les membres éligibles pour devenir président d'un club :
     * adhésion acceptée, profil complété, compte non banni, hors président actuel.
     *
     * Logique identique à Admin\Dashboard::eligibleSuccessors(), dupliquée ici
     * volontairement car les deux composants restent indépendants.
     */
    private function eligibleSuccessors(Club $club, User $president)
    {
        return User::whereHas('clubMemberships', function ($query) use ($club) {
                $query->where('club_id', $club->id)
                    ->where('status', 'accepted');
            })
            ->where('id', '!=', $president->id)
            ->where('profile_completed', true)
            ->where('is_banned', false)
            ->get();
    }

    public function render()
    {
        return view('livewire.profile.edit');
    }
}