<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\CarbonInterface;

class SchoolCalendar
{
    public function isHoliday(CarbonInterface $date): bool
    {
        return $date->isWeekend() || $this->manualHoliday($date) !== null;
    }

    public function holidayName(CarbonInterface $date): ?string
    {
        if ($date->isSaturday()) {
            return 'Libur rutin hari Sabtu';
        }

        if ($date->isSunday()) {
            return 'Libur rutin hari Minggu';
        }

        return $this->manualHoliday($date)?->name;
    }

    private function manualHoliday(CarbonInterface $date): ?Holiday
    {
        return Holiday::query()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
    }
}
