<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        return view('holidays.index', ['holidays' => Holiday::latest('start_date')->get()]);
    }

    public function store(Request $request)
    {
        Holiday::create($request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:nasional,madrasah,cuti_bersama,akademik,hari_besar_islam'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
        ]));

        return back()->with('success', 'Hari libur tersimpan.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return back()->with('success', 'Hari libur dihapus.');
    }
}
