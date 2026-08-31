<?php

namespace App\Http\Controllers;

use App\Models\BranchWithdrawal;
use App\Models\BranchWithdrawalItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BranchWithdrawalController extends Controller
{
    private function authorizeAccess(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()?->canAccessMenu(['manager', 'warehouse', 'sales']), 403);
    }

    private function authorizeManagerAction(): void
    {
        $this->authorizeAccess();
        abort_unless(auth()->user()?->isManager(), 403);
    }

    private function authorizeCreateAction(): void
    {
        $this->authorizeAccess();
        abort_unless(auth()->user()?->isManager() || auth()->user()?->isSales(), 403);
    }

    private function indexRouteName(): string
    {
        return auth()->user()?->isSales()
            ? 'sales.branch-withdrawals.index'
            : 'branch-withdrawals.index';
    }

    public function index(): View
    {
        $this->authorizeAccess();

        $withdrawals = BranchWithdrawal::query()
            ->with(['items.product', 'user', 'processor'])
            ->latest()
            ->paginate(20);

        return view('pages.branch-withdrawals.index', compact('withdrawals'));
    }

    public function create(Request $request): View
    {
        $this->authorizeCreateAction();

        $selectedProduct = $request->filled('product_id')
            ? Product::query()->find($request->integer('product_id'))
            : null;

        $withdrawal = new BranchWithdrawal([
            'branch_name' => null,
            'status' => BranchWithdrawal::STATUS_OPEN,
        ]);

        $formItems = collect([
            [
                'product_id' => $selectedProduct?->id,
                'quantity' => '',
            ],
        ]);

        return view('pages.branch-withdrawals.create', [
            'withdrawal' => $withdrawal,
            'products' => $this->products(),
            'formItems' => $formItems,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCreateAction();
        $data = $this->validatedData($request);

        if (auth()->user()?->isSales()) {
            $data['status'] = BranchWithdrawal::STATUS_OPEN;
        }

        DB::transaction(function () use ($data): void {
            if ($data['status'] !== BranchWithdrawal::STATUS_CANCELLED) {
                $this->validateItemsAvailability($data['items']);
            }

            $firstItem = $data['items'][0];

            $withdrawal = BranchWithdrawal::query()->create([
                'product_id' => $firstItem['product_id'],
                'user_id' => auth()->id(),
                'withdrawal_number' => $this->nextNumber(),
                'branch_name' => $data['branch_name'],
                'status' => $data['status'],
                'quantity' => $firstItem['quantity'],
                'stock_before' => 0,
                'stock_after' => 0,
                'batch_allocations' => [],
                'note' => $data['note'] ?? '',
            ]);

            $this->replaceItems($withdrawal, $data['items']);

            if ($withdrawal->isIssued()) {
                $this->issueWithdrawal($withdrawal);
            }

            $this->syncLegacyFields($withdrawal);
        });

        return redirect()
            ->route($this->indexRouteName())
            ->with('success', 'Filialausgang wurde gespeichert. Die Ware ist reserviert.');
    }

    public function edit(BranchWithdrawal $branchWithdrawal): View
    {
        $this->authorizeManagerAction();
        $branchWithdrawal->load('items.product');

        return view('pages.branch-withdrawals.edit', [
            'withdrawal' => $branchWithdrawal,
            'products' => $this->products(),
            'formItems' => $branchWithdrawal->items->map(fn (BranchWithdrawalItem $item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ]),
        ]);
    }

    public function update(Request $request, BranchWithdrawal $branchWithdrawal): RedirectResponse
    {
        $this->authorizeAccess();

        abort_if(auth()->user()?->isSales(), 403);

        if (! auth()->user()?->isManager()) {
            return $this->updateWarehouseStatus($request, $branchWithdrawal);
        }

        $data = $this->validatedData($request);

        DB::transaction(function () use ($branchWithdrawal, $data): void {
            $withdrawal = BranchWithdrawal::query()
                ->lockForUpdate()
                ->findOrFail($branchWithdrawal->id);

            $withdrawal->load('items');

            if ($withdrawal->isIssued()) {
                $this->rollbackWithdrawal($withdrawal);
                $withdrawal->refresh();
                $withdrawal->load('items');
            }

            if ($data['status'] !== BranchWithdrawal::STATUS_CANCELLED) {
                $this->validateItemsAvailability($data['items'], $withdrawal->id);
            }

            $withdrawal->update([
                'branch_name' => $data['branch_name'],
                'status' => $data['status'],
                'note' => $data['note'] ?? '',
                'processed_by' => null,
                'processed_at' => null,
            ]);

            $this->replaceItems($withdrawal, $data['items']);

            if ($withdrawal->isIssued()) {
                $this->issueWithdrawal($withdrawal);
            }

            $this->syncLegacyFields($withdrawal);
        });

        return redirect()
            ->route('branch-withdrawals.index')
            ->with('success', 'Filialausgang und Lagerbestand wurden aktualisiert.');
    }

    private function updateWarehouseStatus(Request $request, BranchWithdrawal $branchWithdrawal): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(BranchWithdrawal::statusLabels()))],
        ]);

        DB::transaction(function () use ($branchWithdrawal, $data): void {
            $withdrawal = BranchWithdrawal::query()
                ->lockForUpdate()
                ->findOrFail($branchWithdrawal->id);

            $withdrawal->load('items');

            $oldStatus = $withdrawal->status;
            $newStatus = $data['status'];

            if ($oldStatus === $newStatus) {
                return;
            }

            if ($oldStatus === BranchWithdrawal::STATUS_ISSUED && $newStatus !== BranchWithdrawal::STATUS_ISSUED) {
                $this->rollbackWithdrawal($withdrawal);
                $withdrawal->refresh();
            }

            $withdrawal->update([
                'status' => $newStatus,
                'processed_by' => null,
                'processed_at' => null,
            ]);

            if ($newStatus === BranchWithdrawal::STATUS_ISSUED && $oldStatus !== BranchWithdrawal::STATUS_ISSUED) {
                $this->issueWithdrawal($withdrawal);
            }

            $this->syncLegacyFields($withdrawal);
        });

        return redirect()
            ->route('branch-withdrawals.index')
            ->with('success', 'Status des Filialausgangs wurde aktualisiert.');
    }

    public function destroy(BranchWithdrawal $branchWithdrawal): RedirectResponse
    {
        $this->authorizeManagerAction();

        DB::transaction(function () use ($branchWithdrawal): void {
            $withdrawal = BranchWithdrawal::query()
                ->lockForUpdate()
                ->findOrFail($branchWithdrawal->id);

            $withdrawal->load('items');

            if ($withdrawal->isIssued()) {
                $this->rollbackWithdrawal($withdrawal);
            }

            $withdrawal->delete();
        });

        return redirect()
            ->route('branch-withdrawals.index')
            ->with('success', 'Filialausgang wurde gelöscht. Reservierungen wurden freigegeben und bereits ausgegebene Mengen zurückgebucht.');
    }

    private function validatedData(Request $request): array
    {
        $items = collect($request->input('items', []))
            ->filter(fn ($item) => is_array($item))
            ->filter(fn (array $item) => filled($item['product_id'] ?? null) || filled($item['quantity'] ?? null))
            ->values()
            ->all();

        $request->merge(['items' => $items]);

        return $request->validate([
            'branch_name' => ['required', Rule::in(array_keys(BranchWithdrawal::branches()))],
            'status' => ['required', Rule::in(array_keys(BranchWithdrawal::statusLabels()))],
            'note' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999'],
        ], [
            'branch_name.required' => 'Bitte wähle eine Filiale aus.',
            'items.required' => 'Mindestens eine Produktposition ist erforderlich.',
            'items.min' => 'Mindestens eine Produktposition ist erforderlich.',
            'items.*.product_id.required' => 'Bitte wähle für jede Position ein Produkt aus.',
            'items.*.product_id.distinct' => 'Jedes Produkt darf nur einmal im Filialausgang vorkommen.',
            'items.*.quantity.required' => 'Bitte trage für jede Position eine Menge ein.',
            'items.*.quantity.gt' => 'Die Menge muss größer als 0 sein.',
        ]);
    }

    private function validateItemsAvailability(array $items, ?int $excludeWithdrawalId = null): void
    {
        foreach (collect($items)->sortBy('product_id') as $item) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail((int) $item['product_id']);

            $requested = round((float) $item['quantity'], 3);
            $totalStock = round((float) ProductBatch::query()
                ->where('product_id', $product->id)
                ->sum('quantity'), 3);

            $offerReserved = round((float) DB::table('offer_items')
                ->join('offers', 'offers.id', '=', 'offer_items.offer_id')
                ->where('offer_items.product_id', $product->id)
                ->whereIn('offers.status', \App\Models\Offer::RESERVING_STATUSES)
                ->sum('offer_items.quantity'), 3);

            $branchReservedQuery = DB::table('branch_withdrawal_items')
                ->join('branch_withdrawals', 'branch_withdrawals.id', '=', 'branch_withdrawal_items.branch_withdrawal_id')
                ->where('branch_withdrawal_items.product_id', $product->id)
                ->whereIn('branch_withdrawals.status', BranchWithdrawal::RESERVING_STATUSES);

            if ($excludeWithdrawalId !== null) {
                $branchReservedQuery->where('branch_withdrawals.id', '!=', $excludeWithdrawalId);
            }

            $branchReserved = round((float) $branchReservedQuery->sum('branch_withdrawal_items.quantity'), 3);
            $available = max(0, round($totalStock - $offerReserved - $branchReserved, 3));

            if ($requested > $available) {
                throw ValidationException::withMessages([
                    'items' => "Für „{$product->name}“ sind nur "
                        . \App\Support\GermanNumber::format($available)
                        . ' ' . $product->unitLabel('de')
                        . ' verfügbar. Der Filialausgang wurde nicht erstellt.',
                ]);
            }
        }
    }

    private function replaceItems(BranchWithdrawal $withdrawal, array $items): void
    {
        $withdrawal->items()->delete();

        foreach ($items as $item) {
            $withdrawal->items()->create([
                'product_id' => (int) $item['product_id'],
                'quantity' => round((float) $item['quantity'], 3),
                'stock_before' => 0,
                'stock_after' => 0,
                'batch_allocations' => [],
            ]);
        }

        $withdrawal->load('items');
    }

    private function issueWithdrawal(BranchWithdrawal $withdrawal): void
    {
        $withdrawal->loadMissing('items.product');

        foreach ($withdrawal->items->sortBy('product_id') as $item) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($item->product_id);

            $quantity = round((float) $item->quantity, 3);
            $availableBefore = round((float) $product->available_stock, 3);

            if ($quantity > $availableBefore) {
                throw ValidationException::withMessages([
                    'items' => "Für „{$product->name}“ sind nur " . \App\Support\GermanNumber::format($availableBefore) . ' verfügbar.',
                ]);
            }

            $remaining = $quantity;
            $allocations = [];

            $batches = ProductBatch::query()
                ->where('product_id', $product->id)
                ->where('quantity', '>', 0)
                ->orderBy('received_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0.0001) {
                    break;
                }

                $batchQuantity = round((float) $batch->quantity, 3);
                $take = min($batchQuantity, $remaining);

                if ($take <= 0) {
                    continue;
                }

                $batch->update([
                    'quantity' => round($batchQuantity - $take, 3),
                ]);

                $allocations[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'received_at' => $batch->received_at?->toDateString(),
                    'expires_at' => $batch->expires_at?->toDateString(),
                    'quantity' => $take,
                ];

                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => -$take,
                    'reference_type' => BranchWithdrawal::class,
                    'reference_id' => $withdrawal->id,
                    'note' => "Filialausgang {$withdrawal->withdrawal_number} – {$withdrawal->branch_name}",
                ]);

                $remaining = round($remaining - $take, 3);
            }

            if ($remaining > 0.0001) {
                throw ValidationException::withMessages([
                    'items' => "Die Menge für „{$product->name}“ konnte nicht vollständig aus den Chargen entnommen werden.",
                ]);
            }

            $product->refresh();

            $item->update([
                'stock_before' => $availableBefore,
                'stock_after' => round((float) $product->available_stock, 3),
                'batch_allocations' => $allocations,
            ]);
        }

        $withdrawal->update([
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        $withdrawal->load('items');
    }

    private function rollbackWithdrawal(BranchWithdrawal $withdrawal): void
    {
        $withdrawal->loadMissing('items.product');

        foreach ($withdrawal->items->sortBy('product_id') as $item) {
            foreach ($item->batch_allocations ?? [] as $allocation) {
                $quantity = round((float) ($allocation['quantity'] ?? 0), 3);

                if ($quantity <= 0) {
                    continue;
                }

                $batch = ProductBatch::query()
                    ->whereKey($allocation['batch_id'] ?? 0)
                    ->lockForUpdate()
                    ->first();

                if (! $batch) {
                    $batch = ProductBatch::query()->create([
                        'product_id' => $item->product_id,
                        'batch_number' => $allocation['batch_number'] ?? null,
                        'quantity' => 0,
                        'received_at' => $allocation['received_at'] ?? now()->toDateString(),
                        'expires_at' => $allocation['expires_at'] ?? null,
                    ]);
                }

                $batch->update([
                    'quantity' => round((float) $batch->quantity + $quantity, 3),
                ]);

                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'product_batch_id' => $batch->id,
                    'user_id' => auth()->id(),
                    'type' => 'adjustment',
                    'quantity' => $quantity,
                    'reference_type' => BranchWithdrawal::class,
                    'reference_id' => $withdrawal->id,
                    'note' => "Rückbuchung Filialausgang {$withdrawal->withdrawal_number}",
                ]);
            }

            $product = Product::query()->find($item->product_id);

            $item->update([
                'stock_before' => $product ? round((float) $product->available_stock, 3) : 0,
                'stock_after' => $product ? round((float) $product->available_stock, 3) : 0,
                'batch_allocations' => [],
            ]);
        }

        $withdrawal->update([
            'processed_by' => null,
            'processed_at' => null,
        ]);

        $withdrawal->load('items');
    }

    private function syncLegacyFields(BranchWithdrawal $withdrawal): void
    {
        $withdrawal->loadMissing('items');
        $firstItem = $withdrawal->items->first();

        if (! $firstItem) {
            return;
        }

        $withdrawal->update([
            'product_id' => $firstItem->product_id,
            'quantity' => round((float) $withdrawal->items->sum('quantity'), 3),
            'stock_before' => $firstItem->stock_before,
            'stock_after' => $firstItem->stock_after,
            'batch_allocations' => $firstItem->batch_allocations ?? [],
        ]);
    }

    private function products()
    {
        return Product::query()
            ->orderBy('name')
            ->get();
    }

    private function nextNumber(): string
    {
        return 'FIL-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
    }
}
