<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings', [
            'logo' => Settings::setting('logo') ?? null,
            'not_identify_text' => Settings::setting('not_identify_text') ?? null,
            'sms_api_key' => Settings::setting('sms_api_key') ?? null,
            'sms_text_phone' => Settings::setting('sms_text_phone') ?? null,
            'sms_text_driver' => Settings::setting('sms_text_driver') ?? null,
            'sms_text_car' => Settings::setting('sms_text_car') ?? null,
            'sms_text_default' => Settings::setting('sms_text_default') ?? null,
            'phone' => Settings::setting('phone') ?? null,
            'telegram' => Settings::setting('telegram') ?? null,
            'pressure_systolic' => Settings::setting('pressure_systolic') ?? null,
            'pressure_diastolic' => Settings::setting('pressure_diastolic') ?? null,
            'pulse_lower' => Settings::setting('pulse_lower') ?? null,
            'pulse_upper' => Settings::setting('pulse_upper') ?? null,
            'time_of_pressure_ban' => Settings::setting('time_of_pressure_ban') ?? 20,
            'time_of_alcohol_ban' => Settings::setting('time_of_alcohol_ban') ?? 120,
            'timeout' => Settings::setting('timeout') ?? null,
            'sdpo_support_phone' => Settings::setting('sdpo_support_phone') ?? null
        ]);
    }

    public function update(Request $request)
    {
        if ($request->hasFile('logo')) {
            $logo = Settings::where('key', 'logo')->first();
            Storage::disk('public')->delete($logo->value);
            $path = Storage::disk('public')->putFile('static', $request->file('logo'));
            $logo->value = $path;
            $logo->save();
        }

        Settings::set('sms_api_key', $request->sms_api_key);
        Settings::set('not_identify_text', $request->not_identify_text);
        Settings::set('sms_text_phone', $request->sms_text_phone);
        Settings::set('sms_text_driver', $request->sms_text_driver);
        Settings::set('sms_text_car', $request->sms_text_car);
        Settings::set('sms_text_default', $request->sms_text_default);
        Settings::set('phone', $request->phone);
        Settings::set('telegram', $request->telegram);
        Settings::set('pressure_systolic', $request->pressure_systolic);
        Settings::set('pressure_diastolic', $request->pressure_diastolic);
        Settings::set('pulse_lower', $request->pulse_lower);
        Settings::set('pulse_upper', $request->pulse_upper);
        Settings::set('time_of_pressure_ban', $request->time_of_pressure_ban);
        Settings::set('time_of_alcohol_ban', $request->time_of_alcohol_ban);
        Settings::set('timeout', $request->timeout);
        Settings::set('sdpo_support_phone', $request->sdpo_support_phone);

        return back();
    }
}
