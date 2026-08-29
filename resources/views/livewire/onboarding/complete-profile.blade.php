<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50 flex items-center justify-center px-4">

    <div class="bg-white rounded-3xl shadow-xl w-full max-w-xl p-10">

        {{-- Barre de progression (texte et % retirés, seule la barre reste) --}}
        <div class="mb-10">

            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div
                    class="bg-blue-600 h-3 rounded-full transition-all duration-500 ease-out"
                    style="width: {{ $step * 33 }}%">
                </div>
            </div>

        </div>

        {{-- ================= ÉTAPE 1 : PHOTO ================= --}}
        @if ($step === 1)

            <div wire:key="step-1" class="animate-[fadeIn_0.3s_ease-in-out]">

                <h1 class="text-3xl font-bold mb-2">Bienvenue 👋</h1>
                <p class="text-gray-500 mb-8">Ajoutez une photo de profil pour que les autres vous reconnaissent.</p>

                <div class="flex flex-col items-center">

                    <div class="w-32 h-32 rounded-full bg-gray-100 border-4 border-white shadow-lg overflow-hidden flex items-center justify-center mb-6">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-14 h-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="cursor-pointer bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold px-5 py-2 rounded-xl transition">
                            Choisir une photo
                            <input type="file" wire:model="avatar" class="hidden" accept="image/*">
                        </label>

                        @if ($avatar)
                            <button
                                type="button"
                                wire:click="removeAvatar"
                                class="text-red-500 hover:text-red-700 font-semibold px-3 py-2 text-sm">
                                🗑️ Supprimer
                            </button>
                        @endif
                    </div>

                    <div wire:loading wire:target="avatar" class="text-sm text-gray-400 mt-3">
                        Chargement...
                    </div>

                    @error('avatar')
                        <span class="text-red-500 text-sm mt-3">{{ $message }}</span>
                    @enderror

                </div>

            </div>

        @endif

        {{-- ================= ÉTAPE 2 : DÉPARTEMENT ================= --}}
        @if ($step === 2)

            <div wire:key="step-2" class="animate-[fadeIn_0.3s_ease-in-out]">

                <h1 class="text-3xl font-bold mb-2">Votre parcours 🎓</h1>
                <p class="text-gray-500 mb-8">Choisissez votre département.</p>

                <div>
                    <label class="font-semibold block mb-3">Département</label>

                    <select wire:model.live="department" class="border rounded-xl w-full p-3">
                        <option value="">-- Choisir --</option>

                        @foreach ($departments as $category => $filieres)
                            <optgroup label="{{ $category }}">
                                @foreach ($filieres as $dep)
                                    <option value="{{ $dep }}">{{ $dep }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    @error('department')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                @if ($department === 'Autre')
                    <div class="mt-6">
                        <input type="text" wire:model="otherDepartment"
                               placeholder="Votre département"
                               class="border rounded-xl w-full p-3">
                        @error('otherDepartment')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

            </div>

        @endif

        {{-- ================= ÉTAPE 3 : BIO ================= --}}
        @if ($step === 3)

            <div wire:key="step-3" class="animate-[fadeIn_0.3s_ease-in-out]">

                <h1 class="text-3xl font-bold mb-2">Parlez-nous de vous ✨</h1>
                <p class="text-gray-500 mb-8">Une courte bio (facultatif).</p>

                <textarea
                    wire:model="bio"
                    rows="4"
                    maxlength="250"
                    placeholder="Étudiant(e) passionné(e) par..."
                    class="border rounded-xl w-full p-3 resize-none"></textarea>

                <div class="text-right text-xs text-gray-400 mt-1">
                    {{ strlen($bio) }}/250
                </div>

                @error('bio')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

            </div>

        @endif

        {{-- ================= NAVIGATION ================= --}}
        <div class="mt-10 flex items-center justify-between">

            @if ($step > 1)
                <button
                    wire:click="previousStep"
                    class="text-gray-500 hover:text-gray-700 font-medium px-4 py-3">
                    ← Retour
                </button>
            @else
                <span></span>
            @endif

            <div class="flex gap-3">

                @if ($step === 1)
                    {{-- Étape 1 (photo, facultative) : Passer ou Continuer selon le choix --}}
                    @if ($avatar)
                        <button
                            wire:click="save"
                            wire:loading.attr="disabled"
                            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-8 py-3 rounded-xl font-semibold transition">
                            <span wire:loading.remove>Continuer →</span>
                            <span wire:loading>...</span>
                        </button>
                    @else
                        <button
                            wire:click="skip"
                            class="text-gray-500 hover:text-gray-700 font-medium px-5 py-3">
                            Passer
                        </button>
                    @endif
                @elseif ($step === 2)
                    {{-- Étape 2 (département, obligatoire) : uniquement Continuer, pas de Passer --}}
                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-8 py-3 rounded-xl font-semibold transition">
                        <span wire:loading.remove>Continuer →</span>
                        <span wire:loading>...</span>
                    </button>
                @else
                    {{-- Étape 3 (bio, facultative) : Passer + Terminer --}}
                    <button
                        wire:click="skip"
                        class="text-gray-500 hover:text-gray-700 font-medium px-5 py-3">
                        Passer
                    </button>

                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-8 py-3 rounded-xl font-semibold transition">
                        <span wire:loading.remove>Terminer</span>
                        <span wire:loading>...</span>
                    </button>
                @endif

            </div>

        </div>

    </div>

</div>