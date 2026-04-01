<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Contracts\Interfaces\CategoryInterface;
use App\Enums\ActionEnum;
use App\Enums\ModuleEnum;
use App\Helpers\PaginationHelper;
use App\Helpers\Response;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    private $categoryInterface, $logService, $logInterface;
    public function __construct(CategoryInterface $categoryInterface, ActivityLogService $logService, ActivityLogInterface $logInterface)
    {
        $this->categoryInterface = $categoryInterface;
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
        $payload = $request->only(['category', 'search', 'type', 'date_from', 'date_to']);
        try {
            $data = $this->categoryInterface->customPaginate($per_page, $page, $payload);
            $resource = CategoryResource::collection($data);
            $helper = PaginationHelper::meta($data);

            return Response::Paginate('Berhasil menampilkan data kategori', $resource, $helper);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menampilkan data kategori', $th->getMessage());
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
    public function store(CategoryRequest $request)
    {

        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $category = $this->categoryInterface->store($validate);

            $log = $this->logService->logActivity(ActionEnum::CREATE->value, ModuleEnum::CATEGORY->value, 'Membuat data kategori "' . $category->name . '"');
            $this->logInterface->store($log);
            DB::commit();
            return Response::Ok('Berhasil menambahkan data kategori', $category);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Terjadi kesalahan saat menambahkan data kategori', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = $this->categoryInterface->show($id);

            if (!$data) return Response::NotFound('Kategori tidak ditemukan');

            return Response::Ok('Berhasil mengambil data kategori', $data);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mengambil data kategori', $th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CategoryRequest $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, string $id)
    {
        $data = $this->categoryInterface->show($id);
        if (!$data) return Response::NotFound('Kategori tidak ditemukan');

        $validate = $request->validated();

        DB::beginTransaction();
        try {
            $update = $this->categoryInterface->update($id, $validate);

            $newData = $this->categoryInterface->show($id);
            $log = $this->logService->logActivity(ActionEnum::UPDATE->value, ModuleEnum::CATEGORY->value, 'Mengubah data kategori "' . $data->name . '" menjadi "' . $newData->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('Berhasil mengubah data kategori', $newData);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('Gagal mengubah data kategori', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->categoryInterface->show($id);
        if (!$data) return Response::NotFound('Kategori tidak ditemukan');

        DB::beginTransaction();
        try {
            $delete = $this->categoryInterface->delete($id);

            $log = $this->logService->logActivity(ActionEnum::DELETE->value, ModuleEnum::CATEGORY->value, 'Menghapus data kategori "' . $data->name . '"');
            $this->logInterface->store($log);

            DB::commit();
            return Response::Ok('berhasil menghapus data kategori', $delete);
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::Error('gagal menghapus data kategori', $th->getMessage());
        }
    }

    public function noPaginate(Request $request)
    {
        $payload = $request->only(['category']);

        try {
            $data = $this->categoryInterface->noPaginate($payload);

            return Response::Ok('Berhasil mendapatkan data kategori', CategoryResource::collection($data));
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data kategori', $th->getMessage());
        }
    }
}
