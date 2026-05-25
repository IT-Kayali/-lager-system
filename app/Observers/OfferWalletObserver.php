<?php

namespace App\Observers;

use App\Models\Offer;
use App\Services\CustomerWalletService;

class OfferWalletObserver
{
    public function saved(Offer $offer): void
    {
        app(CustomerWalletService::class)->syncOffer($offer);
    }
}
