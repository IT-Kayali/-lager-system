<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerWalletService
{
    public function balance(Customer $customer): float
    {
        return (float) CustomerWalletTransaction::query()
            ->where('customer_id', $customer->id)
            ->sum('amount');
    }

    public function record(
        Customer $customer,
        string $type,
        float $amount,
        string $note,
        ?User $user = null,
        ?Offer $offer = null
    ): CustomerWalletTransaction {
        return DB::transaction(function () use ($customer, $type, $amount, $note, $user, $offer) {
            Customer::query()
                ->whereKey($customer->id)
                ->lockForUpdate()
                ->first();

            $balanceAfter = $this->balance($customer) + $amount;

            return CustomerWalletTransaction::create([
                'customer_id' => $customer->id,
                'offer_id' => $offer?->id,
                'user_id' => $user?->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'note' => $note,
            ]);
        });
    }

    public function recordManualCredit(Customer $customer, float $amount, string $note, User $user): CustomerWalletTransaction
    {
        return $this->record(
            $customer,
            CustomerWalletTransaction::TYPE_MANUAL_CREDIT,
            abs($amount),
            $note,
            $user
        );
    }

    public function recordManualDebit(Customer $customer, float $amount, string $note, User $user): CustomerWalletTransaction
    {
        return $this->record(
            $customer,
            CustomerWalletTransaction::TYPE_MANUAL_DEBIT,
            -abs($amount),
            $note,
            $user
        );
    }

    public function syncOffer(Offer $offer): void
    {
        $offer->refresh();

        if (! $offer->customer_id || (float) $offer->total <= 0) {
            return;
        }

        $customer = $offer->customer()->first();

        if (! $customer) {
            return;
        }

        if (! in_array($offer->status, [
            Offer::STATUS_CANCELLED,
            Offer::STATUS_RESERVATION_EXPIRED,
        ], true)) {
            $this->ensureOfferDebit($offer, $customer);
        }

        if ($offer->status === Offer::STATUS_IN_PROGRESS) {
            $this->ensureOfferPaymentCredit($offer, $customer);
        }

        if ($offer->status === Offer::STATUS_CANCELLED) {
            $this->ensureOfferCancelAdjustment($offer, $customer);
        }
    }

    private function ensureOfferDebit(Offer $offer, Customer $customer): void
    {
        $exists = CustomerWalletTransaction::query()
            ->where('offer_id', $offer->id)
            ->where('type', CustomerWalletTransaction::TYPE_OFFER_DEBIT)
            ->exists();

        if ($exists) {
            return;
        }

        $this->record(
            $customer,
            CustomerWalletTransaction::TYPE_OFFER_DEBIT,
            -abs((float) $offer->total),
            'Automatischer Abzug durch Angebot ' . $offer->offer_number,
            null,
            $offer
        );
    }

    private function ensureOfferPaymentCredit(Offer $offer, Customer $customer): void
    {
        $exists = CustomerWalletTransaction::query()
            ->where('offer_id', $offer->id)
            ->where('type', CustomerWalletTransaction::TYPE_OFFER_PAYMENT_CREDIT)
            ->exists();

        if ($exists) {
            return;
        }

        $this->record(
            $customer,
            CustomerWalletTransaction::TYPE_OFFER_PAYMENT_CREDIT,
            abs((float) $offer->total),
            'Automatische Gutschrift: Angebot wurde auf In Bearbeitung gesetzt',
            null,
            $offer
        );
    }

    private function ensureOfferCancelAdjustment(Offer $offer, Customer $customer): void
    {
        $hasDebit = CustomerWalletTransaction::query()
            ->where('offer_id', $offer->id)
            ->where('type', CustomerWalletTransaction::TYPE_OFFER_DEBIT)
            ->exists();

        if (! $hasDebit) {
            return;
        }

        $exists = CustomerWalletTransaction::query()
            ->where('offer_id', $offer->id)
            ->where('type', CustomerWalletTransaction::TYPE_OFFER_CANCEL_ADJUSTMENT)
            ->exists();

        if ($exists) {
            return;
        }

        $this->record(
            $customer,
            CustomerWalletTransaction::TYPE_OFFER_CANCEL_ADJUSTMENT,
            abs((float) $offer->total),
            'Automatische Korrektur: Angebot wurde storniert',
            null,
            $offer
        );
    }
}
