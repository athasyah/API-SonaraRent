<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Helpers\PaginationHelper;
use App\Helpers\Response;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    private $logInterface;
    public function __construct(ActivityLogInterface $logInterface)
    {
        $this->logInterface = $logInterface;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 8;
        $page = $request->page ?? 1;
        $payload = $request->only(['search', 'action', 'module', 'role', 'date_from', 'date_to']);
        try {
            $data = $this->logInterface->customPaginate($per_page, $page, $payload);
            $resource = ActivityLogResource::collection($data);
            $helper = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil menampilkan data log aktifitas', $resource, $helper);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menampilkan data log aktifitas', $th->getMessage());
        }
    }

    public function noPaginate(Request $requeset)
    {
        $payload = [];

        try {
            $data = $this->logInterface->noPaginate($payload);

            return Response::Ok('Berhasil mendapatkan data kategori', ActivityLogResource::collection($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data kategori', $th->getMessage());
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ActivityLog $activityLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ActivityLog $activityLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ActivityLog $activityLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityLog $activityLog)
    {
        //
    }
}
