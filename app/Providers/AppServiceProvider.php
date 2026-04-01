<?php

namespace App\Providers;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Contracts\Interfaces\CategoryInterface;
use App\Contracts\Interfaces\GuaranteeInterface;
use App\Contracts\Interfaces\InstrumentConditionInterface;
use App\Contracts\Interfaces\InstrumentInterface;
use App\Contracts\Interfaces\PenaltyInterface;
use App\Contracts\Interfaces\RentalDetailInterface;
use App\Contracts\Interfaces\RentalInterface;
use App\Contracts\Interfaces\ReviewInterface;
use App\Contracts\Interfaces\UserInterface;
use App\Contracts\Interfaces\DashboardInterface;
use App\Contracts\Interfaces\SettingInterface;
use App\Contracts\Repositories\ActivityLogRepository;
use App\Contracts\Repositories\CategoryRepository;
use App\Contracts\Repositories\GuaranteeRepository;
use App\Contracts\Repositories\InstrumentRepository;
use App\Contracts\Repositories\RentalDetailRepository;
use App\Contracts\Repositories\RentalRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\InstrumentConditionRepository;
use App\Contracts\Repositories\PenaltyRepository;
use App\Contracts\Repositories\ReviewRepository;
use App\Contracts\Repositories\DashboardRepository;
use App\Contracts\Repositories\SettingRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    private array $register = [
        UserInterface::class => UserRepository::class,
        CategoryInterface::class => CategoryRepository::class,
        InstrumentInterface::class => InstrumentRepository::class,
        RentalInterface::class => RentalRepository::class,
        RentalDetailInterface::class => RentalDetailRepository::class,
        InstrumentConditionInterface::class => InstrumentConditionRepository::class,
        ReviewInterface::class => ReviewRepository::class,
        GuaranteeInterface::class => GuaranteeRepository::class,
        ActivityLogInterface::class => ActivityLogRepository::class,
        PenaltyInterface::class => PenaltyRepository::class,
        DashboardInterface::class => DashboardRepository::class,
        SettingInterface::class => SettingRepository::class
    ];
    public function register(): void
    {
        foreach ($this->register as $index => $value) $this->app->bind($index, $value);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
