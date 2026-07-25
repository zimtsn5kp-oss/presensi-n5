<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MonthlyRecapExport implements FromView
{
    public function __construct(private readonly array $data)
    {
    }

    public function view(): View
    {
        return view('exports.monthly-recap', $this->data);
    }
}
