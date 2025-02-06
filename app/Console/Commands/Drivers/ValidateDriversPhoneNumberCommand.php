<?php

namespace App\Console\Commands\Drivers;

use App\Driver;
use App\ValueObjects\Phone;
use Illuminate\Console\Command;

class ValidateDriversPhoneNumberCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:validate-phones
                            {--update : Обновить валидные номера}
                            {--show : Показать валидные номера}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Валидация и обновление номеров водителей';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $updateAfterValidate = $this->option('update');
        $showValid = $this->option('show');

        $drivers = Driver::query()
            ->select([
                'id',
                'hash_id',
                'phone'
            ])
            ->whereNotNull('phone')
            ->get();

        $allCount = 0;
        $updatedCount = 0;
        $invalidCount = 0;

        /** @var Driver $driver */
        foreach ($drivers as $driver) {
            $nativePhone = $driver->getAttribute('phone');
            if (strlen(trim($nativePhone)) === 0) {
                $driver->setAttribute('phone', null);
                $driver->save();

                continue;
            }

            $allCount++;

            $phone = new Phone($nativePhone);

            if (!$phone->isValid()) {
                $invalidCount++;

                $message = sprintf(
                    "%s: %s",
                    $driver->getAttribute('hash_id'),
                    $nativePhone
                );

                $this->error($message);
            }

            if ($showValid && $phone->isValid()) {
                $message = sprintf(
                    "%s: %s => %s",
                    $driver->getAttribute('hash_id'),
                    $nativePhone,
                    $phone
                );

                $this->info($message);
            }

            if ($updateAfterValidate && $phone->isValid() && !$phone->isSanitized()) {
                $driver->setAttribute('phone', $phone);
                $driver->save();

                $updatedCount++;
            }
        }

        $this->info("Водителей с номерами мобильных телефонов = " . $allCount);
        $this->info("Водителей с обновленными номерами = " . $updatedCount);
        $this->warn("Водителей с невалидными номерами = " . $invalidCount);
    }
}
