<?php

namespace App\Models;

use Framework\Core\Model;

class Recipe extends Model
{
    protected ?int $id = null;
    protected ?string $title = null;
    protected ?string $category = null;
    protected ?int $prep_time = null;
    protected ?int $calories = null;
    protected ?string $created_at = null; // <-- PRIDANÝ STĹPEC

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getPrepTime(): ?int
    {
        return $this->prep_time;
    }

    public function setPrepTime(?int $prep_time): void
    {
        $this->prep_time = $prep_time;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(?int $calories): void
    {
        $this->calories = $calories;
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