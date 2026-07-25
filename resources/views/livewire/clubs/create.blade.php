<div class="p-6">

<h1 class="text-2xl font-bold mb-5">
Créer un club
</h1>


<form wire:submit="save">

<input 
type="text"
wire:model="name"
placeholder="Nom du club"
class="border p-2 w-full mb-3">


<textarea
wire:model="description"
placeholder="Description"
class="border p-2 w-full mb-3">
</textarea>


<input
type="text"
wire:model="category"
placeholder="Catégorie"
class="border p-2 w-full mb-3">


<button
class="bg-blue-600 text-white px-4 py-2 rounded">

Créer

</button>


</form>

</div>