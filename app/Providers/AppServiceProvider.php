<?php

namespace App\Providers;

use App\Actions\User\CreateUser\CreateUserCommand;
use App\Actions\User\CreateUser\CreateUserHandler;
use App\Actions\User\DeleteUser\DeleteUserCommand;
use App\Actions\User\DeleteUser\DeleteUserHandler;
use App\Actions\User\RestoreUser\RestoreUserCommand;
use App\Actions\User\RestoreUser\RestoreUserHandler;
use App\Actions\User\UpdateUser\UpdateUserCommand;
use App\Actions\User\UpdateUser\UpdateUserHandler;
use App\Anketa;
use App\Car;
use App\Company;
use App\Contractcs\GetServicesReportForCompanyByPeriodInterface;
use App\Driver;
use App\Models\Contract;
use App\Observers\AnketaObserver;
use App\Observers\CarObserver;
use App\Observers\CompanyObserver;
use App\Observers\ContractObserver;
use App\Observers\DriverObserver;
use App\Observers\UserObserver;
use App\Services\CompanyReqsChecker\CompanyReqsCheckerInterface;
use App\Services\CompanyReqsChecker\DaDataCompanyReqsChecker;
use App\Services\ElementsSearch\ElementSearchService;
use App\Services\ElementsSearch\ElementsSearchServiceInterface;
use App\Services\HashIdGenerator\HashIdGeneratorService;
use App\Services\OneC\CompanySync\CompanySyncService;
use App\Services\OneC\CompanySync\CompanySyncServiceInterface;
use App\Services\OneC\Reports\GetServicesReportForCompanyByPeriod;
use App\Services\QRCode\QRCodeGenerator;
use App\Services\QRCode\QRCodeGeneratorInterface;
use App\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        CompanySyncServiceInterface::class => CompanySyncService::class,
        CompanyReqsCheckerInterface::class => DaDataCompanyReqsChecker::class,
        ElementsSearchServiceInterface::class => ElementSearchService::class,
        QRCodeGeneratorInterface::class => QRCodeGenerator::class,
        GetServicesReportForCompanyByPeriodInterface::class => GetServicesReportForCompanyByPeriod::class
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('hash-id-generator', function () {
            return new HashIdGeneratorService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $this->registerModelObservers();
        $this->registerCommandHandlers();
    }

    private function registerModelObservers()
    {
        Car::observe(CarObserver::class);
        Company::observe(CompanyObserver::class);
        Contract::observe(ContractObserver::class);
        Driver::observe(DriverObserver::class);
        User::observe(UserObserver::class);
        Anketa::observe(AnketaObserver::class);
    }

    private function registerCommandHandlers()
    {
        $this->app->extend(Dispatcher::class, function (Dispatcher $dispatcher) {
            $dispatcher->map([
                CreateUserCommand::class => CreateUserHandler::class,
                UpdateUserCommand::class => UpdateUserHandler::class,
                DeleteUserCommand::class => DeleteUserHandler::class,
                RestoreUserCommand::class => RestoreUserHandler::class,
            ]);

            return $dispatcher;
        });
    }
}
