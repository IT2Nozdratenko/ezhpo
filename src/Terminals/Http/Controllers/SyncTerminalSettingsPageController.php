<?php
declare(strict_types=1);

namespace Src\Terminals\Http\Controllers;

use Illuminate\Http\Request;

final class SyncTerminalSettingsPageController
{
    public function __invoke(Request $request)
    {
        return view('terminals::sync-settings', [
            'settings' => [
                'main' => [
                    'password' => '0000',
                    'medic_password' => '0000',
                ],
                'system' => [
                    "driver_info" => false,
                    "driver_photo" => false,
                    "type_ride" => true,
                    "question_sleep" => true,
                    "question_helth" => true,
                    "alcometer_fast" => false,
                    "alcometer_skip" => true,
                    "alcometer_retry" => true,
                    "alcometer_visible" => true,
                    "tonometer_skip" => true,
                    "tonometer_visible" => true,
                    "camera_video" => true,
                    "check_phone_number" => true,
                    "camera_photo" => true,
                    "printer_write" => true,
                    "print_qr_check" => false,
                    "print_count" => 1,
                    "thermometer_skip" => true,
                    "thermometer_visible" => true,
                    "manual_mode" => false,
                    "auto_start" => true,
                    "delay_before_retry_inspection" => 5000,
                    "delay_before_redirect_to_main_page" => 10000,
                    "delay_day_in_offline_mod" => 7,
                    "max_inspection_in_offline_mod" => 10,
                ]
            ]
        ]);
    }
}
