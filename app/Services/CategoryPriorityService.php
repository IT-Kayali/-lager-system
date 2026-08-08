<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;

class CategoryPriorityService
{
    private const TEMP_OFFSET = 10_000_000;

    public function nextPriority(): int
    {
        return ((int) ProductCategory::query()->max('priority')) + 1;
    }

    public function create(array $attributes): ProductCategory
    {
        return DB::transaction(function () use ($attributes): ProductCategory {
            $priority = (int) $attributes['priority'];
            $this->shiftRangeUp($priority);

            return ProductCategory::create($attributes);
        });
    }

    public function update(ProductCategory $category, array $attributes): void
    {
        DB::transaction(function () use ($category, $attributes): void {
            $oldPriority = $category->priority !== null ? (int) $category->priority : null;
            $newPriority = (int) $attributes['priority'];

            if ($oldPriority === null) {
                $this->shiftRangeUp($newPriority, null, $category->id);
                $category->update($attributes);

                return;
            }

            if ($oldPriority === $newPriority) {
                $category->update($attributes);

                return;
            }

            ProductCategory::query()
                ->whereKey($category->id)
                ->update(['priority' => null]);

            if ($newPriority < $oldPriority) {
                $this->shiftRangeUp($newPriority, $oldPriority - 1, $category->id);
            } else {
                $this->shiftRangeDown($oldPriority + 1, $newPriority, $category->id);
            }

            $category->refresh();
            $category->update($attributes);
        });
    }

    public function delete(ProductCategory $category): void
    {
        DB::transaction(function () use ($category): void {
            $priority = $category->priority !== null ? (int) $category->priority : null;
            $category->delete();

            if ($priority !== null) {
                $this->shiftRangeDown($priority + 1);
            }
        });
    }

    private function shiftRangeUp(int $from, ?int $to = null, ?int $excludeId = null): void
    {
        $query = ProductCategory::query()
            ->whereNotNull('priority')
            ->where('priority', '>=', $from);

        if ($to !== null) {
            $query->where('priority', '<=', $to);
        }

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $query->increment('priority', self::TEMP_OFFSET);

        $shifted = ProductCategory::query()
            ->where('priority', '>=', $from + self::TEMP_OFFSET);

        if ($to !== null) {
            $shifted->where('priority', '<=', $to + self::TEMP_OFFSET);
        }

        if ($excludeId !== null) {
            $shifted->where('id', '!=', $excludeId);
        }

        $shifted->decrement('priority', self::TEMP_OFFSET - 1);
    }

    private function shiftRangeDown(int $from, ?int $to = null, ?int $excludeId = null): void
    {
        $query = ProductCategory::query()
            ->whereNotNull('priority')
            ->where('priority', '>=', $from);

        if ($to !== null) {
            $query->where('priority', '<=', $to);
        }

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $query->increment('priority', self::TEMP_OFFSET);

        $shifted = ProductCategory::query()
            ->where('priority', '>=', $from + self::TEMP_OFFSET);

        if ($to !== null) {
            $shifted->where('priority', '<=', $to + self::TEMP_OFFSET);
        }

        if ($excludeId !== null) {
            $shifted->where('id', '!=', $excludeId);
        }

        $shifted->decrement('priority', self::TEMP_OFFSET + 1);
    }
}
