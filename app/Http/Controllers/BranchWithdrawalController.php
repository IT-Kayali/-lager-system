<?php

namespace App\Http\Controllers;

use App\Models\BranchWithdrawal;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchWithdrawalController extends Controller
{
    private function authorizeAccess(): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()?->canAccessMenu(['manager', 'warehouse']), 403);
    }

    public function index()
    {
        $this->authorizeAccess();

        $withdrawals = BranchWithdrawal::query()
            ->with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('pages.branch-withdrawals.index', compact('withdrawals'));
    }

    public function create(Request $request)
    {
        $this->authorizeAccess();

        $products = Product::query()
            ->orderBy('name')
            ->get();

        $selectedProduct = null;

        if ($request->filled('product_id')) {
            $selectedProduct = Product::query()->find($request->integer('product_id'));
        }

        return view('pages.branch-withdrawals.create', compact('products', 'selectedProduct'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'note' => ['required', 'string', 'min:3', 'max:5000'],
        ]);

        $quantity = round((float) $validated['quantity'], 3);

        DB::transaction(function () use ($validated, $quantity) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($validated['product_id']);

            $product->refresh();

            $availableBefore = round((float) $product->available_stock, 3);

            if ($quantity > $availableBefore) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'Die Menge ist größer als der verfügbare Bestand. Für die Filiale darf der Mindestbestand genutzt werden, aber nicht bereits reservierte oder nicht vorhandene Ware.',
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
                if ($remaining <= 0) {
                    break;
                }

                $batchQuantity = round((float) $batch->quantity, 3);

                if ($batchQuantity <= 0) {
                    continue;
                }

                $take = min($batchQuantity, $remaining);

                $batch->quantity = round($batchQuantity - $take, 3);
                $batch->save();

                $allocations[] = [
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number ?? null,
                    'quantity' => $take,
                ];

                $remaining = round($remaining - $take, 3);
            }

            if ($remaining > 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'Die Menge konnte nicht vollständig aus den Chargen abgezogen werden. Bitte Chargenbestand prüfen.',
                ]);
            }

            $product->refresh();
            $availableAfter = round((float) $product->available_stock, 3);

            BranchWithdrawal::query()->create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'withdrawal_number' => 'FIL-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
                'branch_name' => $validated['branch_name'] ?: 'Lokales Geschäft / Filiale',
                'quantity' => $quantity,
                'stock_before' => $availableBefore,
                'stock_after' => $availableAfter,
                'batch_allocations' => $allocations,
                'note' => $validated['note'],
            ]);
        });

        return redirect()
            ->route('branch-withdrawals.index')
            ->with('success', 'Filialausgang wurde gespeichert und vom Lagerbestand abgezogen.');
    }
}
