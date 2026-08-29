<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'code',
        'purpose',
        'attempts',
        'expires_at',
        'blocked_until',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'blocked_until' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Génère un nouveau code à 6 chiffres pour un utilisateur, un email et
     * un usage donnés. Supprime les anciens codes actifs du même contexte
     * avant d'en créer un nouveau, pour n'avoir jamais qu'un seul code
     * valide à la fois pour un même couple utilisateur/usage.
     */
    public static function generateFor(User $user, string $email, string $purpose): self
    {
        static::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->delete();

        return static::create([
            'user_id' => $user->id,
            'email' => $email,
            'code' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'purpose' => $purpose,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'last_sent_at' => now(),
        ]);
    }

    /**
     * Récupère le code actif (le plus récent) pour un utilisateur et un
     * usage donnés, s'il existe.
     */
    public static function activeFor(User $user, string $purpose): ?self
    {
        return static::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest()
            ->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isBlocked(): bool
    {
        return $this->blocked_until && $this->blocked_until->isFuture();
    }

    /**
     * Vérifie le code saisi. Retourne une des 3 chaînes :
     * 'success', 'invalid', 'expired', ou 'blocked'.
     * Incrémente le compteur de tentatives et bloque temporairement
     * après 3 essais incorrects.
     */
    public function attempt(string $submittedCode): string
    {
        if ($this->isBlocked()) {
            return 'blocked';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->code !== $submittedCode) {
            $this->increment('attempts');

            if ($this->attempts >= 3) {
                $this->update(['blocked_until' => now()->addMinutes(2)]);
            }

            return 'invalid';
        }

        return 'success';
    }

    /**
     * Indique si on peut renvoyer un nouveau code (délai anti-spam de
     * 60 secondes depuis le dernier envoi).
     */
    public static function canResend(User $user, string $purpose): bool
    {
        $lastCode = static::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest()
            ->first();

        return ! $lastCode || ! $lastCode->last_sent_at || $lastCode->last_sent_at->lte(now()->subSeconds(60));
    }
}