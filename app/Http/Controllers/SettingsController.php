<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('pages.settings.index', [
            'reservationHours' => ApplicationSetting::reservationHours(),
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
}
