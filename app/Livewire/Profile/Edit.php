<?php

namespace App\Livewire\Profile;

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Edit extends Component
{
    use WithFileUploads;

    // --- Section : informations de profil ---
    public string $name = '';
    public string $email = '';
    public ?string $department = '';
    public ?string $bio = '';

    // Nouvel avatar en attente d'upload (temporaire, pas encore sauvegardé)
    public $avatar = null;

    // --- Section : mot de passe ---
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // --- Section : suppression de compte ---
    public string $delete_password = '';

    /**
     * Pré-remplit le formulaire avec les données actuelles de l'utilisateur connecté.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->department = $user->department;
        $this->bio = $user->bio;
    }

    /**
     * Met à jour les informations générales du profil (nom, email, département, bio, avatar).
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'department' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->name = $validated['name'];
        $user->department = $validated['department'];
        $user->bio = $validated['bio'];

        // Si l'email change, on force une nouvelle vérification (comportement standard Breeze)
        if ($validated['email'] !== $user->email) {
            $user->email = $validated['email'];

            if ($user instanceof MustVerifyEmail) {
                $user->email_verified_at = null;
            }
        }

        // Upload du nouvel avatar : on supprime l'ancien fichier avant d'enregistrer le nouveau
        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $this->avatar->store('avatars', 'public');
        }

        $user->save();

        // On vide le champ d'upload temporaire après sauvegarde
        $this->avatar = null;

        $this->dispatch('profile-updated');
    }


    public function deleteAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        $this->dispatch('avatar-deleted');
    }

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }


    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'delete_password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: false);
    }

    public function render()
    {
        return view('livewire.profile.edit');
    }
}