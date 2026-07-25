<?php

namespace App\Livewire\Clubs;

use Livewire\Component;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;

class ClubForm extends Component
{
    public $name;
    public $description;
    public $category;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'category' => 'required|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        Club::create([
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'president_id' => Auth::id(),
        ]);

        session()->flash('success', 'Club créé avec succès 🎉');

        $this->reset();
    }

    public function render()
    {
        return view('livewire.clubs.club-form')
            ->layout('components.layouts.app');
    }
}