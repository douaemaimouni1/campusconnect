<div>
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mon profil') }}
        </h2>
    </div>

    <div
        class="max-w-3xl space-y-6"
        x-data="{ activeTab: @js($errors->has('current_password') || $errors->has('password') || $errors->has('delete_password') ? 'security' : 'info') }"
    >

        {{-- ================= ONGLETS ================= --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex gap-6" aria-label="Onglets du profil">
                <button
                    type="button"
                    @click="activeTab = 'info'"
                    :class="activeTab === 'info' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                    {{ __('Informations') }}
                </button>
                <button
                    type="button"
                    @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">
                    {{ __('Sécurité') }}
                </button>
            </nav>
        </div>

        {{-- ================= ONGLET 1 : INFORMATIONS ================= --}}
        <section x-show="activeTab === 'info'" x-cloak class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <header>
                <h3 class="text-lg font-medium text-gray-900">
                    {{ __('Informations du profil') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __("Mets à jour ton nom, ton email, ton département, ta bio et ta photo de profil.") }}
                </p>
            </header>

            <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">

                {{-- Avatar --}}
                <div class="flex items-center gap-4">
                    <div>
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="w-20 h-20 rounded-full object-cover">
                        @elseif (auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-20 h-20 rounded-full object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xl font-semibold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <x-input-label for="avatar" :value="__('Photo de profil')" />
                        <input type="file" wire:model="avatar" id="avatar" class="mt-1 block w-full text-sm text-gray-600">
                        <div wire:loading wire:target="avatar" class="text-sm text-gray-500 mt-1">
                            {{ __('Chargement de l\'image...') }}
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

                        @if (auth()->user()->avatar)
                            <button
                                type="button"
                                wire:click="deleteAvatar"
                                wire:confirm="Supprimer ta photo de profil actuelle ?"
                                class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
                                {{ __('Supprimer la photo actuelle') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div>
                    <x-input-label for="name" :value="__('Nom')" />
                    <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" :disabled="(bool) $pendingEmail" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail() && ! $pendingEmail)
                        <p class="text-sm mt-2 text-gray-800">
                            {{ __('Ton adresse email n\'est pas vérifiée.') }}
                        </p>
                    @endif

                    @if ($pendingEmail)
                        <div wire:key="email-verification-box" class="mt-3 p-4 border border-indigo-200 bg-indigo-50 rounded-md">
                            <p class="text-sm text-gray-700 mb-3">
                                {{ __('Un code à 6 chiffres a été envoyé à') }} <strong>{{ $pendingEmail }}</strong>.
                                {{ __('Saisis-le pour confirmer ce nouvel email.') }}
                            </p>

                            <x-input-label for="email_verification_code" :value="__('Code de vérification')" />
                            <x-text-input
                                wire:model="email_verification_code"
                                id="email_verification_code"
                                type="text"
                                inputmode="numeric"
                                maxlength="6"
                                class="mt-1 block w-full max-w-xs text-center tracking-[0.5em] text-xl"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('email_verification_code')" />

                            <div class="flex items-center gap-4 mt-3">
                                <x-secondary-button type="button" wire:click="verifyEmailChangeCode">
                                    {{ __('Vérifier') }}
                                </x-secondary-button>

                                <button type="button" wire:click="resendEmailChangeCode" class="text-sm text-indigo-600 hover:text-indigo-800 underline">
                                    {{ __('Renvoyer le code') }}
                                </button>

                                <button type="button" wire:click="cancelEmailChange" class="text-sm text-gray-500 hover:text-gray-700 underline">
                                    {{ __('Annuler') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    <x-input-label for="department" :value="__('Département')" />
                    <x-text-input wire:model="department" id="department" name="department" type="text" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('department')" />
                </div>

                <div>
                    <x-input-label for="bio" :value="__('Bio')" />
                    <textarea wire:model="bio" id="bio" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>

                    <x-action-message class="me-3" on="profile-updated">
                        {{ __('Enregistré.') }}
                    </x-action-message>

                    <x-action-message class="me-3" on="email-updated">
                        {{ __('Email mis à jour.') }}
                    </x-action-message>
                </div>
            </form>
        </section>

        {{-- ================= ONGLET 2 : SÉCURITÉ (mot de passe + suppression de compte) ================= --}}
        <div x-show="activeTab === 'security'" x-cloak class="space-y-6">

            <section class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ __('Changer le mot de passe') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Utilise un mot de passe long et unique pour sécuriser ton compte.') }}
                    </p>
                </header>

                <form wire:submit="updatePassword" class="mt-6 space-y-6">
                    <div>
                        <x-input-label for="current_password" :value="__('Mot de passe actuel')" />
                        <x-text-input wire:model="current_password" id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Nouveau mot de passe')" />
                        <x-text-input wire:model="password" id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                        <x-text-input wire:model="password_confirmation" id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>

                        <x-action-message class="me-3" on="password-updated">
                            {{ __('Enregistré.') }}
                        </x-action-message>
                    </div>
                </form>
            </section>

            {{-- ================= SUPPRESSION DE COMPTE (uniquement dans l'onglet Sécurité) ================= --}}
            <section class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-6">
                <header>
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ __('Supprimer le compte') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Une fois ton compte supprimé, toutes tes données seront définitivement effacées. Télécharge tout ce que tu souhaites garder avant de continuer.') }}
                    </p>
                </header>

                <x-danger-button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                >
                    {{ __('Supprimer mon compte') }}
                </x-danger-button>

                <x-modal name="confirm-user-deletion" :show="$errors->has('delete_password')" focusable>
                    <form wire:submit="deleteUser" class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ __('Es-tu sûr(e) de vouloir supprimer ton compte ?') }}
                        </h3>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Cette action est irréversible. Entre ton mot de passe pour confirmer.') }}
                        </p>

                        <div class="mt-6">
                            <x-input-label for="delete_password" value="{{ __('Mot de passe') }}" class="sr-only" />

                            <x-text-input
                                wire:model="delete_password"
                                id="delete_password"
                                name="delete_password"
                                type="password"
                                class="mt-1 block w-3/4"
                                placeholder="{{ __('Mot de passe') }}"
                            />

                            <x-input-error :messages="$errors->get('delete_password')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Annuler') }}
                            </x-secondary-button>

                            <x-danger-button class="ms-3">
                                {{ __('Supprimer mon compte') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>

                {{-- Modale : suppression bloquée (aucun successeur éligible pour au moins un club) --}}
                <x-modal name="account-deletion-blocked" focusable>
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            🚫 {{ __('Suppression impossible pour le moment') }}
                        </h3>

                        <p class="mt-2 text-sm text-gray-600">
                            {{ __("Tu es président du/des club(s) suivant(s), qui n'ont aucun autre membre éligible (adhésion acceptée, profil complété et compte non banni) pour reprendre la présidence :") }}
                        </p>

                        <ul class="list-disc list-inside text-sm text-gray-700 mt-2 mb-2">
                            @foreach ($blockingClubsForDeletion as $clubName)
                                <li>{{ $clubName }}</li>
                            @endforeach
                        </ul>

                        <p class="mt-2 text-sm text-gray-600">
                            {{ __("Pour supprimer ton compte, un administrateur doit d'abord intervenir sur ce(s) club(s) (ajouter un membre éligible ou le(s) supprimer).") }}
                        </p>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button
                                wire:click="cancelBlockedDeletion"
                                x-on:click="$dispatch('close')"
                            >
                                {{ __('Fermer') }}
                            </x-secondary-button>
                        </div>
                    </div>
                </x-modal>

                {{-- Modale : choix du/des successeur(s) avant suppression du compte --}}
                <x-modal name="account-deletion-successor" focusable>
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            👤 {{ __('Choisir ton/tes successeur(s)') }}
                        </h3>

                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Tu es président du/des club(s) ci-dessous. Choisis un successeur pour chacun : ton compte sera supprimé automatiquement dès que le(s) successeur(s) auront accepté.') }}
                        </p>

                        <div class="mt-4 space-y-4">
                            @foreach ($clubsNeedingSuccessorForDeletion as $clubId => $data)
                                <div class="border rounded-md p-3">
                                    <p class="font-medium text-gray-800 text-sm mb-2">{{ $data['club_name'] }}</p>
                                    <div class="space-y-1">
                                        @foreach ($data['candidates'] as $candidate)
                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                <input
                                                    type="radio"
                                                    wire:model="selectedSuccessorsForDeletion.{{ $clubId }}"
                                                    value="{{ $candidate->id }}"
                                                >
                                                {{ $candidate->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button
                                wire:click="cancelAccountDeletionSuccessorSelection"
                                x-on:click="$dispatch('close')"
                            >
                                {{ __('Annuler') }}
                            </x-secondary-button>

                            <x-primary-button
                                wire:click="submitAccountDeletionSuccessor"
                                class="ms-3"
                            >
                                {{ __('Proposer le(s) transfert(s)') }}
                            </x-primary-button>
                        </div>
                    </div>
                </x-modal>
            </section>

        </div>

    </div>
</div>