<?php

namespace App\Http\Controllers;

use App\Contracts\Interfaces\DashboardInterface;
use App\Helpers\Response;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $dashboardInterface;

    public function __construct(DashboardInterface $dashboardInterface)
    {
        $this->dashboardInterface = $dashboardInterface;
    }

    /**
     * Admin Dashboard - full stats
     */
    public function admin()
    {
        try {
            $data = $this->dashboardInterface->adminStats();
            
            // Add popular instruments for admin dashboard
            $data['popular_instruments'] = $this->dashboardInterface->getPopularInstruments(4);

            return Response::Ok('Berhasil mendapatkan data dashboard', $data);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data dashboard', $th->getMessage());
        }
    }

    /**
     * Staff Dashboard - rental-focused stats
     */
    public function staff()
    {
        try {
            $data = $this->dashboardInterface->staffStats();

            return Response::Ok('Berhasil mendapatkan data dashboard staff', $data);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mendapatkan data dashboard staff', $th->getMessage());
        }
    }
}
