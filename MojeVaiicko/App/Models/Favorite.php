<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Obľúbený recept používateľa (väzobná tabuľka M:N medzi users a recipes).
 */
class Favorite extends Model
{
    protected static ?string $tableName = 'favorites';

    protected ?int $id = null;
    protected ?int $user_id = null;
    protected ?int $recipe_id = null;
    protected ?string $created_at = null;

    /**
     * ID všetkých receptov, ktoré má používateľ v obľúbených (kľúč = id receptu).
     *
     * @return array<int, true>
     */
    public static function recipeIdsForUser(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }
        $ids = [];
        foreach (self::getAll('`user_id` = ?', [$userId]) as $favorite) {
            $ids[$favorite->getRecipeId()] = true;
        }
        return $ids;
    }

    public static function findFor(int $userId, int $recipeId): ?Favorite
    {
        $found = self::getAll('`user_id` = ? AND `recipe_id` = ?', [$userId, $recipeId], null, 1);
        return $found[0] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getRecipeId(): ?int
    {
        return $this->recipe_id;
    }

    public function setRecipeId(?int $recipe_id): void
    {
        $this->recipe_id = $recipe_id;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }
}
