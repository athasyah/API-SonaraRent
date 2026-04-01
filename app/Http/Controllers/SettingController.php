<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\SettingInterface;
use App\Helpers\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    private SettingInterface $setting;

    public function __construct(SettingInterface $setting)
    {
        $this->setting = $setting;
    }

    public function index()
    {
        $settings = $this->setting->get();
        return Response::Ok("Berhasil mengambil pengaturan", $settings);
    }

    public function getByKey($key)
    {
        $setting = $this->setting->getByKey($key);
        if (!$setting) {
            return Response::Error("Pengaturan tidak ditemukan", null);
        }
        return Response::Ok("Berhasil mengambil pengaturan $key", $setting);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::Custom(false, "Validasi gagal", $validator->errors(), 422);
        }

        $this->setting->updateBatch($request->settings);

        return Response::Ok("Pengaturan berhasil diperbarui", null);
    }
}
