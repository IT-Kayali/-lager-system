<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OfferFulfillmentService
{
    public function complete(Offer $offer): void
    {
        if ($offer->isCompleted() || $offer->completed_at) {
            throw new RuntimeException('Dieses Angebot wurde bereits erledigt.');
        }

        if ($offer->isFinal()) {
            throw new RuntimeException('Dieses Angebot ist bereits abgeschlossen.');
        }

        DB::transaction(function () use ($offer) {
            $offer = Offer::query()
                ->with('items.product')
                ->lockForUpdate()
                ->findOrFail($offer->id);

            foreach ($offer->items as $item) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item->product_id);

                $this->deductProductByFifo(
                    product: $product,
                    quantity: (float) $item->quantity,
                    offer: $offer
                );
            }

            $offer->update([
                'status' => Offer::STATUS_COMPLETED,
                'completed_at' => now(),
                'reservation_released_at' => now(),
            ]);

            ActivityLog::record('offer.completed', $offer, [
                'offer_number' => $offer->offer_number,
                'total' => (float) $offer->total,
            ]);
        });
    }

    public function cancel(Offer $offer, string $status = Offer::STATUS_CANCELLED): void
    {
        if ($offer->isFinal()) {
            throw new RuntimeException('Dieses Angebot ist bereits abgeschlossen.');
        }

        $offer->update([
            'status' => $status,
            'cancelled_at' => now(),
            'reservation_released_at' => now(),
        ]);

        ActivityLog::record(
            $status === Offer::STATUS_CANCELLED ? 'offer.cancelled' : 'offer.reservation_expired',
            $offer,
            [
                'offer_number' => $offer->offer_number,
            ]
        );
    }

    private function deductProductByFifo(Product $product, float $quantity, Offer $offer): void
    {
        $totalStock = (float) $product->batches()->sum('quantity');

        if ($quantity > $totalStock) {
            throw new RuntimeException(
                'Nicht genug Bestand für ' . $product->product_code . ' — ' . $product->name .
                '. Benötigt: ' . number_format($quantity, 3, ',', '.') .
                ', vorhanden: ' . number_format($totalStock, 3, ',', '.')
            );
        }

        $remaining = $quantity;

        $batches = ProductBatch::query()
            ->where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->orderBy('received_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $availableInBatch = (float) $batch->quantity;
            $deduct = min($availableInBatch, $remaining);
            $newQuantity = round($availableInBatch - $deduct, 3);

            if ($newQuantity < 0) {
                throw new RuntimeException('FIFO-Abbuchung würde eine negative Charge erzeugen.');
            }

            $batch->update([
                'quantity' => $newQuantity,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'product_batch_id' => $batch->id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => -$deduct,
                'reference_type' => Offer::class,
                'reference_id' => $offer->id,
                'note' => 'FIFO-Abbuchung durch Erledigung von ' . $offer->offer_number,
            ]);

            $remaining = round($remaining - $deduct, 3);
        }

        if ($remaining > 0.0001) {
            throw new RuntimeException('FIFO-Abbuchung konnte nicht vollständig abgeschlossen werden.');
        }
    }
}
