<?php

namespace App\Core;

use App\Helpers\Ui;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Spoločný predok kontrolérov aplikácie (nie je v App\Controllers, aby sa nedal zavolať cez URL).
 */
abstract class AppController extends BaseController
{
    /**
     * ID prihláseného používateľa alebo null.
     */
    protected function userId(): ?int
    {
        $identity = $this->user->getIdentity();
        return ($identity !== null && method_exists($identity, 'getId')) ? $identity->getId() : null;
    }

    protected function flash(string $message, string $type = 'success'): void
    {
        Ui::flash($message, $type);
    }

    /**
     * Presmeruje späť na interný odkaz z parametra `return` (len relatívne „?c=…“), inak na $fallback.
     */
    protected function redirectBack(Request $request, string $fallback): Response
    {
        $return = (string)($request->value('return') ?? '');
        if ($return !== '' && str_starts_with($return, '?') && !str_contains($return, "\n")) {
            return $this->redirect($return);
        }
        return $this->redirect($fallback);
    }
}
