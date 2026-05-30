<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'school' => SchoolSetting::firstOrCreate([]),
            'attendanceSettings' => AttendanceSetting::orderBy('day_of_week')->get(),
        ]);
    }

    public function updateSchool(Request $request)
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'active_semester' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('school', 'public');
        }

        unset($data['logo']);

        SchoolSetting::firstOrCreate([])->update($data);

        return back()->with('success', 'Pengaturan sekolah diperbarui.');
    }

    public function storeAttendance(Request $request)
    {
        AttendanceSetting::updateOrCreate(
            ['id' => $request->integer('id') ?: null],
            $request->validate([
                'day_of_week' => ['nullable', 'integer', 'between:1,5'],
                'name' => ['required', 'string', 'max:100'],
                'check_in_start' => ['required', 'date_format:H:i'],
                'check_in_end' => ['required', 'date_format:H:i'],
                'late_after' => ['required', 'date_format:H:i'],
                'check_out_start' => ['nullable', 'date_format:H:i'],
                'check_out_end' => ['nullable', 'date_format:H:i'],
                'is_ramadhan' => ['nullable', 'boolean'],
                'is_active' => ['nullable', 'boolean'],
            ]) + ['is_ramadhan' => $request->boolean('is_ramadhan'), 'is_active' => $request->boolean('is_active', true)]
        );

        return back()->with('success', 'Jam absensi tersimpan.');
    }
}
