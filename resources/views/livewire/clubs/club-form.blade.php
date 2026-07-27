<div class="max-w-xl mx-auto py-8">

    <h1 class="text-2xl font-bold mb-6">
        {{ $club ? 'Modifier le club' : 'Créer un club' }}
    </h1>

    @if(session()->has('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="bg-white rounded-2xl shadow p-8 space-y-5">

        {{-- Bannière --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bannière du club</label>

            <div class="h-32 rounded-xl overflow-hidden bg-gradient-to-r from-indigo-500 to-purple-500 mb-2">
                @if ($banner)
                    <img src="{{ $banner->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif ($existingBannerUrl)
                    <img src="{{ $existingBannerUrl }}" class="w-full h-full object-cover">
                @endif
            </div>

            <input type="file" wire:model="banner" accept="image/*" class="text-sm">

            @error('banner') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Logo --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Logo du club</label>

            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full overflow-hidden bg-indigo-50 flex items-center justify-center text-3xl shrink-0">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($existingLogoUrl)
                        <img src="{{ $existingLogoUrl }}" class="w-full h-full object-cover">
                    @else
                        🏛
                    @endif
                </div>

                <input type="file" wire:model="logo" accept="image/*" class="text-sm">
            </div>

            @error('logo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Nom --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du club</label>

            <input
                type="text"
                wire:model="name"
                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            >

            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>

            <textarea
                wire:model="description"
                rows="4"
                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </textarea>

            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Catégorie --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>

            <input
                type="text"
                wire:model="category"
                class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            >

            @error('category') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
            {{ $club ? 'Enregistrer les modifications' : 'Créer le club' }}
        </button>

    </form>

</div>