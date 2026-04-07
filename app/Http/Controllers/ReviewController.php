<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Contracts\Interfaces\RentalDetailInterface;
use App\Contracts\Interfaces\RentalInterface;
use App\Contracts\Interfaces\ReviewInterface;
use App\Enums\ActionEnum;
use App\Enums\ModuleEnum;
use App\Helpers\PaginationHelper;
use App\Helpers\Response;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Rental;
use App\Models\review;
use App\Services\ActivityLogService;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    private $reviewInterface, $reviewService, $rentalDetailInterface, $rentalInterface, $logService, $logInterface;
    public function __construct(ReviewInterface $reviewInterface, ReviewService $reviewService, RentalDetailInterface $rentalDetailInterface, RentalInterface $rentalInterface, ActivityLogService $logService, ActivityLogInterface $logInterface)
    {
        $this->reviewInterface = $reviewInterface;
        $this->reviewService = $reviewService;
        $this->rentalDetailInterface = $rentalDetailInterface;
        $this->rentalInterface = $rentalInterface;
        $this->logService = $logService;
        $this->logInterface = $logInterface;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 8;
        $page = $request->page ?? 1;
        $payload = $request->only(['search', 'rating', 'instrument_id']);
        try {
            $data = $this->reviewInterface->customPaginate($per_page, $page, $payload);
            $resource = ReviewResource::collection($data);
            $helper = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil menampilkan data review', $resource, $helper);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menampilkan data review', $th->getMessage());
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
    public function store(ReviewRequest $request)
    {
        if ($this->reviewInterface->existsByRentalAndInstrument($request->rental_id, $request->instrument_id)) {
            return Response::Error('Review untuk instrumen ini sudah pernah dibuat', null);
        }

        $rental = $this->rentalInterface->show($request->rental_id);

        if (!$rental || $rental->customer_id !== auth()->id()) {
            return Response::Error('Anda tidak berhak memberi review pada rental ini', null);
        }

        if (!$this->rentalDetailInterface->instrumentExistsInRental($request->rental_id, $request->instrument_id)) {
            return Response::Error('Instrumen tidak termasuk dalam rental ini', null);
        }

        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $mapping = $this->reviewService->mappingReview($validate);
            $data = $this->reviewInterface->store($mapping);

            $data->load(['customer', 'instrument']);
            $log = $this->logService->logActivity(ActionEnum::CREATE->value, ModuleEnum::REVIEW->value, 'Memberi review untuk instrumen "' . $data->instrument->name . '" oleh ' . $data->customer->name);
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menambahkan data review', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal menambahkan data review', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = $this->reviewInterface->show($id);

            if (!$data) return Response::NotFound('Data review tidak ditemukan');

            return Response::Ok('Berhasil mendapatkan data review', $data);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data review', $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewRequest $request, string $id)
    {
        if (!$this->rentalDetailInterface->instrumentExistsInRental($request->rental_id, $request->instrument_id)) {
            return Response::Error('Instrumen tidak termasuk dalam rental ini', null);
        }

        $data = $this->reviewInterface->show($id);

        if (!$data) return Response::NotFound('Data review tidak ditemukan');

        $validate = $request->validated();
        DB::beginTransaction();
        try {
            $mapping = $this->reviewService->mappingReview($validate);
            
            if ($request->hasFile('image') && $data->image) {
                $this->reviewService->remove($data->image);
            }

            $update = $this->reviewInterface->update($id, $mapping);

            $data->load(['customer', 'instrument']);
            $log = $this->logService->logActivity(ActionEnum::UPDATE->value, ModuleEnum::REVIEW->value, 'Mengubah review instrumen "' . $data->instrument->name . '" oleh ' . $data->customer->name);
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil mengubah data review', $update);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal mengubah data review', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->reviewInterface->show($id);

        if (!$data) return Response::NotFound('Data review tidak ditemukan');
        try {
            if ($data->image) {
                $this->reviewService->remove($data->image);
            }
            $delete = $this->reviewInterface->delete($id);

            $data->load(['customer', 'instrument']);
            $log = $this->logService->logActivity(ActionEnum::DELETE->value, ModuleEnum::REVIEW->value, 'Menghapus review instrumen "' . $data->instrument->name . '" oleh ' . $data->customer->name);
            $this->logInterface->store($log);

            return Response::Ok('Berhasil menghapus data review', $delete);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menghapus data review', $th->getMessage());
        }
    }

    public function noPaginate(Request $request)
    {
        $payload = $request->only(['search', 'instrument_id']);

        try {
            $data = $this->reviewInterface->noPaginate($payload);

            return Response::Ok('Berhasil mendapatkan data review', ReviewResource::collection($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data review', $th->getMessage());
        }
    }
}
