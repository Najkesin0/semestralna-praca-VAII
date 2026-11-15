<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Hodnotenie receptu – entita vo vzťahu N:1 k entite Recipe (cudzí kľúč recipe_id).
 */
class Review extends Model
{
    protected ?int $id = null;
    protected ?int $recipe_id = null;
    protected ?string $author = null;
    protected ?int $rating = null;
    protected ?string $comment = null;
    protected ?string $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getRecipeId(): ?int
    {
        return $this->recipe_id;
    }

    public function setRecipeId(?int $recipe_id): void
    {
        $this->recipe_id = $recipe_id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): void
    {
        $this->rating = $rating;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * Vráti recept, ku ktorému hodnotenie patrí (vzťah N:1 cez ORM).
     */
    public function getRecipe(): ?Recipe
    {
        return $this->getOneRelated(Recipe::class, 'recipe_id');
    }
}