<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClubForm extends Component
{
    use WithFileUploads;

    public ?Club $club = null;

    public $name = '';
    public $description = '';
    public $category = '';

    public $logo;
    public $banner;

    public $existingLogoUrl = null;
    public $existingBannerUrl = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
        ];
    }

    public function mount(?Club $club = null)
    {
        if ($club && $club->exists) {

            // Sécurité : seul le président peut modifier son club
            abort_if($club->president_id !== Auth::id(), 403);

            $this->club = $club;

            $this->name = $club->name;
            $this->description = $club->description;
            $this->category = $club->category;

            if ($club->logo) {
                $this->existingLogoUrl = asset('storage/' . $club->logo);
            }

            if ($club->banner) {
                $this->existingBannerUrl = asset('storage/' . $club->banner);
            }
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->club) {

            // --------- MODIFICATION ---------

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'category' => $this->category,
            ];

            if ($this->logo) {
                $data['logo'] = $this->logo->store('clubs/logos', 'public');
            }

            if ($this->banner) {
                $data['banner'] = $this->banner->store('clubs/banners', 'public');
            }

            $this->club->update($data);

            session()->flash('success', 'Club modifié avec succès 🎉');

        } else {

            // --------- CRÉATION ---------

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'category' => $this->category,
                'president_id' => Auth::id(),
            ];

            if ($this->logo) {
                $data['logo'] = $this->logo->store('clubs/logos', 'public');
            }

            if ($this->banner) {
                $data['banner'] = $this->banner->store('clubs/banners', 'public');
            }

            $this->club = Club::create($data);

            session()->flash('success', 'Club créé avec succès 🎉');
        }

        return redirect()->route('clubs.show', $this->club);
    }

    public function render()
    {
        return view('livewire.clubs.club-form')
            ->layout('components.layouts.app');
    }
}