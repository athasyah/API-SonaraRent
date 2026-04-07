<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Contracts\Interfaces\InstrumentInterface;
use App\Contracts\Interfaces\PenaltyInterface;
use App\Contracts\Interfaces\RentalDetailInterface;
use App\Contracts\Interfaces\RentalInterface;
use App\Enums\ActionEnum;
use App\Enums\ModuleEnum;
use App\Enums\StatusEnum;
use App\Events\RentalStatusUpdated;
use App\Helpers\PaginationHelper;
use App\Helpers\Response;
use App\Http\Requests\RentalRequest;
use App\Http\Requests\StatusRentalRequest;
use App\Http\Resources\RentalResource;
use App\Models\rental;
use App\Services\ActivityLogService;
use App\Services\PenaltyService;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RentalController extends Controller
{
    private $rentalInterface, $rentalService, $rentDetailInterface, $logService, $logInterface, $penaltyService, $penaltyInterface, $instrumentInterface;
    public function __construct(
        RentalInterface $rentalInterface,
        RentalService $rentalService,
        RentalDetailInterface $rentalDetailInterface,
        ActivityLogService $logService,
        ActivityLogInterface $logInterface,
        PenaltyService $penaltyService,
        PenaltyInterface $penaltyInterface,
        InstrumentInterface $instrumentInterface
    ) {
        $this->rentalInterface = $rentalInterface;
        $this->rentalService = $rentalService;
        $this->rentDetailInterface = $rentalDetailInterface;
        $this->logService = $logService;
        $this->logInterface = $logInterface;
        $this->penaltyInterface = $penaltyInterface;
        $this->penaltyService = $penaltyService;
        $this->instrumentInterface = $instrumentInterface;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 8;
        $page = $request->page ?? 1;
        $payload = $request->only(['search', 'status', 'date_from', 'date_to']);
        try {
            $data = $this->rentalInterface->customPaginate($per_page, $page, $payload);
            $resource = RentalResource::collection($data);
            $helper = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil menampilkan data Rental', $resource, $helper);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menampilkan data Rental', $th->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentalRequest $request)
    {
        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $totalDays = $this->rentalService->calculateRentalDays(
                $request->rent_date,
                $request->return_date
            );

            $details = $this->rentalService->mapRentalDetails(
                $request->details,
                $totalDays
            );
            $totalPrice = collect($details)->sum('subtotal');

            foreach ($details as $detail) {

                $conflict = $this->rentDetailInterface
                    ->hasDateConflict(
                        $detail['instrument_id'],
                        $request->rent_date,
                        $request->return_date
                    );

                if ($conflict) {
                    DB::rollBack();
                    return Response::Error(
                        'Instrument sudah dibooking pada tanggal tersebut',
                        null
                    );
                }
            }

            $map = $this->rentalService->rentalStore($validate, $totalPrice);
            $rental = $this->rentalInterface->store($map);

            foreach ($details as &$detail) {
                $detail['rental_id'] = $rental->id;
                $this->rentDetailInterface->store($detail);
            }

            $customerName = $rental->customer?->name ?? 'Customer';
            $log = $this->logService->logActivity(ActionEnum::CREATE->value, ModuleEnum::RENTAL->value, 'Membuat data Rental untuk ' . $customerName . ' dengan total harga Rp ' . number_format($totalPrice, 0, ',', '.'));
            $this->logInterface->store($log);

            DB::commit();
            $rental->load(['details', 'customer']);
            return Response::Ok('Berhasil menambahkan data rental', $rental);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Terjadi kesalahan saat menambahkan data Rental', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = $this->rentalInterface->show($id);

            if (!$data) return Response::NotFound('Data rental tidak ditemukan');

            return Response::Ok('Berhasil mengambil data rental', new RentalResource($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mengambil data rental', $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rental $rental)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RentalRequest $request, string $id)
    {
        $rental = $this->rentalInterface->show($id);
        if (!$rental) {
            return Response::NotFound('Rental tidak ditemukan');
        }

        if ($rental->status !== StatusEnum::PENDING->value) {
            return Response::Error('Rental hanya bisa diubah jika status pending', null);
        }

        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $totalDays = $this->rentalService->calculateRentalDays(
                $validate['rent_date'],
                $validate['return_date']
            );

            $details = $this->rentalService->mapRentalDetails(
                $validate['details'],
                $totalDays
            );

            $totalPrice = collect($details)->sum('subtotal');

            $updatedRental = $this->rentalInterface->update($id, [
                'rent_date'   => $validate['rent_date'],
                'return_date' => $validate['return_date'],
                'total_price' => $totalPrice,
            ]);

            $this->rentDetailInterface->deleteByRentalId($id);

            foreach ($details as $detail) {
                $detail['rental_id'] = $id;
                $this->rentDetailInterface->store($detail);
            }

            $customerName = $rental->customer?->name ?? 'Customer';
            $log = $this->logService->logActivity(ActionEnum::UPDATE->value, ModuleEnum::RENTAL->value, 'Mengubah data Rental milik ' . $customerName);
            $this->logInterface->store($log);

            DB::commit();

            $updatedRental->load(['details', 'customer']);

            return Response::Ok('Berhasil mengubah data rental', $updatedRental);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal mengubah data rental', $th->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->rentalInterface->show($id);
        if (!$data) return Response::NotFound('Rental tidak ditemukan');

        if ($data->status !== 'pending') {
            return Response::Error('Rental hanya bisa dihapus jika status pending', null);
        }

        DB::beginTransaction();
        try {
            $rent = $this->rentalInterface->delete($id);

            $customerName = $data->customer?->name ?? 'Customer';
            $log = $this->logService->logActivity(ActionEnum::DELETE->value, ModuleEnum::RENTAL->value, 'Menghapus data Rental milik ' . $customerName);
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menhapus data rental', $rent);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Terjadi kesalahan saat menghapus data rental', $th->getMessage());
        }
    }

    public function noPaginate(Request $requeset)
    {
        $payload = [];

        try {
            $data = $this->rentalInterface->noPaginate($payload);

            return Response::Ok('Berhasil mendapatkan data rental', RentalResource::collection($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data rental', $th->getMessage());
        }
    }

    public function statusRental(StatusRentalRequest $request, string $id)
    {
        $rental = $this->rentalInterface->show($id);
        if (!$rental) {
            return Response::NotFound('Rental tidak ditemukan');
        }

        $validate = $request->validated();
        $newStatus = $validate['status'];
        $oldStatus = $rental->status;
        $customerName = $rental->customer?->name ?? 'Customer tidak diketahui';
        $rentalPeriod = \Carbon\Carbon::parse($rental->rent_date)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($rental->return_date)->format('d M Y');
        $allowedTransitions = [
            StatusEnum::PENDING->value => [
                StatusEnum::CANCELLED->value,
                StatusEnum::RESERVED->value,
                StatusEnum::ONGOING->value,
            ],
            StatusEnum::RESERVED->value => [
                StatusEnum::ONGOING->value,
                StatusEnum::CANCELLED->value,
            ],
            StatusEnum::ONGOING->value => [
                StatusEnum::RETURNED->value,
            ],
        ];

        if (
            !isset($allowedTransitions[$oldStatus]) ||
            !in_array($newStatus, $allowedTransitions[$oldStatus])
        ) {
            return Response::Error(
                "Status {$oldStatus} tidak bisa diubah menjadi {$newStatus}",
                null
            );
        }

        if ($newStatus === StatusEnum::ONGOING->value) {
            if ($rental->payment_status !== 'paid') {
                return Response::Error('Tidak dapat diubah ke ONGOING. Pembayaran belum lunas.', null);
            }

            // Only require if not already uploaded
            if (empty($rental->guarantee_image) && !$request->hasFile('guarantee_image')) {
                return Response::Error('Tidak dapat diubah ke ONGOING. Foto identitas/jaminan belum diunggah petugas.', null);
            }
        }

        DB::beginTransaction();
        try {
            $updateData = ['status' => $newStatus];

            // Handle Guarantee Upload if provided (for transition to ONGOING)
            if ($newStatus === StatusEnum::ONGOING->value && $request->hasFile('guarantee_image')) {
                $file = $request->file('guarantee_image');
                $path = $file->store('guarantees', 'public');

                $updateData['guarantee_image'] = $path;
                $updateData['guarantee_type'] = $request->guarantee_type ?? 'KTP';
                $updateData['guarantee_taken_at'] = \Carbon\Carbon::now();
                $updateData['guarantee_taken_by'] = auth()->id();
            }

            if ($newStatus === StatusEnum::RETURNED->value) {
                $updateData['actual_return_date'] = \Carbon\Carbon::now();
                $updateData['returned_by_user'] = auth()->id();
            }

            $updatedRental = $this->rentalInterface->update($id, $updateData);

            if ($newStatus === StatusEnum::RETURNED->value) {
                $penaltyAmount = $this->penaltyService->calculateLatePenalty($rental);

                if ($penaltyAmount > 0) {
                    $this->penaltyInterface->store([
                        'rental_id' => $rental->id,
                        'title'     => 'Denda keterlambatan',
                        'reason'    => 'Pengembalian melebihi batas waktu',
                        'amount'    => $penaltyAmount,
                    ]);
                }
            }

            $logMessage = "Mengubah status rental ({$rentalPeriod}) milik {$customerName} dari {$oldStatus} menjadi {$newStatus}";
            $log = $this->logService->logActivity(
                ActionEnum::UPDATE->value,
                ModuleEnum::RENTAL->value,
                $logMessage
            );
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil mengubah status rental', $updatedRental);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error(
                'Terjadi kesalahan saat mengubah status rental',
                $th->getMessage()
            );
        }
    }

    public function markAsPaid(string $id)
    {
        $rental = $this->rentalInterface->show($id);
        if (!$rental) {
            return Response::NotFound('Rental tidak ditemukan');
        }

        DB::beginTransaction();
        try {
            $updatedRental = $this->rentalInterface->update($id, [
                'payment_status' => 'paid',
                'status' => 'reserved' // or StatusEnum::RESERVED->value
            ]);

            $log = $this->logService->logActivity(
                ActionEnum::UPDATE->value,
                ModuleEnum::RENTAL->value,
                "Menandai pembayaran lunas untuk rental ID: " . $id
            );
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menandai pembayaran lunas', $updatedRental);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Terjadi kesalahan saat menandai pembayaran', $th->getMessage());
        }
    }

    public function uploadGuarantee(Request $request, string $id)
    {
        $request->validate([
            'guarantee_type' => 'required|string',
            'guarantee_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'
        ], [
            'guarantee_type.required' => 'Tipe jaminan harus diisi',
            'guarantee_image.required' => 'Foto jaminan harus diunggah',
            'guarantee_image.image' => 'File harus berupa gambar',
            'guarantee_image.mimes' => 'Format gambar harus jpeg, png, jpg, webp',
            'guarantee_image.max' => 'Ukuran gambar maksimal 5MB'
        ]);

        $rental = $this->rentalInterface->show($id);
        if (!$rental) {
            return Response::NotFound('Rental tidak ditemukan');
        }

        DB::beginTransaction();
        try {
            $imagePath = $request->file('guarantee_image')->store('guarantees', 'public');

            $updatedRental = $this->rentalInterface->update($id, [
                'guarantee_type' => $request->guarantee_type,
                'guarantee_image' => $imagePath,
                'guarantee_taken_at' => now(),
                'guarantee_taken_by' => auth()->id()
            ]);

            $log = $this->logService->logActivity(
                ActionEnum::UPDATE->value,
                ModuleEnum::RENTAL->value,
                "Mengunggah identitas jaminan untuk pelanggan: " . ($rental->customer?->name ?? 'Unknown')
            );
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil mengunggah jaminan', $updatedRental);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Terjadi kesalahan saat mengunggah jaminan', $th->getMessage());
        }
    }

    public function getByUser(Request $request)
    {
        $payload = [];
        try {
            $data = $this->rentalInterface->getByUser(auth()->user()->id);
            $resource = RentalResource::collection($data);
            $paginate = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil mendapatkan data rental', $resource, $paginate);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data rental', $th->getMessage());
        }
    }

    public function simulatePayment(Request $request, string $id)
    {
        try {
            $rental = $this->rentalInterface->show($id);
            if (!$rental) {
                return Response::NotFound('Data rental tidak ditemukan');
            }

            // Ensure the user owns this rental
            if ($rental->customer_id !== auth()->id()) {
                return Response::Error('Unauthorized: Anda tidak dapat mengakses pesanan ini', null);
            }

            // Update status only if not already paid
            if ($rental->payment_status !== 'paid') {
                $this->rentalInterface->update($id, [
                    'payment_status' => 'paid',
                    'status' => StatusEnum::RESERVED->value
                ]);

                $log = $this->logService->logActivity(
                    ActionEnum::UPDATE->value,
                    ModuleEnum::RENTAL->value,
                    "Customer berhasil mensimulasikan pembayaran untuk pesanan #{$rental->id}"
                );
                $this->logInterface->store($log);
            }

            return Response::Ok('Pembayaran berhasil disimulasikan', new RentalResource($rental->refresh()));
        } catch (\Throwable $th) {
            return Response::Error('Terjadi kesalahan saat mensimulasikan pembayaran', $th->getMessage());
        }
    }
}
