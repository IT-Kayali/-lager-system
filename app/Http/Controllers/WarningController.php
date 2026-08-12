<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\GermanNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WarningController extends Controller
{
    public function index(Request $request): View
    {
        app(\App\Services\ReservationReleaseService::class)->releaseExpired();

        $filter = $request->query('filter', 'warning');
        $allProducts = $this->productRows();

        $summary = [
            'all' => $allProducts->count(),
            'low' => $allProducts->where('status', 'low')->count(),
            'critical' => $allProducts->where('status', 'critical')->count(),
            'warning' => $allProducts->whereIn('status', ['low', 'critical'])->count(),
        ];

        return view('pages.warnings.index', [
            'products' => $this->filteredRows($allProducts, $filter),
            'filter' => $filter,
            'summary' => $summary,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        app(\App\Services\ReservationReleaseService::class)->releaseExpired();

        $filter = $request->query('filter', 'warning');
        $products = $this->filteredRows($this->productRows(), $filter);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bestandswarnungen');

        $sheet->fromArray([[
            'Produktcode',
            'Produkt',
            'Herstellerbezeichnung',
            'Seriennummer',
            'Einheit',
            'Lieferant-Nr.',
            'Lieferant',
            'Gesamtbestand',
            'Reserviert',
            'Verfügbar',
            'Mindestbestand',
            'Warnschwelle',
            'Max. reservierbar',
            'Status',
            'Beschreibung',
        ]]);

        $rowNumber = 2;

        foreach ($products as $row) {
            /** @var Product $product */
            $product = $row['product'];

            $sheet->fromArray([[
                $product->product_code,
                $product->name,
                $product->manufacturer_designation,
                $product->serial_number,
                $product->unit,
                $product->supplierRecord?->supplier_number,
                $product->supplierRecord?->company_name ?: $product->supplier,
                (float) $row['total_stock'],
                (float) $row['reserved_stock'],
                (float) $row['available_stock'],
                (float) $row['minimum_stock'],
                (float) $row['warning_threshold'],
                (float) $row['max_reservable'],
                $this->statusLabel($row['status']),
                $product->description,
            ]], null, 'A' . $rowNumber);

            $rowNumber++;
        }

        if ($rowNumber > 2) {
            foreach (['H', 'I', 'J', 'K', 'L', 'M'] as $column) {
                $sheet->getStyle($column . '2:' . $column . ($rowNumber - 1))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }
        }

        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:O' . max(1, $rowNumber - 1));

        $path = storage_path('app/bestandswarnungen-' . now()->format('Y-m-d-His') . '.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return response()
            ->download($path, 'bestandswarnungen.xlsx')
            ->deleteFileAfterSend(true);
    }

    private function productRows(): Collection
    {
        return Product::query()
            ->with([
                'supplierRecord',
                'batches' => fn ($query) => $query->orderBy('received_at')->orderBy('id'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) {
                return [
                    'product' => $product,
                    'status' => $product->stock_status,
                    'total_stock' => $product->total_stock,
                    'reserved_stock' => $product->reserved_stock,
                    'available_stock' => $product->available_stock,
                    'minimum_stock' => (float) $product->minimum_stock,
                    'warning_threshold' => $product->low_stock_warning_threshold,
                    'max_reservable' => $product->max_reservable,
                ];
            });
    }

    private function filteredRows(Collection $rows, string $filter): Collection
    {
        return $rows
            ->filter(function (array $row) use ($filter) {
                if ($filter === 'critical') {
                    return $row['status'] === 'critical';
                }

                if ($filter === 'low') {
                    return $row['status'] === 'low';
                }

                if ($filter === 'all') {
                    return true;
                }

                return in_array($row['status'], ['low', 'critical'], true);
            })
            ->values();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'critical' => 'Kritisch',
            'low' => 'Niedrig',
            default => 'OK',
        };
    }
}
