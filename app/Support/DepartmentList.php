<?php

namespace App\Support;

/**
 * Classe utilitaire centralisant l'accès à la liste des départements.
 *
 * Elle lit config/departments.php et expose deux formats :
 *   - grouped() : format groupé par catégorie, pour générer les <optgroup> dans les vues
 *   - flat()    : format à plat (juste les noms), pour la validation Laravel
 *
 * Elle gère aussi l'option "Autre", qui n'existe pas dans config/departments.php
 * (car ce n'est pas un vrai département, mais une option d'interface).
 */
class DepartmentList
{
    /**
     * Le libellé utilisé pour l'option "texte libre" en fin de liste.
     * Centralisé ici pour ne pas écrire "Autre" en dur à plusieurs endroits.
     */
    public const AUTRE = 'Autre';

    /**
     * Retourne la liste groupée par catégorie, avec "Autre" ajouté
     * à la fin dans sa propre catégorie "Autre".
     *
     * Format retourné :
     * [
     *     'Informatique & numérique' => ['Génie Logiciel', 'Data Science', ...],
     *     'Électrique-électronique-automatique' => [...],
     *     ...
     *     'Autre' => ['Autre'],
     * ]
     */
    public static function grouped(): array
    {
        $categories = config('departments');

        // On ajoute "Autre" comme dernière "catégorie", avec lui-même comme seule option.
        // Cela permet de le générer dans son propre <optgroup> dans la vue, tout en
        // gardant une structure uniforme (toujours catégorie => tableau de filières).
        $categories[self::AUTRE] = [self::AUTRE];

        return $categories;
    }

    /**
     * Retourne la liste à plat de toutes les filières valides, "Autre" inclus.
     * Utilisée pour la validation Laravel (Rule::in()).
     *
     * Format retourné : ['Génie Logiciel', 'Data Science', ..., 'Autre']
     */
    public static function flat(): array
    {
        $categories = config('departments');

        // array_merge(...$categories) fusionne tous les sous-tableaux de catégories
        // en une seule liste plate de filières.
        $departments = array_merge(...array_values($categories));

        // On ajoute "Autre" à la fin de la liste plate.
        $departments[] = self::AUTRE;

        return $departments;
    }
}