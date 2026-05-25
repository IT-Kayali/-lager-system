<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerWalletTransactionController extends Controller
{
    public function store(Request $request, Customer $customer, CustomerWalletService $walletService): RedirectResponse
    {
        $data = $request->validate([
            'direction' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'note' => ['required', 'string', 'min:3', 'max:3000'],
        ]);

        if ($data['direction'] === 'credit') {
            $walletService->recordManualCredit(
                $customer,
                (float) $data['amount'],
                $data['note'],
                $request->user()
            );
        } else {
            $walletService->recordManualDebit(
                $customer,
                (float) $data['amount'],
                $data['note'],
                $request->user()
            );
        }

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Guthaben-Buchung wurde gespeichert.');
    }
}
