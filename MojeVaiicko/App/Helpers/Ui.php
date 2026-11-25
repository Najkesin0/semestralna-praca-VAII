<?php

namespace App\Helpers;

use App\Models\Recipe;
use Framework\Support\LinkGenerator;

/**
 * Pomocné funkcie pre šablóny: preklady kategórií, formátovanie čísel, skloňovanie,
 * dátumy, flash správy a obrázky receptov.
 */
class Ui
{
    /** Kategória v DB => názov v bočnom filtri */
    public const CATEGORY_LABELS = [
        'Raňajky' => 'Raňajky',
        'Obed' => 'Obed',
        'Večera' => 'Večera',
        'Dezert' => 'Dezerty',
        'Občerstvenie' => 'Občerstvenie',
    ];

    public const MEAL_LABELS = [
        'ranajky' => 'Raňajky',
        'obed' => 'Obed',
        'vecera' => 'Večera',
    ];

    public const MEAL_ICONS = [
        'ranajky' => 'bi-cup-hot',
        'obed' => 'bi-sun',
        'vecera' => 'bi-moon',
    ];

    public const DAY_NAMES = ['Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota', 'Nedeľa'];

    public const MONTHS_GENITIVE = [
        1 => 'januára', 'februára', 'marca', 'apríla', 'mája', 'júna',
        'júla', 'augusta', 'septembra', 'októbra', 'novembra', 'decembra',
    ];

    /** Sekcie nákupného zoznamu */
    public const SHOP_CATEGORIES = [
        'zelenina' => 'Zelenina a bylinky',
        'chladene' => 'Chladené a mliečne',
        'ovocie' => 'Ovocie',
        'spajza' => 'Špajza a pečivo',
        'ine' => 'Ostatné',
    ];

    public const UNITS = ['g', 'kg', 'ml', 'l', 'ks', 'PL', 'ČL', 'strúčik', 'strúčiky', 'plátky', 'zväzok', 'štipka', 'hrsť', 'podľa chuti'];

    private const CATEGORY_ICONS = [
        'Raňajky' => 'bi-cup-hot',
        'Obed' => 'bi-egg-fried',
        'Večera' => 'bi-moon-stars',
        'Dezert' => 'bi-cake2',
        'Občerstvenie' => 'bi-cup-straw',
    ];

    private static ?string $title = null;

    // ------------------------------------------------------------------ titulok stránky

    public static function setTitle(string $title): void
    {
        self::$title = $title;
    }

    public static function title(): string
    {
        return self::$title ? self::$title . ' · SmartRecipe Hub' : 'SmartRecipe Hub';
    }

    // ------------------------------------------------------------------ flash správy

    public static function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }

    /** @return array{message: string, type: string}|null */
    public static function takeFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    // ------------------------------------------------------------------ čísla a skloňovanie

    /**
     * 200 → „200“, 0.5 → „0,5“, 1.25 → „1,25“.
     */
    public static function number(?float $value): string
    {
        if ($value === null) {
            return '';
        }
        if (abs($value - round($value)) < 0.001) {
            return (string)(int)round($value);
        }
        return rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',');
    }

    /**
     * Slovenské skloňovanie podľa počtu: plural(2, 'porcia', 'porcie', 'porcií') → „porcie“.
     */
    public static function plural(int|float $n, string $one, string $few, string $many): string
    {
        if (abs($n - round($n)) > 0.001) {
            return $few; // desatinné číslo: „1,5 porcie“
        }
        $n = (int)round($n);
        if ($n === 1) {
            return $one;
        }
        return ($n >= 2 && $n <= 4) ? $few : $many;
    }

    public static function servings(int $n): string
    {
        return $n . ' ' . self::plural($n, 'porcia', 'porcie', 'porcií');
    }

    public static function rating(?float $avg): string
    {
        return $avg === null ? '–' : number_format($avg, 1, ',', '');
    }

    // ------------------------------------------------------------------ dátumy

    public static function mondayOf(string $date): \DateTimeImmutable
    {
        $d = new \DateTimeImmutable($date);
        return $d->modify('-' . ((int)$d->format('N') - 1) . ' days')->setTime(0, 0);
    }

    /**
     * „16. – 22. novembra 2026“ alebo „30. novembra – 6. decembra 2026“.
     */
    public static function weekRange(\DateTimeImmutable $monday): string
    {
        $sunday = $monday->modify('+6 days');
        $endMonth = self::MONTHS_GENITIVE[(int)$sunday->format('n')];
        if ($monday->format('n') === $sunday->format('n')) {
            return $monday->format('j') . '. – ' . $sunday->format('j') . '. ' . $endMonth . ' ' . $sunday->format('Y');
        }
        $startMonth = self::MONTHS_GENITIVE[(int)$monday->format('n')];
        $startYear = $monday->format('Y') !== $sunday->format('Y') ? ' ' . $monday->format('Y') : '';
        return $monday->format('j') . '. ' . $startMonth . $startYear . ' – ' . $sunday->format('j') . '. ' . $endMonth
            . ' ' . $sunday->format('Y');
    }

    public static function dayName(\DateTimeInterface $date): string
    {
        return self::DAY_NAMES[(int)$date->format('N') - 1];
    }

    /**
     * „Utorok a štvrtok“, „Pondelok, streda a piatok“.
     *
     * @param string[] $words
     */
    public static function joinWords(array $words): string
    {
        $words = array_values($words);
        if (count($words) <= 1) {
            return $words[0] ?? '';
        }
        $last = array_pop($words);
        return implode(', ', $words) . ' a ' . $last;
    }

    public static function ucfirst(string $text): string
    {
        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    public static function seasonText(?int $month = null): string
    {
        $month ??= (int)date('n');
        return match (true) {
            in_array($month, [12, 1, 2], true) => 'Sezóna chutí: kapusta, citrusy a poctivé guláše',
            in_array($month, [3, 4, 5], true) => 'Sezóna chutí: špargľa, medvedí cesnak a reďkovky',
            in_array($month, [6, 7, 8], true) => 'Sezóna chutí: jahody, cukety a ľahké šaláty',
            default => 'Sezóna chutí: tekvica, jablká a hrejivé polievky',
        };
    }

    // ------------------------------------------------------------------ obrázky receptov

    public static function imageUrl(Recipe $recipe, LinkGenerator $link): ?string
    {
        return $recipe->getImage() ? $link->asset('uploads/' . rawurlencode($recipe->getImage())) : null;
    }

    /**
     * Obrázok receptu alebo farebný zástupný obrázok podľa kategórie (ak fotka chýba).
     */
    public static function recipeImage(Recipe $recipe, LinkGenerator $link, string $class = ''): string
    {
        $url = self::imageUrl($recipe, $link);
        if ($url !== null) {
            return '<img src="' . e($url) . '" alt="' . e($recipe->getTitle()) . '" class="recipe-img ' . e($class)
                . '" loading="lazy">';
        }
        $category = $recipe->getCategory() ?? '';
        $icon = self::CATEGORY_ICONS[$category] ?? 'bi-basket';
        $variant = array_search($category, Recipe::CATEGORIES, true);
        $variant = $variant === false ? 0 : $variant;
        return '<div class="recipe-img recipe-img--placeholder variant-' . $variant . ' ' . e($class)
            . '" role="img" aria-label="' . e($recipe->getTitle()) . '"><i class="bi ' . $icon . '"></i></div>';
    }
}
