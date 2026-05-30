<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Services\SchoolCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class MonthlyRecapController extends Controller
{
    public function __invoke(Request $request, SchoolCalendar $calendar)
    {
        $filters = $request->validate([
            'classroom' => ['nullable', Rule::in($this->classrooms())],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2020,2100'],
        ]);

        $month = (int) (($filters['month'] ?? null) ?: now()->month);
        $year = (int) (($filters['year'] ?? null) ?: now()->year);
        $classroom = $filters['classroom'] ?? null;
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $students = Student::query()
            ->where('is_active', true)
            ->when($classroom, fn ($query) => $query->where('classroom', $classroom))
            ->orderBy('classroom')
            ->orderBy('name')
            ->get();

        $logs = AttendanceLog::query()
            ->where('attendable_type', Student::class)
            ->whereIn('attendable_id', $students->pluck('id'))
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($log) => $log->attendable_id.'-'.$log->attendance_date->format('Y-m-d'));

        $dates = collect(range(1, $end->day))->map(function (int $day) use ($start, $calendar) {
            $date = $start->copy()->day($day);

            return [
                'date' => $date,
                'key' => $date->format('Y-m-d'),
                'is_weekend' => $date->isWeekend(),
                'is_holiday' => $calendar->isHoliday($date),
                'holiday_name' => $calendar->holidayName($date),
            ];
        });

        $rows = $students->map(function (Student $student) use ($dates, $logs) {
            $summary = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0];
            $days = $dates->mapWithKeys(function (array $date) use ($student, $logs, &$summary) {
                $status = $logs->get($student->id.'-'.$date['key'])?->first()?->status;

                if (in_array($status, ['hadir', 'terlambat'], true)) {
                    $summary['hadir']++;
                } elseif (isset($summary[$status])) {
                    $summary[$status]++;
                }

                return [$date['key'] => $status];
            });

            return compact('student', 'days', 'summary');
        });

        return view('recaps.monthly', [
            'classrooms' => $this->classrooms(),
            'months' => $this->months(),
            'filters' => compact('classroom', 'month', 'year'),
            'dates' => $dates,
            'rows' => $rows,
            'title' => $this->months()[$month].' '.$year,
        ]);
    }

    private function classrooms(): array
    {
        return ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'];
    }

    private function months(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}
