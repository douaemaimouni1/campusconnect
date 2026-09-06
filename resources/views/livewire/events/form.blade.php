<div>

    <div class="max-w-xl mx-auto pt-6 px-6">
        <h2 class="font-serif text-xl font-bold text-ink">
            {{ $event ? 'Modifier l\'événement' : 'Créer un événement' }}
        </h2>
    </div>

    <div class="max-w-xl mx-auto py-6 px-6">

        @if (session()->has('success'))
            <div class="bg-pine-50 text-pine-700 p-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit="save" class="bg-white rounded-2xl shadow p-6 space-y-3">

            {{-- Image de l'événement --}}
            <div>
                <label class="block text-sm font-medium text-ink mb-1">Image de l'événement</label>

                <div class="h-24 rounded-xl overflow-hidden bg-pine-700 relative mb-1">
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($existingImageUrl)
                        <img src="{{ $existingImageUrl }}" class="w-full h-full object-cover">
                    @endif

                    <label for="event-image-upload" class="absolute inset-0 cursor-pointer">
                        <span class="absolute bottom-2 right-2 flex items-center gap-1 bg-white/90 text-ink text-xs font-semibold px-2 py-1 rounded-lg shadow">
                            <x-lucide-camera class="w-3.5 h-3.5" />
                            Changer
                        </span>
                    </label>
                    <input id="event-image-upload" type="file" wire:model="image" accept="image/*" class="hidden">
                </div>

                @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Titre + Lieu côte à côte --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-ink mb-1">Titre</label>
                    <input
                        type="text"
                        wire:model="title"
                        class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink mb-1">Lieu</label>
                    <input
                        type="text"
                        wire:model="location"
                        class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                    @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-ink mb-1">Description</label>
                <textarea
                    wire:model="description"
                    rows="2"
                    class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none"></textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Date + Capacité côte à côte --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-ink mb-1">Date et heure</label>
                    <input
                        type="datetime-local"
                        wire:model="date"
                        min="{{ now()->format('Y-m-d\TH:i') }}"
                        class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                    @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink mb-1">Capacité (places)</label>
                    <input
                        type="number"
                        min="1"
                        wire:model="capacity"
                        class="border border-muted-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-pine-500 focus:border-pine-500 outline-none">
                    @error('capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="bg-pine-600 hover:bg-pine-700 disabled:opacity-50 text-white px-6 py-2 rounded-xl font-semibold transition">
                {{ $event ? 'Enregistrer les modifications' : 'Créer l\'événement' }}
            </button>

        </form>

    </div>

</div>