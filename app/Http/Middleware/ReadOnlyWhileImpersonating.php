<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Site\PDFController;
use App\Http\Controllers\Site\PistaController;
use App\Support\Impersonation;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps an administrator who is viewing as another user from changing anything.
 *
 * Every request that is not GET or HEAD is rejected, except leaving the view
 * and the POST routes named in READ_ONLY_POST_ROUTES. This app also changes
 * data through some GET routes (the "delete" links, the author registration
 * and the test mail), so those are rejected as well.
 *
 * Several pages fetch their tables and charts with POST because the filters
 * travel in the body. Those routes only query and render data; each one was
 * read for writes before it was listed, and while it runs the database
 * rejects any statement that is not a read, so an edit that adds a write to
 * one of them fails instead of changing data for a view-only administrator.
 */
class ReadOnlyWhileImpersonating
{
    /** GET actions that change data or send mail, besides every `destroy` action. */
    private const MUTATING_GET_ACTIONS = [
        PistaController::class.'@adicionarAutor',
        PDFController::class.'@index',
    ];

    /**
     * POST routes that only query and render data, by route name. Add a route here only after reading its action
     * and everything it calls for writes (database, cache, session, files, mail).
     */
    public const READ_ONLY_POST_ROUTES = [
        // /home: season filters and the arrivals / starts / podiums / poles / wins tables
        'ajax.ajaxGetChegadasEquipesPorTemporada',
        'ajax.ajaxGetChegadasPilotosPorTemporada',
        'ajax.ajaxGetPodiosEquipesPorTemporada',
        'ajax.ajaxGetPodiosPilotoPorTemporada',
        'ajax.ajaxGetPolesEquipesPorTemporada',
        'ajax.ajaxGetPolesPilotosPorTemporada',
        'ajax.ajaxGetVitoriasEquipesPorTemporada',
        'ajax.ajaxGetVitoriasPilotoPorTemporada',
        'url_chegada_equipes',
        'url_chegada_pilotos',
        'url_largada_equipes',
        'url_largada_pilotos',
        'buscaResultadosCorrida',
        // driver and team pages, comparison, season classification
        'ajax.ajaxGetStatsEquipePorTemporada',
        'ajax.ajaxGetStatsPilotoPorTemporada',
        'ajax.classificacaoGeralPorTemporada',
        'ajax.comparativos',
        'ajax.getPilotosPorTemporada',
        'fetch.getClassificacaoAposCorrida',
        'post.temporadas.classificacao',
        // country page
        'paises.ajaxGetChegadasEquipes',
        'paises.ajaxGetChegadasPilotos',
        'paises.ajaxGetLargadasEquipes',
        'paises.ajaxGetLargadasPilotos',
        // performance per track, driver and team statistics
        'buscaPerformancePorPista',
        'estatisticas.pilotos.equipes.buscar',
    ];

    /** @var \WeakMap<Connection, \stdClass>|null */
    private static ?\WeakMap $guards = null;

    public function handle(Request $request, Closure $next): Response
    {
        $impersonation = Impersonation::current($request);

        if ($impersonation === null) {
            return $next($request);
        }

        if (! Impersonation::adminIsStillAdmin($impersonation)) {
            Log::warning('Impersonation ended: the administrator is gone or no longer an administrator', [
                'admin_id' => $impersonation['admin_id'],
                'target_id' => $impersonation['target_id'],
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if ($request->routeIs(Impersonation::LEAVE_ROUTE)) {
            return $next($request);
        }

        if ($this->isReadOnlyRequest($request)) {
            return $next($request);
        }

        if ($this->isReadOnlyPost($request)) {
            return $this->withReadOnlyDatabase($request, $next);
        }

        $message = 'Somente leitura: você está vendo como outro usuário. Volte para a sua conta para alterar dados.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->back(302, [], route('home'))->with('impersonation_blocked', $message);
    }

    private function isReadOnlyPost(Request $request): bool
    {
        return $request->method() === 'POST'
            && in_array($request->route()?->getName(), self::READ_ONLY_POST_ROUTES, true);
    }

    /**
     * Runs the request with a database that refuses anything but `select`. The session is saved after this
     * middleware returns, so it is not affected. The check is registered once per connection and switched on and
     * off around the request.
     */
    private function withReadOnlyDatabase(Request $request, Closure $next): Response
    {
        $connection = DB::connection();

        self::$guards ??= new \WeakMap();
        $guard = self::$guards[$connection] ??= $this->registerGuard($connection);

        $guard->active = true;

        try {
            return $next($request);
        } finally {
            $guard->active = false;
        }
    }

    private function registerGuard(Connection $connection): \stdClass
    {
        $guard = new \stdClass();
        $guard->active = false;

        $connection->beforeExecuting(function (string $query) use ($guard): void {
            if ($guard->active && ! preg_match('/^\s*select\b/i', $query)) {
                throw new \LogicException('A read-only view tried to run a statement that is not a select.');
            }
        });

        return $guard;
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
