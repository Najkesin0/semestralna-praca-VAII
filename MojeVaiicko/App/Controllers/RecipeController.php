<?php

namespace App\Controllers;

use App\Models\Recipe;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class RecipeController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    public function index(Request $request): Response
    {
        $recipes = Recipe::getAll();
        return $this->html([
            'recipes' => $recipes,
            'title' => 'SmartRecipe Hub - Recepty'
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->html([
            'recipe' => new Recipe(),
            'title' => 'Pridať recept'
        ], 'form');
    }

    public function edit(Request $request): Response
    {
        $id = $request->get('id');
        $recipe = Recipe::getOne($id);

        if (!$recipe) {
            return $this->redirect($this->url('recipe.index'));
        }

        return $this->html([
            'recipe' => $recipe,
            'title' => 'Upraviť recept'
        ], 'form');
    }

    public function save(Request $request): Response
    {
        $id = $request->post('id');

        if ($id) {
            $recipe = Recipe::getOne($id);
        } else {
            $recipe = new Recipe();
        }

        $recipe->setTitle($request->post('title'));
        $recipe->setCategory($request->post('category'));
        $recipe->setPrepTime((int)$request->post('prep_time'));
        $recipe->setCalories((int)$request->post('calories'));

        $recipe->save();

        return $this->redirect($this->url('recipe.index'));
    }

    public function delete(Request $request): Response
    {
        $id = $request->get('id');
        $recipe = Recipe::getOne($id);

        if ($recipe) {
            $recipe->delete();
        }

        return $this->redirect($this->url('recipe.index'));
    }
}