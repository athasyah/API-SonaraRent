<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\Instrument;
use App\Models\Rental;
use App\Models\Review;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\RentalsExport;
use App\Exports\InstrumentsExport;
use App\Exports\UsersExport;
use App\Exports\ReviewsExport;
use Maatwebsite\Excel\Facades\Excel;

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

            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->date_from) {
                $query->where('rent_date', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->where('return_date', '<=', $request->date_to);
            }

            $data = $query->get();
            $format = $request->input('format', 'pdf');

            if ($format === 'excel') {
                return Excel::download(new RentalsExport($data), 'laporan-rental-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.rentals', ['data' => $data]);
            $pdf->setPaper('a4', 'landscape');
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

            if ($format === 'excel') {
                return Excel::download(new InstrumentsExport($data), 'laporan-instrumen-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.instruments', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');
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

            if ($format === 'excel') {
                // Pastikan exportExcel tidak melempar exception
                return Excel::download(new UsersExport($data), 'laporan-users-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.users', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download('laporan-users-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            // Log error untuk debugging
            \Log::error('Export users failed: ' . $th->getMessage());

            // Kembalikan response error dengan status code 500 dan header JSON
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

            if ($format === 'excel') {
                return Excel::download(new ReviewsExport($data), 'laporan-reviews-' . now()->format('Y-m-d') . '.xlsx');
            }

            $pdf = Pdf::loadView('exports.reviews', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download('laporan-reviews-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Throwable $th) {
            return Response::Error('Gagal export data review', $th->getMessage());
        }
    }

}
