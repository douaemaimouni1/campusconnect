<div class="{{ $embedded ? 'px-6 py-4' : 'max-w-xl mx-auto py-8' }}">

    @unless ($embedded)
        <h1 class="font-serif text-2xl font-bold text-ink mb-6">
            {{ $club ? 'Modifier le club' : 'Créer un club' }}
        </h1>
    @endunless

    @if(session()->has('success'))
        <div class="bg-pine-50 text-pine-700 p-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @php
        $categoryBadgeClasses = [
            'pine'       => 'bg-pine-50 text-pine-700',
            'amber'      => 'bg-amber-50 text-amber-700',
            'terracotta' => 'bg-terracotta-50 text-terracotta-700',
            'prune'      => 'bg-prune-50 text-prune-700',
            'ardoise'    => 'bg-ardoise-50 text-ardoise-700',
        ];
    @endphp

    <form wire:submit="save" class="{{ $embedded ? 'space-y-3' : 'bg-white rounded-2xl shadow p-8 space-y-3' }}">

        {{-- Bannière + Logo façon "photo de couverture", version ultra-compacte --}}
        <div class="relative mb-7">

            <div class="h-20 rounded-xl overflow-hidden bg-pine-700 relative">
                @if ($banner)
                    <img src="{{ $banner->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif ($existingBannerUrl)
                    <img src="{{ $existingBannerUrl }}" class="w-full h-full object-cover">
                @endif

                <label for="banner-upload" class="absolute inset-0 cursor-pointer">
                    <span class="absolute bottom-2 right-2 flex items-center gap-1 bg-white/90 text-ink text-xs font-semibold px-2 py-1 rounded-lg shadow">
                        <x-lucide-camera class="w-3.5 h-3.5" />
                        Changer
                    </span>
                </label>
                <input id="banner-upload" type="file" wire:model="banner" accept="image/*" class="hidden">
            </div>

            <div class="absolute -bottom-5 left-6">
                <div class="w-14 h-14 rounded-full overflow-hidden bg-pine-50 border-4 border-white shadow flex items-center justify-center shrink-0 relative">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($existingLogoUrl)
                        <img src="{{ $existingLogoUrl }}" class="w-full h-full object-cover">
                    @else
                        <x-lucide-landmark class="w-5 h-5 text-pine-600" />
                    @endif

                    <label for="logo-upload" class="absolute inset-0 rounded-full cursor-pointer">
                        <span class="absolute -bottom-1 -right-1 bg-white rounded-full p-1 shadow">
                            <x-lucide-camera class="w-3 h-3 text-pine-600" />
                        </span>
                    </label>
                    <input id="logo-upload" type="file" wire:model="logo" accept="image/*" class="hidden">
                </div>
            </div>
        </div>

        <p class="text-xs text-muted-500 -mt-5 ml-1">
            Cliquez sur la bannière ou le logo pour ajouter une image (optionnel)
        </p>

        @error('banner') <span class="text-red-500 text-sm block">{{ $message }}</span> @enderror
        @error('logo') <span class="text-red-500 text-sm block">{{ $message }}</span> @enderror

        {{-- Nom --}}
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-0.5">Nom du club</label>
            <input type="text" wire:model="name"
                class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-0.5">Description</label>
            <textarea wire:model="description" rows="2"
                class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none"></textarea>
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Catégorie --}}
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-0.5">Catégorie</label>

            <select wire:model.live="categorySelection"
                class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none bg-white">
                <option value="" disabled>Choisir une catégorie</option>
                @foreach (\App\Livewire\Clubs\ClubForm::CATEGORIES as $key => $cat)
                    <option value="{{ $key }}">{{ $cat['label'] }}</option>
                @endforeach
                <option value="{{ \App\Livewire\Clubs\ClubForm::AUTRE_KEY }}">Autre (préciser)</option>
            </select>

            @error('categorySelection') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            @if ($categorySelection === \App\Livewire\Clubs\ClubForm::AUTRE_KEY)
                <input type="text" wire:model="customCategory" placeholder="Précisez votre domaine..."
                    class="mt-2 border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                @error('customCategory') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            @elseif ($categorySelection && isset(\App\Livewire\Clubs\ClubForm::CATEGORIES[$categorySelection]))
                @php $selectedCat = \App\Livewire\Clubs\ClubForm::CATEGORIES[$categorySelection]; @endphp
                <div class="mt-2 inline-flex items-center gap-1.5 text-sm px-3 py-1 rounded-full {{ $categoryBadgeClasses[$selectedCat['color']] }}">
                    <x-dynamic-component :component="'lucide-' . $selectedCat['icon']" class="w-3.5 h-3.5" />
                    {{ $selectedCat['label'] }}
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit" wire:loading.attr="disabled"
                class="bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
                {{ $club ? 'Enregistrer les modifications' : 'Créer le club' }}
            </button>

            @if ($embedded)
                <button type="button" wire:click="cancel"
                    class="border border-muted-300 text-muted-700 hover:bg-muted-50 px-4 py-2 rounded-xl font-semibold transition">
                    Annuler
                </button>
            @endif
        </div>

    </form>

</div>