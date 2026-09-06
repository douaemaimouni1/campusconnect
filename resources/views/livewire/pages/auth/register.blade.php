<?php

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public int $step = 1;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $code = '';

    /**
     * Clé de cache utilisée pour stocker les données en attente de
     * vérification (données du compte + code + tentatives + dates).
     */
    protected function cacheKey(): string
    {
        return 'pending_registration:' . strtolower($this->email);
    }

    /**
     * Étape 1 : valide le formulaire, stocke les données en cache
     * (PAS en base), génère un code, l'envoie par email, passe à l'étape 2.
     *
     * Important : toutes les dates sont stockées en chaînes ISO (pas en
     * objets Carbon), pour éviter les soucis de désérialisation selon le
     * driver de cache utilisé (ex: driver "database").
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey(), [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'code' => $code,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
            'blocked_until' => null,
            'last_sent_at' => now()->toIso8601String(),
        ], now()->addMinutes(15));

        Mail::to($validated['email'])->send(new VerificationCodeMail($code));

        $this->step = 2;
    }

    /**
     * Étape 2 : vérifie le code saisi. Si correct, crée réellement le
     * compte à partir des données en cache, connecte l'utilisateur,
     * et redirige vers l'onboarding.
     */
    public function verifyCode(): void
    {
        $data = Cache::get($this->cacheKey());

        if (! $data) {
            throw ValidationException::withMessages([
                'form.code' => 'Votre session a expiré, veuillez recommencer l\'inscription.',
            ]);
        }

        if ($data['blocked_until'] && now()->lt(Carbon::parse($data['blocked_until']))) {
            throw ValidationException::withMessages([
                'form.code' => 'Trop de tentatives incorrectes. Réessayez dans quelques minutes.',
            ]);
        }

        if (now()->gt(Carbon::parse($data['expires_at']))) {
            throw ValidationException::withMessages([
                'form.code' => 'Ce code a expiré. Cliquez sur "Renvoyer le code".',
            ]);
        }

        if ($this->code !== $data['code']) {
            $data['attempts']++;

            if ($data['attempts'] >= 3) {
                $data['blocked_until'] = now()->addMinutes(2)->toIso8601String();
            }

            Cache::put($this->cacheKey(), $data, now()->addMinutes(15));

            throw ValidationException::withMessages([
                'form.code' => 'Code incorrect.',
            ]);
        }

        // Code correct : on crée enfin le vrai compte.
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password_hash'],
        ]);

        Cache::forget($this->cacheKey());

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('onboarding', absolute: false), navigate: true);
    }

    /**
     * Renvoie un nouveau code (avec délai anti-spam de 60 secondes).
     */
    public function resendCode(): void
    {
        $data = Cache::get($this->cacheKey());

        if (! $data) {
            $this->step = 1;
            return;
        }

        if ($data['last_sent_at'] && now()->lt(Carbon::parse($data['last_sent_at'])->addSeconds(60))) {
            throw ValidationException::withMessages([
                'form.code' => 'Merci de patienter avant de redemander un code.',
            ]);
        }

        $newCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $data['code'] = $newCode;
        $data['attempts'] = 0;
        $data['blocked_until'] = null;
        $data['expires_at'] = now()->addMinutes(10)->toIso8601String();
        $data['last_sent_at'] = now()->toIso8601String();

        Cache::put($this->cacheKey(), $data, now()->addMinutes(15));

        Mail::to($data['email'])->send(new VerificationCodeMail($newCode));
    }

    public function backToForm(): void
    {
        $this->step = 1;
        $this->code = '';
    }
}; ?>

<div>
    @if ($step === 1)
        <div class="mb-4">
            <h2 class="font-serif text-xl font-bold text-ink">Crée ton compte</h2>
        </div>

        <form wire:submit="register">
            <!-- Name -->
            <div>
                <x-input-label for="name" value="Nom" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4" x-data="{ value: @entangle('email'), touched: false }">
                <x-input-label for="email" value="Email" />
                <x-text-input
                    wire:model="email"
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    required
                    autocomplete="username"
                    x-on:input="touched = true"
                />

                <p
                    x-show="touched && value.length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)"
                    x-cloak
                    class="text-red-500 text-sm mt-1"
                >
                    Format d'email invalide (exemple : nom@domaine.com).
                </p>

                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4" x-data="{ show: false, length: 0 }">
                <x-input-label for="password" value="Mot de passe" />

                <div class="relative">
                    <input
                        wire:model="password"
                        id="password"
                        :type="show ? 'text' : 'password'"
                        x-on:input="length = $event.target.value.length"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="border-gray-300 focus:border-pine-500 focus:ring-pine-500 rounded-md shadow-sm block mt-1 w-full pr-10"
                    >

                    <button type="button" x-on:click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-500 hover:text-ink mt-1" tabindex="-1">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                <p x-show="length > 0 && length < 8" x-cloak class="text-red-500 text-sm mt-1">
                    Le mot de passe doit contenir au moins 8 caractères.
                </p>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4" x-data="{ show: false }">
                <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />

                <div class="relative">
                    <input
                        wire:model="password_confirmation"
                        id="password_confirmation"
                        :type="show ? 'text' : 'password'"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="border-gray-300 focus:border-pine-500 focus:ring-pine-500 rounded-md shadow-sm block mt-1 w-full pr-10"
                    >

                    <button type="button" x-on:click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-500 hover:text-ink mt-1" tabindex="-1">
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-muted-500 hover:text-ink rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pine-500" href="{{ route('login') }}">
                    Déjà inscrit(e) ?
                </a>

                <x-primary-button class="ms-4">
                    S'inscrire
                </x-primary-button>
            </div>
        </form>
    @else
        <div wire:key="verify-step">
            <h2 class="font-serif text-lg font-bold text-ink mb-2">Vérifiez votre email</h2>
            <p class="text-sm text-muted-500 mb-6">
                Un code à 6 chiffres a été envoyé à <strong>{{ $email }}</strong>. Saisissez-le ci-dessous pour finaliser votre inscription.
            </p>

            <form wire:submit="verifyCode">
                <x-input-label for="code" value="Code de vérification" />
                <x-text-input
                    wire:model="code"
                    id="code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    class="block mt-1 w-full text-center tracking-[0.5em] text-xl"
                    autofocus
                />
                <x-input-error :messages="$errors->get('form.code')" class="mt-2" />

                <div class="flex items-center justify-between mt-6">
                    <button type="button" wire:click="backToForm" class="text-sm text-muted-500 hover:text-ink underline">
                        ← Retour
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="resendCode" class="text-sm text-pine-600 hover:text-pine-800 underline">
                            Renvoyer le code
                        </button>

                        <x-primary-button>
                            Vérifier
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>