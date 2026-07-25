<?php

namespace App\Livewire\Onboarding;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class CompleteProfile extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public $avatar;
    public $department = '';
    public $otherDepartment = '';
    public $bio = '';

    public $departments = [
        'Génie Informatique',
        'Génie Civil',
        'Génie Industriel',
        'Génie Électrique',
        'Génie Mécanique',
        'Génie Énergétique',
        'Autre',
    ];

    protected function rules()
    {
        return [
            'department' => 'required|string|max:100',
            'otherDepartment' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:250',
            'avatar' => 'nullable|image|max:2048',
        ];
    }

    public function nextStep()
    {
        if ($this->step == 2) {
            $this->validateOnly('department');

            if ($this->department === 'Autre' && empty($this->otherDepartment)) {
                $this->addError('otherDepartment', 'Veuillez préciser votre département.');
                return;
            }
        }

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    /**
     * Passer une étape sans tout valider/enregistrer.
     * - Étape 1 (photo) ou 2 (département) -> avance juste à l'étape suivante.
     * - Étape 3 (bio, dernière étape) -> termine réellement l'onboarding.
     */
    public function skip()
    {
        if ($this->step < 3) {
            $this->step++;
            return;
        }

        $this->finish();
    }

    public function save()
    {
        if ($this->step === 3) {
            $this->finish();
        } else {
            $this->nextStep();
        }
    }

    /**
     * Enregistrement final : appelé uniquement depuis l'étape 3
     * (bouton "Continuer" ou "Passer").
     */
    protected function finish()
    {
        $this->validate([
            'department' => 'required|string|max:100',
            'otherDepartment' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:250',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        $user->department = $this->department === 'Autre'
            ? $this->otherDepartment
            : $this->department;

        $user->bio = $this->bio;

        if ($this->avatar) {
            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        $user->profile_completed = true;

        $user->save();

        redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.onboarding.complete-profile')
            ->layout('layouts.onboarding');
    }
}