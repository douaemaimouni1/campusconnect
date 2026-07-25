<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class ProfileSetup extends Component
{
    use WithFileUploads;

    public $department;
    public $bio;
    public $avatar;


    public function save()
    {
        $user = Auth::user();

        $user->update([
            'department' => $this->department,
            'bio' => $this->bio,
            'avatar' => $this->avatar
                ? $this->avatar->store('avatars', 'public')
                : null,

            'profile_completed' => true,
        ]);

        return redirect('/');
    }


    public function render()
    {
        return view('livewire.profile-setup')
            ->layout('components.layouts.app');
    }
}