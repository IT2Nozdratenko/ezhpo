<?php
declare(strict_types=1);

namespace Src\Terminals\Factories;

use Src\Terminals\ValueObjects\SettingsContainer;

final class SettingsFactory
{
    /** @var array  */
    private const DEFAULT_SETTINGS = [
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
            "auto_send_to_crm" => true,
            "delay_day_in_offline_mod" => 7,
            "max_inspection_in_offline_mod" => 10,
        ]
    ];

    public static function makeMain(?array $settings = null): SettingsContainer
    {
        return new SettingsContainer(array_merge(self::DEFAULT_SETTINGS['main'], $settings ?? []));
    }

    public static function makeSystem(?array $settings = null): SettingsContainer
    {
        return new SettingsContainer(array_merge(self::DEFAULT_SETTINGS['system'], $settings ?? []));
    }
}
