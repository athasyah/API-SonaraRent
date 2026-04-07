<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Contracts\Interfaces\PenaltyInterface;
use App\Enums\ActionEnum;
use App\Enums\ModuleEnum;
use App\Helpers\PaginationHelper;
use App\Helpers\Response;
use App\Http\Requests\PenaltyRequest;
use App\Http\Resources\PenaltyResource;
use App\Models\penalty;
use App\Services\ActivityLogService;
use App\Services\PenaltyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PenaltyController extends Controller
{
    private $penaltyInterface, $penaltyService, $logService, $logInterface;
    public function __construct(PenaltyInterface $penaltyInterface, PenaltyService $penaltyService, ActivityLogInterface $logInterface, ActivityLogService $logService)
    {
        $this->penaltyInterface = $penaltyInterface;
        $this->penaltyService = $penaltyService;
        $this->logInterface = $logInterface;
        $this->logService = $logService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 8;
        $page = $request->page ?? 1;
        $payload = $request->all();

        try {
            $data = $this->penaltyInterface->customPaginate($per_page, $page, $payload);
            $resource = PenaltyResource::collection($data);
            $helper = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil menampilkan data denda', $resource, $helper);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menampilkan data denda', $th->getMessage());
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
    public function store(PenaltyRequest $request)
    {
        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $map = $this->penaltyService->mappPenalty($validate);
            $store = $this->penaltyInterface->store($map);

            $log = $this->logService->logActivity(ActionEnum::CREATE->value, ModuleEnum::PENALTY->value, 'Membuat data denda "' . $store->title . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menambahkan data denda', new PenaltyResource($store));
        } catch (\Throwable $th) {

            DB::rollBack();
            return Response::Error('Gagal menambahkan data denda', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            $data = $this->penaltyInterface->show($id);
            if (!$data) return Response::NotFound('data denda tidak ditemukan');
        } catch (\Throwable $th) {

            return Response::Error('Gagal mendapatkan data denda', $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(penalty $penalty)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PenaltyRequest $request, string $id)
    {
        $data = $this->penaltyInterface->show($id);
        if (!$data) return Response::NotFound('data denda tidak ditemukan');

        $validate = $request->validated();

        DB::beginTransaction();
        try {

            $map = $this->penaltyService->mappPenalty($validate);
            $update = $this->penaltyInterface->update($id, $map);

            $log = $this->logService->logActivity(ActionEnum::UPDATE->value, ModuleEnum::PENALTY->value, 'Mengubah data di "' . $update->title . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil mengubah data denda', new PenaltyResource($update));
        } catch (\Throwable $th) {

            DB::rollBack();
            return Response::Error('Gagal mengubah data denda', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->penaltyInterface->show($id);
        if (!$data) return Response::NotFound('data denda tidak ditemukan');

        DB::beginTransaction();
        try {
            $delete = $this->penaltyInterface->delete($id);

            $log = $this->logService->logActivity(ActionEnum::DELETE->value, ModuleEnum::PENALTY->value, 'Menghapus data "' . $delete->title . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menghapus data denda', $delete);
        } catch (\Throwable $th) {

            DB::rollBack();
            return Response::Error('Gagal menghapus data denda', $th->getMessage());
        }
    }

    public function noPaginate(Request $requeset)
    {
        $payload = [];

        try {
            $data = $this->penaltyInterface->noPaginate($payload);

            return Response::Ok('Berhasil mendapatkan data Denda', PenaltyResource::collection($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data Denda', $th->getMessage());
        }
    }
}
