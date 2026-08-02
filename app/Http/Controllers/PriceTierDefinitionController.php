<?php

namespace App\Http\Controllers;

use App\Models\PriceTierDefinition;
use App\Models\ProductPriceTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PriceTierDefinitionController extends Controller
{
    public function updateAll(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'tiers' => ['required', 'array', 'min:1', 'max:50'],
            'tiers.*.id' => ['nullable', 'integer', 'distinct', 'exists:price_tier_definitions,id'],
            'tiers.*.label' => ['required', 'string', 'max:100'],
            'tiers.*.min_grams' => ['required', 'integer', 'min:1', 'max:5000'],
            'tiers.*.max_grams' => ['required', 'integer', 'min:1', 'max:5000'],
        ], [
            'tiers.required' => 'Mindestens eine Preisstufe ist erforderlich.',
            'tiers.min' => 'Mindestens eine Preisstufe ist erforderlich.',
            'tiers.*.label.required' => 'Jede Preisstufe benötigt eine Bezeichnung.',
            'tiers.*.min_grams.required' => 'Das Startgewicht ist erforderlich.',
            'tiers.*.max_grams.required' => 'Das Endgewicht ist erforderlich.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $tiers = $this->normalizedTiers($request->input('tiers', []));

            if ($tiers->isEmpty()) {
                return;
            }

            $duplicateLabels = $tiers
                ->groupBy(fn (array $tier) => mb_strtolower($tier['label']))
                ->filter(fn (Collection $items) => $items->count() > 1);

            if ($duplicateLabels->isNotEmpty()) {
                $validator->errors()->add('tiers', 'Die Bezeichnungen der Preisstufen müssen eindeutig sein.');
            }

            foreach ($tiers as $tier) {
                if ($tier['min_grams'] > $tier['max_grams']) {
                    $validator->errors()->add(
                        'tiers',
                        "Bei „{$tier['label']}“ darf das Startgewicht nicht größer als das Endgewicht sein."
                    );
                }
            }

            $ordered = $tiers->sortBy('min_grams')->values();

            for ($index = 1; $index < $ordered->count(); $index++) {
                $previous = $ordered[$index - 1];
                $current = $ordered[$index];
                $expectedStart = $previous['max_grams'] + 1;

                if ($current['min_grams'] <= $previous['max_grams']) {
                    $validator->errors()->add(
                        'tiers',
                        "Die Bereiche „{$previous['label']}“ und „{$current['label']}“ überschneiden sich."
                    );
                } elseif ($current['min_grams'] !== $expectedStart) {
                    $validator->errors()->add(
                        'tiers',
                        "Zwischen „{$previous['label']}“ und „{$current['label']}“ besteht eine Lücke. Die nächste Stufe muss bei {$expectedStart} Gramm beginnen."
                    );
                }
            }
        });

        $data = $validator->validate();
        $tiers = $this->normalizedTiers($data['tiers'])->sortBy('min_grams')->values();

        DB::transaction(function () use ($tiers): void {
            $existing = PriceTierDefinition::query()->get()->keyBy('id');
            $submittedIds = $tiers
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            $existing
                ->reject(fn (PriceTierDefinition $definition) => $submittedIds->contains($definition->id))
                ->each(function (PriceTierDefinition $definition): void {
                    ProductPriceTier::query()
                        ->where('tier_key', $definition->key)
                        ->delete();

                    $definition->delete();
                });

            $usedKeys = PriceTierDefinition::query()->pluck('key')->all();

            foreach ($tiers as $tierData) {
                $definition = ! empty($tierData['id'])
                    ? PriceTierDefinition::query()->findOrFail($tierData['id'])
                    : new PriceTierDefinition([
                        'key' => $this->uniqueKey($tierData['label'], $usedKeys),
                    ]);

                if (! $definition->exists) {
                    $usedKeys[] = $definition->key;
                }

                $definition->fill([
                    'label' => $tierData['label'],
                    'min_grams' => $tierData['min_grams'],
                    'max_grams' => $tierData['max_grams'],
                ])->save();

                ProductPriceTier::query()
                    ->where('tier_key', $definition->key)
                    ->update([
                        'tier_label' => $definition->label,
                        'min_grams' => $definition->min_grams,
                        'max_grams' => $definition->max_grams,
                    ]);
            }

            ProductPriceTier::synchronizeAll();
        });

        return redirect()
            ->to(route('settings.index') . '#price-tiers')
            ->with('success', 'Die Preisstufen wurden gespeichert und mit allen Produkten synchronisiert.');
    }

    private function normalizedTiers(array $tiers): Collection
    {
        return collect($tiers)
            ->map(fn (array $tier) => [
                'id' => filled($tier['id'] ?? null) ? (int) $tier['id'] : null,
                'label' => trim((string) ($tier['label'] ?? '')),
                'min_grams' => (int) ($tier['min_grams'] ?? 0),
                'max_grams' => (int) ($tier['max_grams'] ?? 0),
            ]);
    }

    private function uniqueKey(string $label, array $usedKeys): string
    {
        $base = Str::slug($label) ?: 'preisstufe';
        $key = $base;
        $suffix = 2;

        while (in_array($key, $usedKeys, true)) {
            $key = $base . '-' . $suffix;
            $suffix++;
        }

        return $key;
    }
}
