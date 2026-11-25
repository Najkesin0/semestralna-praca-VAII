<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Recept. Vzťahy: autor (N:1 User), ingrediencie a kroky (1:N), hodnotenia (1:N Review),
 * obľúbené a plán jedál (M:N cez favorites / meal_plans).
 */
class Recipe extends Model
{
    protected static ?string $tableName = 'recipes';

    public const CATEGORIES = ['Raňajky', 'Obed', 'Večera', 'Dezert', 'Občerstvenie'];
    public const DIFFICULTIES = ['Jednoduché', 'Stredné', 'Náročné'];

    protected ?int $id = null;
    protected ?int $user_id = null;
    protected ?string $title = null;
    protected ?string $summary = null;
    protected ?string $description = null;
    protected ?string $category = null;
    protected ?string $image = null;
    protected ?int $prep_time = 0;
    protected ?int $cook_time = 0;
    protected ?string $difficulty = 'Jednoduché';
    protected ?int $servings = 2;
    protected ?int $calories = null;
    protected ?int $protein = 0;
    protected ?int $carbs = 0;
    protected ?int $fat = 0;
    protected ?int $is_vegetarian = 0;
    protected ?int $is_vegan = 0;
    protected ?int $is_gluten_free = 0;
    protected ?int $is_lactose_free = 0;
    protected ?string $tip = null;
    protected ?string $created_at = null;

    // ---------------------------------------------------------------- vzťahy

    public function getAuthor(): ?User
    {
        return $this->getOneRelated(User::class, 'user_id');
    }

    /** @return Ingredient[] zoradené podľa poradia */
    public function getIngredients(): array
    {
        $items = $this->getAllRelated(Ingredient::class, 'recipe_id');
        usort($items, fn(Ingredient $a, Ingredient $b) => $a->getPosition() <=> $b->getPosition());
        return $items;
    }

    /** @return Step[] zoradené podľa poradia */
    public function getSteps(): array
    {
        $items = $this->getAllRelated(Step::class, 'recipe_id');
        usort($items, fn(Step $a, Step $b) => $a->getPosition() <=> $b->getPosition());
        return $items;
    }

    /** @return Review[] */
    public function getReviews(): array
    {
        return $this->getAllRelated(Review::class, 'recipe_id');
    }

    /**
     * Priemerné hodnotenie a počet hodnotení.
     *
     * @return array{avg: float|null, count: int}
     */
    public function getRatingStats(): array
    {
        $reviews = $this->getReviews();
        $count = count($reviews);
        $avg = $count > 0 ? array_sum(array_map(fn(Review $r) => $r->getRating(), $reviews)) / $count : null;
        return ['avg' => $avg, 'count' => $count];
    }

    public function isOwnedBy(?int $userId): bool
    {
        return $userId !== null && $this->user_id === $userId;
    }

    // ---------------------------------------------------------------- odvodené hodnoty

    public function getTotalTime(): int
    {
        return (int)$this->prep_time + (int)$this->cook_time;
    }

    /**
     * Štítky stravovania na zobrazenie (Vegánske má prednosť pred Vegetariánske).
     *
     * @return string[]
     */
    public function getDietTags(): array
    {
        $tags = [];
        if ($this->is_vegan) {
            $tags[] = 'Vegánske';
        } elseif ($this->is_vegetarian) {
            $tags[] = 'Vegetariánske';
        }
        if ($this->is_gluten_free) {
            $tags[] = 'Bez lepku';
        }
        if ($this->is_lactose_free) {
            $tags[] = 'Bez laktózy';
        }
        return $tags;
    }

    // ---------------------------------------------------------------- gettery / settery

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getPrepTime(): int
    {
        return $this->prep_time ?? 0;
    }

    public function setPrepTime(?int $prep_time): void
    {
        $this->prep_time = $prep_time;
    }

    public function getCookTime(): int
    {
        return $this->cook_time ?? 0;
    }

    public function setCookTime(?int $cook_time): void
    {
        $this->cook_time = $cook_time;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(?string $difficulty): void
    {
        $this->difficulty = $difficulty;
    }

    public function getServings(): int
    {
        return max(1, $this->servings ?? 2);
    }

    public function setServings(?int $servings): void
    {
        $this->servings = $servings;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(?int $calories): void
    {
        $this->calories = $calories;
    }

    public function getProtein(): int
    {
        return $this->protein ?? 0;
    }

    public function setProtein(?int $protein): void
    {
        $this->protein = $protein;
    }

    public function getCarbs(): int
    {
        return $this->carbs ?? 0;
    }

    public function setCarbs(?int $carbs): void
    {
        $this->carbs = $carbs;
    }

    public function getFat(): int
    {
        return $this->fat ?? 0;
    }

    public function setFat(?int $fat): void
    {
        $this->fat = $fat;
    }

    public function isVegetarian(): bool
    {
        return (bool)$this->is_vegetarian;
    }

    public function setVegetarian(bool $value): void
    {
        $this->is_vegetarian = (int)$value;
    }

    public function isVegan(): bool
    {
        return (bool)$this->is_vegan;
    }

    public function setVegan(bool $value): void
    {
        $this->is_vegan = (int)$value;
    }

    public function isGlutenFree(): bool
    {
        return (bool)$this->is_gluten_free;
    }

    public function setGlutenFree(bool $value): void
    {
        $this->is_gluten_free = (int)$value;
    }

    public function isLactoseFree(): bool
    {
        return (bool)$this->is_lactose_free;
    }

    public function setLactoseFree(bool $value): void
    {
        $this->is_lactose_free = (int)$value;
    }

    public function getTip(): ?string
    {
        return $this->tip;
    }

    public function setTip(?string $tip): void
    {
        $this->tip = $tip;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }
}
