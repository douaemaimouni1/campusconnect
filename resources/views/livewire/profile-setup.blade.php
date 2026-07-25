<div class="max-w-xl mx-auto mt-10">

    <h1 class="text-3xl font-bold mb-6">
        Complète ton profil 👋
    </h1>


    <form wire:submit="save">

        <div class="mb-4">
            <label>Département</label>

            <input 
                type="text"
                wire:model="department"
                class="border p-2 w-full"
                placeholder="Ex: Génie Informatique"
            >
        </div>


        <div class="mb-4">
            <label>Bio</label>

            <textarea
                wire:model="bio"
                class="border p-2 w-full"
                placeholder="Parle-nous de toi..."
            ></textarea>
        </div>


        <div class="mb-4">
            <label>Photo de profil (optionnelle)</label>

            <input 
                type="file"
                wire:model="avatar"
            >
        </div>


        <button type="submit">
    CONTINUER TEST
</button>

    </form>

</div>