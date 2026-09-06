<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferInternalNoteController extends Controller
{
    public function store(Request $request, Offer $offer): RedirectResponse
    {
        abort_unless(
            auth()->user()?->hasRole([
                \App\Models\User::ROLE_MANAGER,
                \App\Models\User::ROLE_SALES,
                \App\Models\User::ROLE_WAREHOUSE,
            ]),
            403,
            'Keine Berechtigung für interne Angebotsnotizen.'
        );

        $data = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
        ]);

        $note = $offer->internalNotes()->create([
            'user_id' => auth()->id(),
            'note' => trim($data['note']),
        ]);

        ActivityLog::record('offer.internal_note.created', $offer, [
            'offer_number' => $offer->offer_number,
            'note_id' => $note->id,
        ]);

        return back()->with('success', 'Interne Notiz wurde hinzugefügt.');
    }
}
