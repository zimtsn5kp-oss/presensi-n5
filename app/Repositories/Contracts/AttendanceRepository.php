<?php

namespace App\Repositories\Contracts;

interface AttendanceRepository
{
    public function dashboardMetrics(): array;

    public function monthlyPercentage(?string $classroom = null): float;

    public function dailyChart(): array;

    public function weeklyLateChart(): array;
}
