<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Impersonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    /**
     * Lists the users with their race and result counts (two aggregate subqueries, no per-row queries).
     */
    public function index()
    {
        $usuarios = User::query()
            ->withCount(['corridas', 'resultados'])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        foreach ($usuarios as $usuario) {
            $usuario->is_admin = Gate::forUser($usuario)->allows('admin');
        }

        return view('site.usuarios.index', compact('usuarios'));
    }

    /**
     * Logs the administrator in as the given user, read-only (see ReadOnlyWhileImpersonating).
     */
    public function entrarComo(Request $request, $id)
    {
        $admin = $request->user();

        // No nesting: a view can only start from the administrator's own account.
        abort_if(Impersonation::current($request) !== null, 403);

        $target = User::findOrFail($id);

        abort_if($target->is($admin), 403);
        abort_if(Gate::forUser($target)->allows('admin'), 403);

        Log::info('Impersonation started', ['admin_id' => $admin->id, 'target_id' => $target->id]);

        $request->session()->put(Impersonation::SESSION_KEY, [
            'admin_id' => $admin->id,
            'target_id' => $target->id,
        ]);

        Auth::login($target);
        // New session id (and CSRF token): nothing from before the identity change is reusable.
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    /**
     * Leaves the view and logs the administrator back in.
     */
    public function voltar(Request $request)
    {
        $impersonation = Impersonation::current($request);

        if ($impersonation === null) {
            return redirect()->route('home');
        }

        $admin = User::find($impersonation['admin_id']);

        $request->session()->forget(Impersonation::SESSION_KEY);

        if ($admin === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        Log::info('Impersonation ended', ['admin_id' => $admin->id, 'target_id' => $impersonation['target_id']]);

        Auth::login($admin);
        // New session id (and CSRF token): nothing from before the identity change is reusable.
        $request->session()->regenerate();

        return Gate::forUser($admin)->allows('admin')
            ? redirect()->route('usuarios.index')
            : redirect()->route('home');
    }
}
