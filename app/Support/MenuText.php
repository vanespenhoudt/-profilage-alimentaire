<?php

namespace App\Support;

/**
 * Affichage sûr du menu saisi : texte riche (éditeur conseiller) ou texte brut (formulaire client).
 */
final class MenuText
{
    private const ALLOWED_TAGS = '<p><br><ul><ol><li><strong><b><em><i><u><h1><h2><h3><h4><blockquote>';

    public static function toSafeHtml(?string $menu): string
    {
        if ($menu === null || trim($menu) === '') {
            return '';
        }

        if ($menu === strip_tags($menu)) {
            // Texte brut : on garde les retours à la ligne (une ligne par repas / journée)
            return nl2br(e($menu));
        }

        // Texte riche : balises de mise en forme uniquement, sans aucun attribut
        $html = strip_tags($menu, self::ALLOWED_TAGS);

        return preg_replace('/<(\w+)\s[^>]*>/', '<$1>', $html);
    }
}
