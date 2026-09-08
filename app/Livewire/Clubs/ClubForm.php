<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use App\Services\CloudinaryUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClubForm extends Component
{
    use WithFileUploads;

    /**
     * Liste fixe des catégories de club.
     * Clé = valeur technique utilisée dans le <select>.
     * 'label' = texte réellement enregistré dans club.category (inchangé : toujours une string).
     * 'icon'  = nom de l'icône Lucide associée.
     * 'color' = famille de couleur Tailwind associée (pine/amber/terracotta/prune/ardoise).
     */
    public const CATEGORIES = [
        'informatique'    => ['label' => 'Informatique & Technologie', 'icon' => 'laptop', 'color' => 'pine'],
        'arts'            => ['label' => 'Arts & Culture', 'icon' => 'palette', 'color' => 'prune'],
        'sport'           => ['label' => 'Sport', 'icon' => 'dumbbell', 'color' => 'terracotta'],
        'environnement'   => ['label' => 'Environnement', 'icon' => 'leaf', 'color' => 'ardoise'],
        'social'          => ['label' => 'Social & Humanitaire', 'icon' => 'handshake', 'color' => 'ardoise'],
        'entrepreneuriat' => ['label' => 'Entrepreneuriat', 'icon' => 'briefcase', 'color' => 'amber'],
        'sciences'        => ['label' => 'Sciences', 'icon' => 'microscope', 'color' => 'pine'],
        'academique'      => ['label' => 'Académique', 'icon' => 'graduation-cap', 'color' => 'amber'],
        'gaming'          => ['label' => 'Loisirs & Gaming', 'icon' => 'gamepad-2', 'color' => 'terracotta'],
        'langues'         => ['label' => 'Langues & International', 'icon' => 'globe', 'color' => 'prune'],
    ];

    // Valeur technique spéciale pour "Autre", en dehors du tableau CATEGORIES ci-dessus.
    public const AUTRE_KEY = 'autre';

    public ?Club $club = null;
    public bool $embedded = false;

    public $name = '';
    public $description = '';
    public $category = ''; // valeur finale enregistrée en base (inchangé)

    // --- Gestion du select + option "Autre" ---
    public $categorySelection = ''; // clé du <select> : une clé de CATEGORIES, ou 'autre'
    public $customCategory = '';    // texte libre, utilisé seulement si categorySelection === 'autre'

    public $logo;
    public $banner;

    public $existingLogoUrl = null;
    public $existingBannerUrl = null;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clubs', 'name')->ignore($this->club?->id),
            ],
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'categorySelection' => 'required|string',
            'customCategory' => 'required_if:categorySelection,' . self::AUTRE_KEY . '|nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
        ];
    }

    protected function messages()
    {
        return [
            'name.unique' => 'Ce nom de club est déjà utilisé, choisissez-en un autre.',
        ];
    }

    public function mount(?Club $club = null, bool $embedded = false)
    {
        $this->embedded = $embedded;

        if ($club && $club->exists) {

            // Sécurité : seul le président peut modifier son club
            abort_if($club->president_id !== Auth::id(), 403);

            $this->club = $club;

            $this->name = $club->name;
            $this->description = $club->description;
            $this->category = $club->category;

            // --- Retrouver quelle option du select correspond à la catégorie existante ---
            $matchedKey = collect(self::CATEGORIES)
                ->search(fn ($cat) => $cat['label'] === $club->category);

            if ($matchedKey !== false) {
                $this->categorySelection = $matchedKey;
            } else {
                // Catégorie non reconnue (ancienne donnée type "info") → on la traite comme "Autre"
                $this->categorySelection = self::AUTRE_KEY;
                $this->customCategory = $club->category;
            }

            // Cloudinary stocke déjà une URL absolue complète en base,
            // plus besoin de la reconstruire avec asset('storage/...').
            $this->existingLogoUrl = $club->logo;
            $this->existingBannerUrl = $club->banner;
        }
    }

    // --- Synchronise $category dès qu'on change le select ---
    public function updatedCategorySelection($value)
    {
        if ($value !== self::AUTRE_KEY && isset(self::CATEGORIES[$value])) {
            $this->category = self::CATEGORIES[$value]['label'];
            $this->customCategory = '';
        } else {
            $this->category = $this->customCategory;
        }
    }

    // --- Synchronise $category en direct quand on tape dans le champ "Autre" ---
    public function updatedCustomCategory($value)
    {
        if ($this->categorySelection === self::AUTRE_KEY) {
            $this->category = $value;
        }
    }

    public function cancel()
    {
        $this->dispatch('cancel-club-form');
    }

    public function save(CloudinaryUploadService $cloudinary)
    {
        // --- Sécurité, au cas où la synchro live n'aurait pas eu lieu ---
        if ($this->categorySelection === self::AUTRE_KEY) {
            $this->category = $this->customCategory;
        } elseif (isset(self::CATEGORIES[$this->categorySelection])) {
            $this->category = self::CATEGORIES[$this->categorySelection]['label'];
        }

        $this->validate();

        if ($this->club) {

            // --------- MODIFICATION ---------

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'category' => $this->category,
            ];

            if ($this->logo) {
                $data['logo'] = $cloudinary->upload($this->logo, 'clubs/logos');
            }

            if ($this->banner) {
                $data['banner'] = $cloudinary->upload($this->banner, 'clubs/banners');
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
                $data['logo'] = $cloudinary->upload($this->logo, 'clubs/logos');
            }

            if ($this->banner) {
                $data['banner'] = $cloudinary->upload($this->banner, 'clubs/banners');
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