<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TeachersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function index()
    {
        return view('teachers.index');
    }

    public function data()
    {
        return DataTables::of(Teacher::query())
            ->addColumn('status_badge', fn ($teacher) => view('partials.status', ['active' => $teacher->is_active])->render())
            ->addColumn('actions', fn ($teacher) => view('teachers.partials.actions', compact('teacher'))->render())
            ->rawColumns(['status_badge', 'actions'])
            ->make();
    }

    public function create()
    {
        return view('teachers.form', ['teacher' => new Teacher()]);
    }

    public function store(TeacherRequest $request, ActivityLogger $logger)
    {
        $teacher = Teacher::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);
        $logger->record('teacher.created', $teacher);

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.form', compact('teacher'));
    }

    public function update(TeacherRequest $request, Teacher $teacher, ActivityLogger $logger)
    {
        $teacher->update($request->validated() + ['is_active' => $request->boolean('is_active')]);
        $logger->record('teacher.updated', $teacher);

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher, ActivityLogger $logger)
    {
        $logger->record('teacher.deleted', $teacher);
        $teacher->delete();

        return back()->with('success', 'Data guru dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new TeachersExport(), 'data-guru.xlsx');
    }

    public function exportPdf()
    {
        return Pdf::loadView('exports.teachers', ['teachers' => Teacher::orderBy('name')->get()])->download('data-guru.pdf');
    }
}
