<?php

/**
 * Liste des départements/filières, groupés par catégorie.
 *
 * Ce fichier est la SEULE source de vérité pour les départements de l'application.
 * Utilisé par app/Support/DepartmentList.php pour :
 *   - grouped() : générer les <optgroup> dans les <select> (onboarding + profil)
 *   - flat()    : générer la liste plate utilisée pour la validation Laravel
 *
 * Pour ajouter/retirer/déplacer une filière : modifier UNIQUEMENT ce fichier.
 * Ne rien modifier dans CompleteProfile.php, Profile/Edit.php ou les vues .blade.php.
 */

return [

    'Informatique & numérique' => [
        'Génie Informatique',
        'Génie Logiciel',
        'Ingénierie des Systèmes d\'Information',
        'Intelligence Artificielle',
        'Data Science',
        'Cybersécurité',
        'Réseaux et Télécommunications',
    ],

    'Électrique-électronique-automatique' => [
        'Génie Électrique',
        'Génie Électronique',
        'Automatique',
        'Électromécanique',
        'Systèmes Embarqués',
    ],

    'Mécanique & industriel' => [
        'Génie des Systèmes',
        'Génie Mécanique',
        'Génie Industriel',
        'Maintenance Industrielle',
        'Mécatronique',
        'Productique',
        'Génie Industriel et Logistique',
    ],

    'Génie civil & construction' => [
        'Génie Civil',
        'Bâtiment et Travaux Publics',
    ],

    'Environnement-énergie-chimie' => [
        'Génie de l\'Eau et de l\'Environnement',
        'Génie Énergétique',
        'Énergies Renouvelables',
        'Génie de l\'Environnement',
        'Génie Agroalimentaire',
        'Génie Chimique',
        'Génie des Procédés',
    ],

    'Management & systèmes' => [
        'Management des Systèmes',
        'Management de la Qualité',
        'Supply Chain et Logistique',
    ],

];