<?php

namespace App\Services;

use Illuminate\Support\Collection;

class DocumentItemSorter
{
    public function sort(Collection $items, bool $quantityAscending = false): Collection
    {
        return $items
            ->sort(function ($left, $right) use ($quantityAscending): int {
                $priorityComparison = $this->priority($left) <=> $this->priority($right);

                if ($priorityComparison !== 0) {
                    return $priorityComparison;
                }

                $leftQuantity = (float) ($left->quantity ?? 0);
                $rightQuantity = (float) ($right->quantity ?? 0);
                $quantityComparison = $quantityAscending
                    ? $leftQuantity <=> $rightQuantity
                    : $rightQuantity <=> $leftQuantity;

                if ($quantityComparison !== 0) {
                    return $quantityComparison;
                }

                $nameComparison = strnatcasecmp($this->productName($left), $this->productName($right));

                if ($nameComparison !== 0) {
                    return $nameComparison;
                }

                return (int) ($left->id ?? 0) <=> (int) ($right->id ?? 0);
            })
            ->values();
    }

    private function priority($item): int
    {
        $product = $item->product ?? null;

        if (! $product) {
            return PHP_INT_MAX;
        }

        if (method_exists($product, 'relationLoaded') && $product->relationLoaded('categories')) {
            $categories = $product->getRelation('categories');
        } else {
            $categories = collect($product->categories ?? []);
        }

        $priority = collect($categories)
            ->pluck('priority')
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (int) $value)
            ->min();

        return $priority ?? PHP_INT_MAX;
    }

    private function productName($item): string
    {
        return trim((string) (
            $item->product_name
            ?? $item->product?->name
            ?? ''
        ));
    }
}
