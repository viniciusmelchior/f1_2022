<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Site\PDFController;
use App\Http\Controllers\Site\PistaController;
use App\Support\Impersonation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps an administrator who is viewing as another user from changing anything.
 *
 * Every request that is not GET or HEAD is rejected, except leaving the view.
 * This app also changes data through some GET routes (the "delete" links, the
 * author registration and the test mail), so those are rejected as well.
 */
class ReadOnlyWhileImpersonating
{
    /** GET actions that change data or send mail, besides every `destroy` action. */
    private const MUTATING_GET_ACTIONS = [
        PistaController::class.'@adicionarAutor',
        PDFController::class.'@index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Impersonation::current($request) === null || $request->routeIs(Impersonation::LEAVE_ROUTE)) {
            return $next($request);
        }

        if ($this->isReadOnlyRequest($request)) {
            return $next($request);
        }

        $message = 'Somente leitura: você está vendo como outro usuário. Volte para a sua conta para alterar dados.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->back(302, [], route('home'))->with('impersonation_blocked', $message);
    }

    private function isReadOnlyRequest(Request $request): bool
    {
        // method() is what the router dispatches on, including a spoofed `_method` (PUT, PATCH, DELETE).
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        $action = $request->route()?->getActionName() ?? '';

        return ! str_ends_with($action, '@destroy') && ! in_array($action, self::MUTATING_GET_ACTIONS, true);
    }
}
