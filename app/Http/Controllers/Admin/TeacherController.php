<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TeacherImportTemplateExport;
use App\Exports\TeachersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Imports\TeachersImport;
use App\Models\Teacher;
use App\Services\ActivityLogger;
use App\Services\TeacherAccountService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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

    public function store(TeacherRequest $request, ActivityLogger $logger, TeacherAccountService $accounts)
    {
        $data = $request->safe()->except(['password', 'password_confirmation']);
        $teacher = Teacher::create($data + ['is_active' => $request->boolean('is_active', true)]);
        $accounts->sync($teacher);
        $logger->record('teacher.created', $teacher);

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.form', compact('teacher'));
    }

    public function update(TeacherRequest $request, Teacher $teacher, ActivityLogger $logger, TeacherAccountService $accounts)
    {
        $data = $request->safe()->except(['password', 'password_confirmation']);
        $teacher->update($data + ['is_active' => $request->boolean('is_active')]);
        $accounts->sync($teacher, $request->string('password')->toString() ?: null);
        $logger->record('teacher.updated', $teacher);

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher, ActivityLogger $logger, TeacherAccountService $accounts)
    {
        $logger->record('teacher.deleted', $teacher);
        $accounts->deactivate($teacher);
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

    public function import(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:4096'],
        ], [
            'file.required' => 'Pilih file Excel yang akan diimport.',
            'file.mimes' => 'File import harus berformat xlsx, xls, atau csv.',
        ]);

        $import = new TeachersImport();
        Excel::import($import, $data['file']);

        return redirect()
            ->route('teachers.index')
            ->with('success', "{$import->created} guru baru ditambahkan, {$import->updated} guru diperbarui.");
    }

    public function importTemplate()
    {
        return Excel::download(new TeacherImportTemplateExport(), 'format-import-guru.xlsx');
    }
}
