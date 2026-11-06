<?php

namespace App\Support;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * State of an administrator who is viewing the site as another user.
 *
 * The session stores the administrator's id together with the id of the user
 * being viewed. The state counts only while that user is the one logged in:
 * if anyone else logs in on the same session (or nobody is logged in), the
 * leftover entry is dropped, so it can never turn a later login into a way
 * back to the administrator's account.
 */
final class Impersonation
{
    public const SESSION_KEY = 'impersonation';

    /** The route a user may POST while viewing as another user. */
    public const LEAVE_ROUTE = 'usuarios.voltar';

    /**
     * @return array{admin_id: int, target_id: int}|null
     */
    public static function current(Request $request): ?array
    {
        $session = $request->hasSession() ? $request->session() : null;

        if ($session === null || ! $session->has(self::SESSION_KEY)) {
            return null;
        }

        $state = $session->get(self::SESSION_KEY);

        if (is_array($state)
            && isset($state['admin_id'], $state['target_id'])
            && Auth::check()
            && (int) Auth::id() === (int) $state['target_id']
        ) {
            return ['admin_id' => (int) $state['admin_id'], 'target_id' => (int) $state['target_id']];
        }

        $session->forget(self::SESSION_KEY);

        return null;
    }

    /**
     * Whether the administrator who started the view still exists and is still an administrator.
     * When not, the view must end: the viewed user's session would otherwise outlive the administrator's rights.
     *
     * @param  array{admin_id: int, target_id: int}  $impersonation
     */
    public static function adminIsStillAdmin(array $impersonation): bool
    {
        $admin = User::find($impersonation['admin_id']);

        return $admin !== null && Gate::forUser($admin)->allows('admin');
    }
}
