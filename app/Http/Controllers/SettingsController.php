<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\CustomerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $loginBackgroundPath = ApplicationSetting::loginBackgroundPath();

        return view('pages.settings.index', [
            'reservationHours' => ApplicationSetting::reservationHours(),
            'loginBackgroundPath' => $loginBackgroundPath,
            'loginBackgroundUrl' => $loginBackgroundPath ? route('login.background', [], false) : null,
            'customerGroups' => CustomerGroup::query()
                ->withCount('customers')
                ->ordered()
                ->get(),
        ]);
    }

    public function updateReservation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reservation_hours' => ['required', 'integer', 'min:1', 'max:720'],
        ]);

        ApplicationSetting::putValue(
            'reservation_hours',
            $data['reservation_hours'],
            'integer',
            'Standard-Reservierungsdauer für neue Angebote in Stunden'
        );

        return redirect()
            ->route('settings.index')
            ->with('success', 'Reservierungsdauer wurde gespeichert.');
    }

    public function updateLoginAppearance(Request $request): RedirectResponse
    {
        $request->validate([
            'login_background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_login_background' => ['nullable', 'boolean'],
        ]);

        $currentPath = ApplicationSetting::loginBackgroundPath();

        if ($request->boolean('remove_login_background')) {
            if ($currentPath) {
                Storage::disk('public')->delete($currentPath);
            }

            ApplicationSetting::putValue(
                'login_background_path',
                '',
                'string',
                'Hintergrundbild der Anmeldeseite'
            );

            return redirect()
                ->route('settings.index')
                ->with('success', 'Login-Hintergrund wurde entfernt.');
        }

        if ($request->hasFile('login_background')) {
            if ($currentPath) {
                Storage::disk('public')->delete($currentPath);
            }

            $path = $request->file('login_background')->store('login-backgrounds', 'public');

            ApplicationSetting::putValue(
                'login_background_path',
                $path,
                'string',
                'Hintergrundbild der Anmeldeseite'
            );

            return redirect()
                ->route('settings.index')
                ->with('success', 'Login-Hintergrund wurde gespeichert.');
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'Keine Änderung am Login-Hintergrund vorgenommen.');
    }

    public function loginBackground()
    {
        $path = ApplicationSetting::loginBackgroundPath();

        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    public function loginLogo()
    {
        $path = ApplicationSetting::loginLogoPath();

        abort_unless(
            $path && Storage::disk('public')->exists($path),
            404
        );

        return Storage::disk('public')->response($path);
    }
}
