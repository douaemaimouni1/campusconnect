<div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <h2 class="font-semibold text-2xl text-gray-800">
            {{ $event ? 'Modifier l\'événement' : 'Créer un événement' }}
        </h2>
    </div>

    <div class="max-w-xl mx-auto py-8 px-6">

        @if (session()->has('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit="save" class="bg-white rounded-2xl shadow p-8 space-y-5">

            {{-- Image de l'événement --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Image de l'événement</label>

                <div class="h-40 rounded-xl overflow-hidden bg-gradient-to-r from-indigo-500 to-purple-500 mb-2">
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($existingImageUrl)
                        <img src="{{ $existingImageUrl }}" class="w-full h-full object-cover">
                    @endif
                </div>

                <input type="file" wire:model="image" accept="image/*" class="text-sm">

                @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                <input
                    type="text"
                    wire:model="title"
                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    wire:model="description"
                    rows="4"
                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                <input
                    type="text"
                    wire:model="location"
                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date et heure</label>
                <input
                    type="datetime-local"
                    wire:model="date"
                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacité (nombre de places)</label>
                <input
                    type="number"
                    min="1"
                    wire:model="capacity"
                    class="border border-gray-200 rounded-lg p-2 w-full focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                @error('capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
                {{ $event ? 'Enregistrer les modifications' : 'Créer l\'événement' }}
            </button>

        </form>

    </div>

</div>