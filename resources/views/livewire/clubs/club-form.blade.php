<div class="max-w-xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
        Créer un club
    </h1>

    @if(session()->has('success'))
        <div class="bg-green-200 p-3 mb-4">
            {{ session('success') }}
        </div>
    @endif


    <form wire:submit="save">

        <div class="mb-4">
            <label>Nom du club</label>

            <input 
                type="text"
                wire:model="name"
                class="border p-2 w-full"
            >

            @error('name')
                <span class="text-red-500">
                    {{ $message }}
                </span>
            @enderror
        </div>


        <div class="mb-4">
            <label>Description</label>

            <textarea
                wire:model="description"
                class="border p-2 w-full">
            </textarea>

            @error('description')
                <span class="text-red-500">
                    {{ $message }}
                </span>
            @enderror
        </div>


        <div class="mb-4">
            <label>Catégorie</label>

            <input 
                type="text"
                wire:model="category"
                class="border p-2 w-full"
            >

            @error('category')
                <span class="text-red-500">
                    {{ $message }}
                </span>
            @enderror
        </div>


        <button 
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded">
            Créer le club
        </button>

    </form>

</div>