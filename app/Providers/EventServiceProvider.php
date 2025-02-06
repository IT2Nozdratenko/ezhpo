<?php

namespace App\Providers;

use App\Events\Forms\DriverDismissed;
use App\Events\Forms\FormAction;
use App\Events\Forms\FormDetachedFromTripTicket;
use App\Events\Relations\Attached;
use App\Events\Relations\Detached;
use App\Events\TripTickets\TripTicketAction;
use App\Events\UserActions\ClientActionLogRequest;
use App\Events\UserActions\ClientAddRecord;
use App\Events\UserActions\ClientDocExport;
use App\Events\UserActions\ClientDocImport;
use App\Events\UserActions\ClientDocumentRequest;
use App\Events\UserActions\ClientReportRequest;
use App\Listeners\Forms\LogFormDetachFromTripTicket;
use App\Listeners\Forms\NotifyDismissingTG;
use App\Listeners\Forms\LogFormActions;
use App\Listeners\Forms\NotifyDismissingSMS;
use App\Listeners\LogAttachedEvent;
use App\Listeners\LogDetachedEvent;
use App\Listeners\TripTickets\LogTripTicketAction;
use App\Listeners\UserActions\LogClientLoginEvent;
use App\Listeners\UserActions\LogUserActionEvent;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Attached::class => [
            LogAttachedEvent::class
        ],
        Detached::class => [
            LogDetachedEvent::class
        ],
        Login::class => [
            LogClientLoginEvent::class
        ],
        ClientDocExport::class => [
            LogUserActionEvent::class
        ],
        ClientDocImport::class => [
            LogUserActionEvent::class
        ],
        ClientActionLogRequest::class => [
            LogUserActionEvent::class
        ],
        ClientReportRequest::class => [
            LogUserActionEvent::class
        ],
        ClientDocumentRequest::class => [
            LogUserActionEvent::class
        ],
        ClientAddRecord::class => [
            LogUserActionEvent::class
        ],
        DriverDismissed::class => [
            NotifyDismissingSMS::class,
            NotifyDismissingTG::class
        ],
        FormAction::class => [
            LogFormActions::class,
        ],
        FormDetachedFromTripTicket::class => [
            LogFormDetachFromTripTicket::class
        ],
        TripTicketAction::class => [
            LogTripTicketAction::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
