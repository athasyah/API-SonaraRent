<?php
// app/Contracts/Interfaces/DashboardInterface.php

namespace App\Contracts\Interfaces;

interface DashboardInterface
{
    /**
     * Get statistics and charts for Admin Dashboard
     * @return array
     */
    public function adminStats(): array;

    /**
     * Get statistics and charts for Staff Dashboard
     * @return array
     */
    public function staffStats(): array;

    /**
     * Get top popular instruments based on rental counts
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularInstruments(int $limit = 4): \Illuminate\Support\Collection;
}
