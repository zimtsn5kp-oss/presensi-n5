<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentImportTemplateExport;
use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Imports\StudentsImport;
use App\Models\Student;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        return view('students.index', [
            'classrooms' => $this->classrooms(),
        ]);
    }

    public function data(Request $request)
    {
        $query = Student::query()
            ->select('students.*')
            ->when($request->filled('classroom'), fn ($query) => $query->where('classroom', $request->string('classroom')));

        $table = DataTables::of($query)
            ->addColumn('status_badge', fn ($student) => view('partials.status', ['active' => $student->is_active])->render())
            ->rawColumns(['status_badge']);

        if ($request->user()->hasRole('administrator')) {
            $table->addColumn('actions', fn ($student) => view('students.partials.actions', compact('student'))->render())
                ->rawColumns(['status_badge', 'actions']);
        }

        return $table->make();
    }

    public function create()
    {
        return view('students.form', [
            'student' => new Student(),
            'classrooms' => $this->classrooms(),
        ]);
    }

    public function store(StudentRequest $request, ActivityLogger $logger)
    {
        $data = $request->validated();

        $student = Student::create($data + ['is_active' => $request->boolean('is_active', true)]);
        $logger->record('student.created', $student);

        return redirect()->route('students.index')->with('success', 'Data murid berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        return view('students.form', [
            'student' => $student,
            'classrooms' => $this->classrooms(),
        ]);
    }

    public function update(StudentRequest $request, Student $student, ActivityLogger $logger)
    {
        $data = $request->validated();

        $student->update($data + ['is_active' => $request->boolean('is_active')]);
        $logger->record('student.updated', $student);

        return redirect()->route('students.index')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy(Student $student, ActivityLogger $logger)
    {
        $logger->record('student.deleted', $student);
        $student->delete();

        return back()->with('success', 'Data murid dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new StudentsExport(), 'data-murid.xlsx');
    }

    public function exportPdf()
    {
        return Pdf::loadView('exports.students', ['students' => Student::orderBy('classroom')->orderBy('name')->get()])
            ->download('data-murid.pdf');
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:4096'],
        ], [
            'file.required' => 'Pilih file Excel yang akan diimport.',
            'file.mimes' => 'File import harus berformat xlsx, xls, atau csv.',
        ]);

        $import = new StudentsImport();
        Excel::import($import, $data['file']);

        return redirect()
            ->route('students.index')
            ->with('success', "{$import->created} murid baru ditambahkan, {$import->updated} murid diperbarui.");
    }

    public function importTemplate()
    {
        return Excel::download(new StudentImportTemplateExport(), 'format-import-murid.xlsx');
    }

    private function classrooms(): array
    {
        return ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C'];
    }
}
