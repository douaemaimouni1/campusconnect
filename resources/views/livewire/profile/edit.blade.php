<div>
    <div
        class="max-w-3xl mx-auto space-y-4"
        x-data="{ activeTab: @js($errors->has('current_password') || $errors->has('password') || $errors->has('delete_password') ? 'security' : 'info') }"
    >

        {{-- ================= ONGLETS ================= --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex gap-6" aria-label="Onglets du profil">
                <button
                    type="button"
                    @click="activeTab = 'info'"
                    :class="activeTab === 'info' ? 'border-pine-500 text-pine-600' : 'border-transparent text-muted-500 hover:text-ink hover:border-gray-300'"
                    class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition">
                    {{ __('Informations') }}
                </button>
                <button
                    type="button"
                    @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'border-pine-500 text-pine-600' : 'border-transparent text-muted-500 hover:text-ink hover:border-gray-300'"
                    class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition">
                    {{ __('Sécurité') }}
                </button>
            </nav>
        </div>

        {{-- ================= ONGLET 1 : INFORMATIONS ================= --}}
        <section x-show="activeTab === 'info'" x-cloak class="bg-white shadow sm:rounded-lg p-4 sm:p-6 space-y-4">

            <header>
                <h3 class="font-serif text-base font-bold text-ink flex items-center gap-2">
                    <x-lucide-user class="w-4 h-4 text-pine-600" />
                    {{ __('Informations du profil') }}
                </h3>
                <p class="mt-0.5 text-sm text-muted-500">
                    {{ __("Mets à jour ton nom, ton email, ton département, ta bio et ta photo de profil.") }}
                </p>
            </header>

            <form wire:submit="updateProfileInformation" class="space-y-4">

                {{-- Avatar --}}
                <div class="flex items-center gap-3">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="w-12 h-12 rounded-full object-cover shrink-0">
                    @elseif (auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-12 h-12 rounded-full object-cover shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-full bg-pine-50 flex items-center justify-center text-pine-600 text-base font-semibold shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <label for="avatar" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50 text-ink">
                            <x-lucide-upload class="w-3.5 h-3.5" />
                            {{ __('Changer la photo') }}
                        </label>
                        <input type="file" wire:model="avatar" id="avatar" class="hidden">
                        <div wire:loading wire:target="avatar" class="text-xs text-muted-500 mt-1">
                            {{ __('Chargement de l\'image...') }}
                        </div>
                        <x-input-error class="mt-1" :messages="$errors->get('avatar')" />

                        @if (auth()->user()->avatar)
                            <button
                                type="button"
                                wire:click="deleteAvatar"
                                wire:confirm="Supprimer ta photo de profil actuelle ?"
                                class="ml-2 text-xs text-red-600 hover:text-red-800 underline">
                                {{ __('Supprimer') }}
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Nom + Email côte à côte --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" :value="__('Nom')" class="mb-1" />
                        <x-text-input wire:model="name" id="name" name="name" type="text" class="block w-full" required autocomplete="name" />
                        <x-input-error class="mt-1" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" class="mb-1" />
                        <x-text-input wire:model="email" id="email" name="email" type="email" class="block w-full" required autocomplete="username" :disabled="(bool) $pendingEmail" />
                        <x-input-error class="mt-1" :messages="$errors->get('email')" />

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail() && ! $pendingEmail)
                            <p class="text-xs mt-1 text-ink">
                                {{ __('Ton adresse email n\'est pas vérifiée.') }}
                            </p>
                        @endif
                    </div>
                </div>

                @if ($pendingEmail)
                    <div wire:key="email-verification-box" class="p-3 border border-pine-200 bg-pine-50 rounded-md">
                        <p class="text-sm text-muted-500 mb-2">
                            {{ __('Un code à 6 chiffres a été envoyé à') }} <strong>{{ $pendingEmail }}</strong>.
                            {{ __('Saisis-le pour confirmer ce nouvel email.') }}
                        </p>

                        <x-input-label for="email_verification_code" :value="__('Code de vérification')" class="mb-1" />
                        <x-text-input
                            wire:model="email_verification_code"
                            id="email_verification_code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            class="block w-full max-w-xs text-center tracking-[0.5em] text-xl"
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('email_verification_code')" />

                        <div class="flex items-center gap-4 mt-2">
                            <x-secondary-button type="button" wire:click="verifyEmailChangeCode">
                                {{ __('Vérifier') }}
                            </x-secondary-button>

                            <button type="button" wire:click="resendEmailChangeCode" class="text-sm text-pine-600 hover:text-pine-800 underline">
                                {{ __('Renvoyer le code') }}
                            </button>

                            <button type="button" wire:click="cancelEmailChange" class="text-sm text-muted-500 hover:text-ink underline">
                                {{ __('Annuler') }}
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Département + Bio côte à côte --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="department" :value="__('Département')" class="mb-1" />

                        <select wire:model.live="department" id="department" name="department"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-pine-500 focus:ring-pine-500">
                            <option value="">-- Choisir --</option>

                            @foreach ($departments as $category => $filieres)
                                <optgroup label="{{ $category }}">
                                    @foreach ($filieres as $dep)
                                        <option value="{{ $dep }}">{{ $dep }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <x-input-error class="mt-1" :messages="$errors->get('department')" />

                        @if ($department === 'Autre')
                            <div class="mt-2">
                                <x-input-label for="otherDepartment" :value="__('Précisez votre département')" class="mb-1" />
                                <x-text-input wire:model="otherDepartment" id="otherDepartment" name="otherDepartment" type="text" class="block w-full" />
                                <x-input-error class="mt-1" :messages="$errors->get('otherDepartment')" />
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-input-label for="bio" :value="__('Bio')" class="mb-1" />
                        <textarea wire:model="bio" id="bio" rows="2" class="block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        <x-input-error class="mt-1" :messages="$errors->get('bio')" />
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
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

        {{-- ================= ONGLET 2 : SÉCURITÉ ================= --}}
        <div x-show="activeTab === 'security'" x-cloak class="space-y-4">

            <section class="p-4 sm:p-6 bg-white shadow sm:rounded-lg">
                <header>
                    <h3 class="font-serif text-base font-bold text-ink flex items-center gap-2">
                        <x-lucide-lock class="w-4 h-4 text-pine-600" />
                        {{ __('Changer le mot de passe') }}
                    </h3>
                    <p class="mt-0.5 text-sm text-muted-500">
                        {{ __('Utilise un mot de passe long et unique pour sécuriser ton compte.') }}
                    </p>
                </header>

                <form wire:submit="updatePassword" class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="current_password" :value="__('Mot de passe actuel')" class="mb-1" />
                            <x-text-input wire:model="current_password" id="current_password" name="current_password" type="password" class="block w-full" autocomplete="current-password" />
                            <x-input-error class="mt-1" :messages="$errors->get('current_password')" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('Nouveau mot de passe')" class="mb-1" />
                            <x-text-input wire:model="password" id="password" name="password" type="password" class="block w-full" autocomplete="new-password" />
                            <x-input-error class="mt-1" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" class="mb-1" />
                            <x-text-input wire:model="password_confirmation" id="password_confirmation" name="password_confirmation" type="password" class="block w-full" autocomplete="new-password" />
                            <x-input-error class="mt-1" :messages="$errors->get('password_confirmation')" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>

                        <x-action-message class="me-3" on="password-updated">
                            {{ __('Enregistré.') }}
                        </x-action-message>
                    </div>
                </form>
            </section>

            {{-- ================= SUPPRESSION DE COMPTE ================= --}}
            <section class="p-4 sm:p-6 bg-white shadow sm:rounded-lg space-y-4">
                <header>
                    <h3 class="font-serif text-base font-bold text-ink flex items-center gap-2">
                        <x-lucide-trash-2 class="w-4 h-4 text-red-500" />
                        {{ __('Supprimer le compte') }}
                    </h3>
                    <p class="mt-0.5 text-sm text-muted-500">
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
                        <h3 class="text-lg font-medium text-ink">
                            {{ __('Es-tu sûr(e) de vouloir supprimer ton compte ?') }}
                        </h3>

                        <p class="mt-1 text-sm text-muted-500">
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

                <x-modal name="account-deletion-successor" focusable>
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-ink flex items-center gap-2">
                            <x-lucide-crown class="w-4 h-4 text-pine-600" />
                            {{ __('Suppression de ton compte de président') }}
                        </h3>

                        <p class="mt-2 text-sm text-muted-500">
                            {{ __('Ton compte va être supprimé immédiatement après validation.') }}
                        </p>

                        @if (count($clubsNeedingSuccessorForDeletion) > 0)
                            <p class="text-sm font-medium text-ink mt-4 mb-1">
                                {{ __('Clubs avec successeur à choisir :') }}
                            </p>
                            <p class="text-sm text-muted-500 mb-2">
                                {{ __('Choisis un successeur pour chacun : une demande de transfert de présidence lui sera envoyée (le club reste actif en attendant sa réponse).') }}
                            </p>
                            <div class="space-y-4">
                                @foreach ($clubsNeedingSuccessorForDeletion as $clubId => $data)
                                    <div class="border rounded-md p-3">
                                        <p class="font-medium text-ink text-sm mb-2">{{ $data['club_name'] }}</p>
                                        <div class="space-y-1">
                                            @foreach ($data['candidates'] as $candidate)
                                                <label class="flex items-center gap-2 text-sm text-ink">
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
                        @endif

                        @if (count($clubsWithoutSuccessorForDeletion) > 0)
                            <p class="text-sm font-medium text-ink mt-4 mb-1">
                                {{ __('Clubs sans successeur disponible :') }}
                            </p>
                            <p class="text-sm text-muted-500 mb-2">
                                {{ __('Ces clubs seront immédiatement sans président et nécessiteront une intervention administrative.') }}
                            </p>
                            <ul class="list-disc list-inside text-sm text-muted-500 bg-gray-50 border rounded-md p-3">
                                @foreach ($clubsWithoutSuccessorForDeletion as $clubName)
                                    <li>{{ $clubName }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button
                                wire:click="cancelAccountDeletionSuccessorSelection"
                                x-on:click="$dispatch('close')"
                            >
                                {{ __('Annuler') }}
                            </x-secondary-button>

                            <x-danger-button
                                wire:click="submitAccountDeletion"
                                class="ms-3"
                            >
                                {{ __('Confirmer la suppression') }}
                            </x-danger-button>
                        </div>
                    </div>
                </x-modal>
            </section>

        </div>

    </div>
</div>