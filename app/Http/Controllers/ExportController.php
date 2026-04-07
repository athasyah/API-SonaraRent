<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\Instrument;
use App\Models\Rental;
use App\Models\Review;
use App\Models\User;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\RentalsExport;
use App\Exports\InstrumentsExport;
use App\Exports\UsersExport;
use App\Exports\ReviewsExport;
use App\Exports\PenaltiesExport;
use App\Models\Penalty;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    /**
     * Export Rentals
     */
    public function rentals(Request $request)
    {
        try {
            $query = Rental::query()
                ->with(['customer', 'details', 'penalty'])
                ->orderBy('created_at', 'desc');

            $format = $request->input('format', 'pdf');
            $hideStatus = $request->input('hide_status') == 1;
            $stream = $request->input('stream') == 1;

            if ($hideStatus) {
                $query->where('status', 'returned');
            } elseif ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->date_from) {
                $query->where('rent_date', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->where('return_date', '<=', $request->date_to);
            }

            $data = $query->get();

            if ($format === 'preview') {
                $export = new RentalsExport($data, $hideStatus);
                return response()->json([
                    'success' => true,
                    'headers' => $export->headings(),
                    'data' => $data->map(fn($item) => $export->map($item))
                ]);
            }

            if ($format === 'excel') {
                return Excel::download(new RentalsExport($data, $hideStatus), 'laporan-rental-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.rentals', [
                'data' => $data,
                'hideStatus' => $hideStatus
            ]);
            $pdf->setPaper('a4', 'landscape');

            if ($stream) {
                return $pdf->stream('laporan-rental-' . now()->format('Y-m-d') . '.pdf');
            }

            return $pdf->download('laporan-rental-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            return Response::Error('Gagal export data rental', $th->getMessage());
        }
    }

    /**
     * Export Instruments
     */
    public function instruments(Request $request)
    {
        try {
            $query = Instrument::query()
                ->with(['category'])
                ->orderBy('name');

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $data = $query->get();
            $format = $request->input('format', 'pdf');
            $stream = $request->input('stream') == 1;

            if ($format === 'preview') {
                $export = new InstrumentsExport($data);
                return response()->json([
                    'success' => true,
                    'headers' => $export->headings(),
                    'data' => $data->map(fn($item) => $export->map($item))
                ]);
            }

            if ($format === 'excel') {
                return Excel::download(new InstrumentsExport($data), 'laporan-instrumen-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.instruments', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');

            if ($stream) {
                return $pdf->stream('laporan-instrumen-' . now()->format('Y-m-d') . '.pdf');
            }

            return $pdf->download('laporan-instrumen-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            return Response::Error('Gagal export data instrumen', $th->getMessage());
        }
    }

    /**
     * Export Users
     */
    public function users(Request $request)
    {
        try {
            $query = User::query()->orderBy('name');

            if ($request->role) {
                $query->role($request->role);
            }
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $data = $query->get();
            $format = $request->input('format', 'pdf');
            $stream = $request->input('stream') == 1;

            if ($format === 'preview') {
                $export = new UsersExport($data);
                return response()->json([
                    'success' => true,
                    'headers' => $export->headings(),
                    'data' => $data->map(fn($item) => $export->map($item))
                ]);
            }

            if ($format === 'excel') {
                return Excel::download(new UsersExport($data), 'laporan-users-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.users', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');

            if ($stream) {
                return $pdf->stream('laporan-users-' . now()->format('Y-m-d') . '.pdf');
            }

            return $pdf->download('laporan-users-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            \Log::error('Export users failed: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal export data user',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Export Reviews
     */
    public function reviews(Request $request)
    {
        try {
            $query = Review::query()
                ->with(['instrument', 'customer'])
                ->orderBy('created_at', 'desc');

            if ($request->rating) {
                $query->where('rating', $request->rating);
            }

            $data = $query->get();
            $format = $request->input('format', 'pdf');
            $stream = $request->input('stream') == 1;

            if ($format === 'preview') {
                $export = new ReviewsExport($data);
                return response()->json([
                    'success' => true,
                    'headers' => $export->headings(),
                    'data' => $data->map(fn($item) => $export->map($item))
                ]);
            }

            if ($format === 'excel') {
                return Excel::download(new ReviewsExport($data), 'laporan-reviews-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.reviews', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');

            if ($stream) {
                return $pdf->stream('laporan-reviews-' . now()->format('Y-m-d') . '.pdf');
            }

            return $pdf->download('laporan-reviews-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            return Response::Error('Gagal export data review', $th->getMessage());
        }
    }

    /**
     * Export Revenue Report
     */
    public function revenue(Request $request)
    {
        try {
            $dateFromReq = $request->date_from;
            $dateToReq = $request->date_to;

            if ($dateFromReq && (str_contains($dateFromReq, 'T') || str_contains($dateFromReq, ':'))) {
                $dateFrom = Carbon::parse($dateFromReq);
            } else {
                $dateFrom = $dateFromReq ? Carbon::parse($dateFromReq)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
            }

            if ($dateToReq && (str_contains($dateToReq, 'T') || str_contains($dateToReq, ':'))) {
                $dateTo = Carbon::parse($dateToReq);
            } else {
                $dateTo = $dateToReq ? Carbon::parse($dateToReq)->endOfDay() : Carbon::now()->endOfDay();
            }
            $filter = $request->input('filter', '1m'); // 1m, 3m, 6m, 1y
            $format = $request->input('format', 'pdf');
            $stream = $request->input('stream') == 1;

            $isMonthly = $filter === '1t' || $filter === '1y';

            // 1. Summary Stats
            $totalSales = Rental::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'returned')
                ->count();

            $totalRevenue = Rental::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'returned')
                ->sum('total_price');

            // 2. Aggregate Data
            $data = [];
            $current = $dateFrom->copy();

            $isHourly = $dateFrom->diffInHours($dateTo) <= 36; // 1.5 days

            if ($isMonthly) {
                // Group by Month
                while ($current <= $dateTo) {
                    $monthStart = $current->copy()->startOfMonth();
                    $monthEnd = $current->copy()->endOfMonth();
                    if ($monthEnd > $dateTo) $monthEnd = $dateTo->copy();

                    $revenue = Rental::whereBetween('created_at', [$monthStart, $monthEnd])
                        ->where('status', 'returned')
                        ->sum('total_price');

                    $count = Rental::whereBetween('created_at', [$monthStart, $monthEnd])
                        ->where('status', 'returned')
                        ->count();

                    $data[] = [
                        'date_label' => $current->translatedFormat('F Y'),
                        'total' => (float)$revenue,
                        'count' => (int)$count,
                    ];

                    $current->addMonth()->startOfMonth();
                }
            } elseif ($isHourly) {
                // Group by Hour
                while ($current <= $dateTo) {
                    $hourStart = $current->copy()->startOfHour();
                    $hourEnd = $current->copy()->endOfHour();

                    $revenue = Rental::whereBetween('created_at', [$hourStart, $hourEnd])
                        ->where('status', 'returned')
                        ->sum('total_price');

                    $count = Rental::whereBetween('created_at', [$hourStart, $hourEnd])
                        ->where('status', 'returned')
                        ->count();

                    $data[] = [
                        'date_label' => $current->translatedFormat('d M Y, H:i'),
                        'total' => (float)$revenue,
                        'count' => (int)$count,
                    ];

                    $current->addHour();
                }
            } else {
                // Group by Day
                while ($current <= $dateTo) {
                    $dayStart = $current->copy()->startOfDay();
                    $dayEnd = $current->copy()->endOfDay();

                    $revenue = Rental::whereBetween('created_at', [$dayStart, $dayEnd])
                        ->where('status', 'returned')
                        ->sum('total_price');

                    $count = Rental::whereBetween('created_at', [$dayStart, $dayEnd])
                        ->where('status', 'returned')
                        ->count();

                    $data[] = [
                        'date_label' => $current->translatedFormat('d F Y'),
                        'total' => (float)$revenue,
                        'count' => (int)$count,
                    ];

                    $current->addDay();
                }
            }

            $summary = [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
            ];

            $dateRange = $dateFrom->format('d/m/Y') . ' - ' . $dateTo->format('d/m/Y');

            if ($format === 'preview') {
                $export = new \App\Exports\RevenueReportExport($data, $isMonthly, $isHourly);
                return response()->json([
                    'success' => true,
                    'headers' => $export->headings(),
                    'data' => collect($data)->map(fn($item) => $export->map($item)),
                    'summary' => $summary
                ]);
            }

            if ($format === 'excel') {
                return Excel::download(new \App\Exports\RevenueReportExport($data, $isMonthly, $isHourly), 'laporan-pendapatan-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.revenue', [
                'data' => $data,
                'summary' => $summary,
                'isMonthly' => $isMonthly,
                'isHourly' => $isHourly,
                'dateRange' => $dateRange
            ]);
            $pdf->setPaper('a4', 'portrait');

            if ($stream) {
                return $pdf->stream('laporan-pendapatan-' . now()->format('Y-m-d') . '.pdf');
            }

            return $pdf->download('laporan-pendapatan-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
        }
    }

    /**
     * Export Popular Instruments
     */
    public function popularInstruments(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
            $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfDay();
            $format = $request->input('format', 'pdf');
            $stream = $request->input('stream') == 1;

            $data = \DB::table('rental_details')
                ->join('rentals', 'rental_details.rental_id', '=', 'rentals.id')
                ->join('instruments', 'rental_details.instrument_id', '=', 'instruments.id')
                ->join('categories', 'instruments.category_id', '=', 'categories.id')
                ->whereBetween('rentals.created_at', [$dateFrom, $dateTo])
                ->where('rentals.status', 'returned')
                ->select(
                    'instruments.id',
                    'instruments.name',
                    'categories.name as category_name',
                    \DB::raw('count(*) as rental_count'),
                    \DB::raw('sum(rental_details.subtotal) as total_revenue'),
                    \DB::raw('avg(rental_details.price_per_day) as avg_price')
                )
                ->groupBy('instruments.id', 'instruments.name', 'categories.name')
                ->orderBy('rental_count', 'desc')
                ->limit($limit)
                ->get();

            $dataArray = json_decode(json_encode($data), true);
            $dateRange = $dateFrom->format('d/m/Y') . ' - ' . $dateTo->format('d/m/Y');

            if ($format === 'preview') {
                $export = new \App\Exports\PopularInstrumentsExport($dataArray);
                return response()->json([
                    'success' => true,
                    'headers' => $export->headings(),
                    'data' => collect($dataArray)->map(fn($item) => $export->map($item))
                ]);
            }

            if ($format === 'excel') {
                return Excel::download(new \App\Exports\PopularInstrumentsExport($dataArray), 'laporan-instrumen-populer-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.popular_instruments', [
                'data' => $dataArray,
                'dateRange' => $dateRange
            ]);
            $pdf->setPaper('a4', 'portrait');

            if ($stream) {
                return $pdf->stream('laporan-instrumen-populer-' . now()->format('Y-m-d') . '.pdf');
            }

            return $pdf->download('laporan-instrumen-populer-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            return Response::Error('Gagal export laporan instrumen populer', $th->getMessage());
        }
    }

    /**
     * Export Sales Trend (Statistik)
     */
    public function salesTrend(Request $request)
    {
        try {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subDays(30);
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();
            $filter = $request->input('filter', '1m');
            $format = $request->input('format', 'pdf');
            $stream = $request->input('stream') == 1;
            $isHourly = $request->input('is_hourly', false);
            $timezone = $request->input('timezone', 'Asia/Jakarta');

            // Untuk filter "today" dengan mode hourly
            if ($isHourly || $filter === 'today') {
                // Set timezone untuk konsistensi
                $dateFrom = $dateFrom->setTimezone($timezone)->startOfDay();
                $dateTo = $dateTo->setTimezone($timezone)->endOfDay();

                // Batasi hanya sampai jam saat ini jika tanggalnya hari ini
                $now = Carbon::now($timezone);
                if ($dateTo->isToday() && $now->lessThan($dateTo)) {
                    $dateTo = $now;
                }

                $data = [];
                $totalVolume = 0;
                $current = $dateFrom->copy()->startOfHour();

                // Generate data per jam
                while ($current <= $dateTo) {
                    $hourStart = $current->copy();
                    $hourEnd = $current->copy()->endOfHour();

                    // Pastikan tidak melebihi dateTo
                    if ($hourEnd > $dateTo) {
                        $hourEnd = $dateTo->copy();
                    }

                    $count = Rental::whereBetween('created_at', [$hourStart, $hourEnd])
                        ->where('status', 'returned')
                        ->count();

                    $hourLabel = $current->format('H:00') . ' - ' . $current->format('H:59');

                    $data[] = [
                        'date_label' => $hourLabel,
                        'datetime' => $current->format('Y-m-d H:00:00'),
                        'count' => (int)$count,
                    ];

                    $totalVolume += $count;
                    $current->addHour();
                }

                $dateRange = $dateFrom->format('d/m/Y H:i') . ' - ' . $dateTo->format('d/m/Y H:i');
                $isMonthly = false;
                $isHourlyMode = true;
            } else {
                $isMonthly = $filter === '1t' || $filter === '1y';
                $isHourlyMode = false;

                // Set timezone untuk konsistensi
                $dateFrom = $dateFrom->setTimezone($timezone)->startOfDay();
                $dateTo = $dateTo->setTimezone($timezone)->endOfDay();

                // Aggregate Data
                $data = [];
                $totalVolume = 0;
                $current = $dateFrom->copy();

                if ($isMonthly) {
                    while ($current <= $dateTo) {
                        $monthStart = $current->copy()->startOfMonth()->setTimezone($timezone);
                        $monthEnd = $current->copy()->endOfMonth()->setTimezone($timezone);
                        if ($monthEnd > $dateTo) $monthEnd = $dateTo->copy();

                        $count = Rental::whereBetween('created_at', [$monthStart, $monthEnd])
                            ->where('status', 'returned')
                            ->count();

                        $data[] = [
                            'date_label' => $current->translatedFormat('F Y'),
                            'count' => (int)$count,
                        ];
                        $totalVolume += $count;
                        $current->addMonth()->startOfMonth();
                    }
                } else {
                    while ($current <= $dateTo) {
                        $dayStart = $current->copy()->startOfDay()->setTimezone($timezone);
                        $dayEnd = $current->copy()->endOfDay()->setTimezone($timezone);

                        $count = Rental::whereBetween('created_at', [$dayStart, $dayEnd])
                            ->where('status', 'returned')
                            ->count();

                        $data[] = [
                            'date_label' => $current->translatedFormat('d F Y'),
                            'count' => (int)$count,
                        ];
                        $totalVolume += $count;
                        $current->addDay();
                    }
                }

                $dateRange = $dateFrom->format('d/m/Y') . ' - ' . $dateTo->format('d/m/Y');
            }

            if ($format === 'preview') {
                // Kirim data preview sesuai format
                $headers = $isHourlyMode
                    ? ['No.', 'Jam', 'Jumlah Transaksi', 'Waktu (Detail)']
                    : ['No.', 'Tanggal', 'Jumlah Transaksi'];

                $previewData = collect($data)->map(function ($item, $index) use ($isHourlyMode) {
                    if ($isHourlyMode) {
                        return [
                            'No.' => $index + 1,
                            'Jam' => $item['date_label'],
                            'Jumlah Transaksi' => $item['count'],
                            'Waktu (Detail)' => $item['datetime'] ?? '-'
                        ];
                    } else {
                        return [
                            'No.' => $index + 1,
                            'Tanggal' => $item['date_label'],
                            'Jumlah Transaksi' => $item['count']
                        ];
                    }
                });

                return response()->json([
                    'success' => true,
                    'headers' => $headers,
                    'data' => $previewData,
                    'totalVolume' => $totalVolume,
                    'isHourly' => $isHourlyMode
                ]);
            }

            if ($format === 'excel') {
                // Untuk Excel, kita buat export langsung tanpa file terpisah
                return $this->exportToExcel($data, $isHourlyMode, $dateRange);
            }

            // Untuk PDF
            $pdf = Pdf::loadView('exports.sales_trend', [
                'data' => $data,
                'totalVolume' => $totalVolume,
                'isMonthly' => $isMonthly,
                'isHourly' => $isHourlyMode,
                'dateRange' => $dateRange,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]);
            $pdf->setPaper('a4', $isHourlyMode ? 'landscape' : 'portrait');

            $filename = $isHourlyMode
                ? 'laporan-statistik-penyewaan-per-jam-' . now()->format('Y-m-d')
                : 'laporan-statistik-penyewaan-' . now()->format('Y-m-d');

            if ($stream) {
                return $pdf->stream($filename . '.pdf');
            }

            return $pdf->download($filename . '.pdf');
        } catch (\Throwable $th) {
            return Response::Error('Gagal export laporan statistik', $th->getMessage());
        }
    }

    /**
     * Export to Excel without creating new file
     */
    private function exportToExcel($data, $isHourlyMode, $dateRange)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        if ($isHourlyMode) {
            $sheet->setCellValue('A1', 'No.');
            $sheet->setCellValue('B1', 'Jam');
            $sheet->setCellValue('C1', 'Jumlah Transaksi');
            $sheet->setCellValue('D1', 'Waktu (Detail)');

            // Fill data
            $row = 2;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item['date_label']);
                $sheet->setCellValue('C' . $row, $item['count']);
                $sheet->setCellValue('D' . $row, $item['datetime'] ?? '-');
                $row++;
            }
        } else {
            $sheet->setCellValue('A1', 'No.');
            $sheet->setCellValue('B1', 'Tanggal');
            $sheet->setCellValue('C1', 'Jumlah Transaksi');

            // Fill data
            $row = 2;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item['date_label']);
                $sheet->setCellValue('C' . $row, $item['count']);
                $row++;
            }
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'A47551']
            ],
        ];
        $sheet->getStyle('A1:' . ($isHourlyMode ? 'D1' : 'C1'))->applyFromArray($headerStyle);

        // Auto size columns
        foreach (range('A', $isHourlyMode ? 'D' : 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create temp file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = ($isHourlyMode ? 'laporan-statistik-penyewaan-per-jam-' : 'laporan-statistik-penyewaan-') . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public function penalties(Request $request)
    {
        try {
            $query = Penalty::query()
                ->with(['rental', 'condition.instrument', 'user'])
                ->orderBy('created_at', 'desc');

            if ($request->search) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $data = $query->get();
            $format = $request->input('format', 'pdf');
            $stream = $request->input('stream') == 1;

            if ($format === 'preview') {
                $export = new PenaltiesExport($data);
                return response()->json([
                    'success' => true,
                    'headers' => $export->headings(),
                    'data' => $data->map(fn($item) => $export->map($item))
                ]);
            }

            if ($format === 'excel') {
                return Excel::download(new PenaltiesExport($data), 'laporan-denda-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.penalties', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');

            if ($stream) {
                return $pdf->stream('laporan-denda-' . now()->format('Y-m-d') . '.pdf');
            }

            return $pdf->download('laporan-denda-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            return Response::Error('Gagal export data denda', $th->getMessage());
        }
    }

    public function rentalReceipt(string $id)
    {
        try {
            $rental = Rental::with(['customer', 'details.instrument', 'penalty'])
                ->findOrFail($id);

            $whatsappNumber = Setting::where('key', 'whatsapp_number')->first()?->value;

            $pdf = Pdf::loadView('exports.rental_receipt', [
                'rental' => $rental,
                'whatsapp_number' => $whatsappNumber
            ]);
            
            $pdf->setPaper('a4', 'portrait');

            return $pdf->stream('bukti-transaksi-' . $rental->id . '.pdf');
        } catch (\Throwable $th) {
            Log::error('PDF Export Error: ' . $th->getMessage(), [
                'exception' => $th,
                'rental_id' => $id
            ]);
            return Response::Error('Gagal generate bukti transaksi', $th->getMessage());
        }
    }
}
