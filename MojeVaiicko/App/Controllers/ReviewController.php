<?php


namespace App\Controllers;

use App\Models\Recipe;
use App\Models\Review;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * CRUD nad hodnoteniami receptov (entita Review, vzťah N:1 k Recipe).
 */
class ReviewController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Zoznam hodnotení, voliteľne filtrovaný podľa receptu (?recipe_id=X).
     */
    public function index(Request $request): Response
    {
        $recipeId = $request->get('recipe_id');
        $recipe = $recipeId ? Recipe::getOne((int)$recipeId) : null;

        if ($recipe) {
            $reviews = Review::getAll('`recipe_id` = ?', [$recipe->getId()], '`created_at` DESC, `id` DESC');
        } else {
            $reviews = Review::getAll(null, [], '`created_at` DESC, `id` DESC');
        }

        return $this->html([
            'reviews' => $reviews,
            'recipe' => $recipe,
            'title' => 'SmartRecipe Hub - Hodnotenia'
        ]);
    }

    public function create(Request $request): Response
    {
        $review = new Review();
        $recipeId = $request->get('recipe_id');
        if ($recipeId) {
            $review->setRecipeId((int)$recipeId);
        }

        return $this->html([
            'review' => $review,
            'recipes' => Recipe::getAll(null, [], '`title` ASC'),
            'title' => 'Pridať hodnotenie'
        ], 'form');
    }

    public function edit(Request $request): Response
    {
        $review = Review::getOne($request->get('id'));

        if (!$review) {
            return $this->redirect($this->url('review.index'));
        }

        return $this->html([
            'review' => $review,
            'recipes' => Recipe::getAll(null, [], '`title` ASC'),
            'title' => 'Upraviť hodnotenie'
        ], 'form');
    }

    public function save(Request $request): Response
    {
        $id = $request->post('id');
        $review = $id ? Review::getOne($id) : new Review();

        if (!$review) {
            return $this->redirect($this->url('review.index'));
        }

        // Prepojenie na recept musí ukazovať na existujúci recept
        $recipe = Recipe::getOne((int)$request->post('recipe_id'));
        if (!$recipe) {
            return $this->redirect($this->url('review.index'));
        }

        $rating = max(1, min(5, (int)$request->post('rating')));
        $comment = trim((string)$request->post('comment'));

        $review->setRecipeId($recipe->getId());
        $review->setAuthor(trim((string)$request->post('author')));
        $review->setRating($rating);
        $review->setComment($comment !== '' ? $comment : null);
        if (!$review->getCreatedAt()) {
            $review->setCreatedAt(date('Y-m-d H:i:s'));
        }

        $review->save();

        return $this->redirect($this->url('review.index', ['recipe_id' => $recipe->getId()]));
    }

    public function delete(Request $request): Response
    {
        $review = Review::getOne($request->get('id'));
        $recipeId = null;

        if ($review) {
            $recipeId = $review->getRecipeId();
            $review->delete();
        }

        return $this->redirect($this->url('review.index', $recipeId ? ['recipe_id' => $recipeId] : []));
    }
}
