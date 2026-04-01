<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Contracts\Interfaces\InstrumentInterface;
use App\Enums\ActionEnum;
use App\Enums\ModuleEnum;
use App\Helpers\PaginationHelper;
use App\Helpers\Response;
use App\Http\Requests\InstrumentRequest;
use App\Http\Resources\InstrumentResource;
use App\Models\Instrument;
use App\Services\ActivityLogService;
use App\Services\InstrumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstrumentController extends Controller
{
    private $instrumentInterface, $instrumentService, $logService, $logInterface;
    public function __construct(InstrumentInterface $instrumentInterface, InstrumentService $instrumentService, ActivityLogService $logService, ActivityLogInterface $logInterface)
    {
        $this->instrumentInterface = $instrumentInterface;
        $this->instrumentService = $instrumentService;
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
        $payload = $request->only(['category', 'min_price', 'max_price', 'status', 'brand', 'search', 'rent_date', 'return_date', 'date_from', 'date_to']);
        try {
            $data = $this->instrumentInterface->customPaginate($per_page, $page, $payload);
            $resource = InstrumentResource::collection($data);
            $helper = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil menampilkan data instrumen', $resource, $helper);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menampilkan data instrumen', $th->getMessage());
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
    public function store(InstrumentRequest $request)
    {
        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $service = $this->instrumentService->mappingInstrument($validate);
            $data = $this->instrumentInterface->store($service);

            $log = $this->logService->logActivity(ActionEnum::CREATE->value, ModuleEnum::INSTRUMENT->value, 'Membuat data instrumen "' . $data->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menambahkan data instrumen', new InstrumentResource($data));
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal menambahkan data instrumen', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        try {
            $data = $this->instrumentInterface->show($id);

            if (!$data) return Response::NotFound('Instrumen tidak ditemukan');

            return Response::Ok('Berhasil mengambil data instrumen', new InstrumentResource($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mengambil data instrumen', $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instrument $instrument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InstrumentRequest $request, string $id)
    {
        $data = $this->instrumentInterface->show($id);

        if (!$data) return Response::NotFound('Instrumen tidak ditemukan');

        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $service = $this->instrumentService->mappingInstrument($validate);

            $instrumen = $this->instrumentInterface->update($id, $service);
            $newData = $this->instrumentInterface->show($id);

            $log = $this->logService->logActivity(ActionEnum::UPDATE->value, ModuleEnum::INSTRUMENT->value, 'Mengubah data instrumen "' . $data->name . '" menjadi "' . $newData->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil mengubah data instrumen', new InstrumentResource($instrumen));
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal mengubah data instrumen', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->instrumentInterface->show($id);

        if (!$data) return Response::NotFound('Instrumen tidak ditemukan');

        DB::beginTransaction();
        try {
            $instrumen = $this->instrumentInterface->delete($id);

            $log = $this->logService->logActivity(ActionEnum::DELETE->value, ModuleEnum::INSTRUMENT->value, 'Menghapus data instrumen "' . $data->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menghapus data instrumen', $instrumen);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal menghapus data instrumen', $th->getMessage());
        }
    }

    public function noPaginate(Request $request)
    {
        $payload = $request->only(['category', 'min_price', 'max_price', 'status', 'brand']);

        try {
            $data = $this->instrumentInterface->noPaginate($payload);

            return Response::Ok('Berhasil mendapatkan data instrumen', InstrumentResource::collection($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data instrumen', $th->getMessage());
        }
    }
}
