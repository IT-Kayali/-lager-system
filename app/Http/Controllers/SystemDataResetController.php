<?php

namespace App\Http\Controllers;

use App\Services\SystemDataResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SystemDataResetController extends Controller
{
    public function store(
        Request $request,
        SystemDataResetService $resetService
    ): RedirectResponse {
        abort_unless($request->user()?->isAdmin(), 403);

        $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'confirmation' => [
                'required',
                'string',
                Rule::in(['SYSTEM RESETTEN']),
            ],
            'acknowledge' => [
                'required',
                'accepted',
            ],
        ], [
            'current_password.required' => 'Bitte gib dein aktuelles Admin-Passwort ein.',
            'current_password.current_password' => 'Das eingegebene Passwort ist nicht korrekt.',
            'confirmation.required' => 'Bitte gib den Bestätigungstext ein.',
            'confirmation.in' => 'Bitte gib exakt „SYSTEM RESETTEN“ ein.',
            'acknowledge.accepted' => 'Du musst bestätigen, dass die Geschäftsdaten dauerhaft gelöscht werden.',
        ]);

        $counts = $resetService->reset(
            (int) $request->user()->id,
            $request->ip(),
        );

        $deleted = array_sum($counts);

        return redirect()
            ->to(route('settings.index') . '#system-data-reset')
            ->with(
                'success',
                number_format($deleted, 0, ',', '.')
                . ' Geschäftsdaten wurden erfolgreich zurückgesetzt.'
            );
    }
}
