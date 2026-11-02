<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class HomeController
 *
 * Handles actions related to the home page and public recipe views.
 *
 * @package App\Controllers
 */
class HomeController extends BaseController
{
    /**
     * Authorizes controller actions based on the specified action name.
     *
     * @param Request $request
     * @param string $action The action name to authorize.
     * @return bool Returns true, allowing all actions.
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Displays the main page (Moje recepty / Wireframe).
     * URL: / alebo ?c=home&a=index
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Vykreslí súbor App/Views/Home/index.view.php
        return $this->html([
            'title' => 'SmartRecipe Hub - Moje recepty'
        ]);
    }

    /**
     * Displays the recipes list page.
     * URL: ?c=home&a=recipes
     *
     * @param Request $request
     * @return Response
     */
    public function recipes(Request $request): Response
    {
        // Vykreslí súbor App/Views/Home/recipes.view.php
        return $this->html([
            'title' => 'SmartRecipe Hub - Recepty'
        ]);
    }
}