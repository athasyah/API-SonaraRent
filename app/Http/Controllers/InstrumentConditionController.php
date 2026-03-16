<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Contracts\Interfaces\InstrumentConditionInterface;
use App\Contracts\Interfaces\InstrumentInterface;
use App\Contracts\Interfaces\PenaltyInterface;
use App\Contracts\Interfaces\RentalDetailInterface;
use App\Enums\ActionEnum;
use App\Enums\ModuleEnum;
use App\Enums\StatusEnum;
use App\Events\InstrumentConditionCreated;
use App\Helpers\PaginationHelper;
use App\Helpers\Response;
use App\Http\Requests\InstrumentConditionRequest;
use App\Http\Resources\InstrumentConditionResource;
use App\Services\ActivityLogService;
use App\Services\InstrumentConditionService;
use App\Services\PenaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstrumentConditionController extends Controller
{
    private $conditionInterface, $conditionService,
        $instrumentInterface, $rentalDetailInterface,
        $logService, $logInterface,
        $penaltyInterface, $penaltyService;
    public function __construct(
        InstrumentConditionInterface $conditionInterface,
        InstrumentConditionService $conditionService,
        InstrumentInterface $instrumentInterface,
        RentalDetailInterface $rentalDetailInterface,
        ActivityLogService $logService,
        ActivityLogInterface $logInterface,
        PenaltyInterface $penaltyInterface,
        PenaltyService $penaltyService,
    ) {
        $this->conditionInterface = $conditionInterface;
        $this->conditionService = $conditionService;
        $this->instrumentInterface = $instrumentInterface;
        $this->rentalDetailInterface = $rentalDetailInterface;
        $this->logService = $logService;
        $this->logInterface = $logInterface;
        $this->penaltyInterface = $penaltyInterface;
        $this->penaltyService = $penaltyService;
    }

    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 8;
        $page = $request->page ?? 1;
        $payload = [];
        try {
            $data = $this->conditionInterface->customPaginate($per_page, $page, $payload);
            $resource = InstrumentConditionResource::collection($data);
            $helper = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil menampilkan data kondisi instrumen', $resource, $helper);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menampilkan data kondisi instrumen', $th->getMessage());
        }
    }

    public function store(InstrumentConditionRequest $request)
    {
        if (!$this->rentalDetailInterface->instrumentExistsInRental(
            $request->rental_id,
            $request->instrument_id
        )) {
            return Response::Error(
                'Instrumen tidak termasuk dalam rental ini',
                null
            );
        }

        $allowedStatuses = [
            StatusEnum::AVAILABLE->value,
            StatusEnum::MAINTENANCE->value,
        ];

        $instrument = $this->instrumentInterface->show($request->instrument_id);

        if (!in_array($instrument->status, $allowedStatuses)) return Response::Error('Status alat harus tersedia atau diperbaiki', null);

        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $service = $this->conditionService->mappingInstrumentCondition($validate);
            $data = $this->conditionInterface->store($service);

            if (isset($validate['damage_cost']) && $validate['damage_cost'] > 0) {
                $mapPenalty = $this->penaltyService->mappPenalty([
                    'rental_id'   => $validate['rental_id'],
                    'condition_id' => $data->id,
                    'amount'      => $validate['damage_cost'],
                    'title'       => 'Denda Pengerusakan Barang',
                    'reason'      => $validate['note'] ?? null,
                ]);
                $this->penaltyInterface->store($mapPenalty);
            }

            $log = $this->logService->logActivity(ActionEnum::CREATE->value, ModuleEnum::CONDITION->value, 'Menambah data kondisi instrumen "' . $instrument->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            event(new InstrumentConditionCreated(
                $data->load('instrument')
            ));
            return Response::Ok('Berhasil menambahkan data kondisi instrumen', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal menambahkan data kondisi instrumen', $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->conditionInterface->show($id);
            if (!$data) return Response::NotFound('Gagal mendapatkan data kondisi instrumen');

            return Response::Ok('Berhasil mendapatkan data kondisi instrumen', $data);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data kondisi instrumen', $th->getMessage());
        }
    }

    public function update(InstrumentConditionRequest $request, string $id)
    {
        if (!$this->rentalDetailInterface->instrumentExistsInRental(
            $request->rental_id,
            $request->instrument_id
        )) {
            return Response::Error(
                'Instrumen tidak termasuk dalam rental ini',
                null
            );
        }

        $allowedStatuses = [
            StatusEnum::AVAILABLE->value,
            StatusEnum::MAINTENANCE->value,
        ];

        $instrument = $this->instrumentInterface->show($request->instrument_id);

        if (!in_array($instrument->status, $allowedStatuses)) return Response::Error('Status alat harus tersedia atau diperbaiki', null);

        $data = $this->conditionInterface->show($id);
        if (!$data) return Response::NotFound('Gagal mendapatkan data kondisi instrumen');

        $validate = $request->validated();
        DB::beginTransaction();
        try {
            $service = $this->conditionService->mappingInstrumentCondition($validate);
            $update = $this->conditionInterface->update($id, $service);

            $log = $this->logService->logActivity(ActionEnum::UPDATE->value, ModuleEnum::CONDITION->value, 'Mengubah data kondisi instrumen "' . $instrument->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            event(new InstrumentConditionCreated(
                $update->load('instrument')
            ));
            return Response::Ok('Berhasil mengubah data kondisi instrumen', $update);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal mengubah data kondisi instrumen', $th->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $data = $this->conditionInterface->show($id);
        if (!$data) return Response::NotFound('Gagal menghapus data kondisi instrumen');

        DB::beginTransaction();
        try {
            $data = $this->conditionInterface->delete($id);

            $instrument = $this->instrumentInterface->show($data->instrument_id);

            $log = $this->logService->logActivity(ActionEnum::DELETE->value, ModuleEnum::CONDITION->value, 'Menghapus data kondisi instrumen "' . $instrument->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil menghapus data kondisi instrumen', $data);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal menghapus data kondisi instrumen', $th->getMessage());
        }
    }

    public function noPaginate(Request $requeset)
    {
        $payload = [];

        try {
            $data = $this->conditionInterface->noPaginate($payload);

            return Response::Ok('Berhasil mendapatkan data kategori', InstrumentConditionResource::collection($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data kategori', $th->getMessage());
        }
    }
}
