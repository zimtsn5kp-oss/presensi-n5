<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Fingerprint;
use App\Models\FingerprintSyncHistory;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ActivityLogger;
use App\Services\FingerprintGateway;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        return view('students.index', [
            'machines' => Fingerprint::orderBy('name')->get(),
        ]);
    }

    public function data()
    {
        return DataTables::of(Student::query())
            ->addColumn('select', fn ($student) => '<input form="syncStudentsForm" type="checkbox" name="student_ids[]" value="'.$student->id.'" class="student-check h-4 w-4 rounded border-emerald-300 text-emerald-700">')
            ->addColumn('status_badge', fn ($student) => view('partials.status', ['active' => $student->is_active])->render())
            ->addColumn('actions', fn ($student) => view('students.partials.actions', compact('student'))->render())
            ->rawColumns(['select', 'status_badge', 'actions'])
            ->make();
    }

    public function create()
    {
        return view('students.form', [
            'student' => new Student(),
        ]);
    }

    public function store(StudentRequest $request, ActivityLogger $logger)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($data + ['is_active' => $request->boolean('is_active', true)]);
        $logger->record('student.created', $student);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        return view('students.form', [
            'student' => $student,
        ]);
    }

    public function update(StudentRequest $request, Student $student, ActivityLogger $logger)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($data + ['is_active' => $request->boolean('is_active')]);
        $logger->record('student.updated', $student);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student, ActivityLogger $logger)
    {
        $logger->record('student.deleted', $student);
        $student->delete();

        return back()->with('success', 'Data siswa dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new StudentsExport(), 'data-siswa.xlsx');
    }

    public function exportPdf()
    {
        return Pdf::loadView('exports.students', ['students' => Student::orderBy('classroom')->orderBy('name')->get()])
            ->download('data-siswa.pdf');
    }

    public function syncToMachine(Request $request, FingerprintGateway $fingerprintGateway)
    {
        $data = $request->validate([
            'fingerprint_id' => ['required', 'exists:fingerprints,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ], [
            'student_ids.required' => 'Pilih minimal satu siswa untuk dikirim ke mesin fingerprint.',
            'fingerprint_id.required' => 'Pilih mesin fingerprint tujuan.',
        ]);

        $students = Student::whereIn('id', $data['student_ids'])->orderBy('name')->get();
        $sent = 0;
        $failed = 0;
        $messages = [];

        foreach ($students as $student) {
            if (blank($student->fingerprint_id)) {
                $student->update(['fingerprint_id' => $this->nextFingerprintId()]);
            }

            $result = $this->sendStudentToMachine($request, $fingerprintGateway, $student->fresh(), (int) $data['fingerprint_id']);
            if ($result['type'] === 'success') {
                $sent++;
            } else {
                $failed++;
                $messages[] = $student->name.': '.$result['text'];
            }
        }

        if ($failed > 0) {
            return back()->with('error', "{$sent} siswa berhasil diproses, {$failed} gagal. ".implode(' ', array_slice($messages, 0, 2)));
        }

        return back()->with('success', "{$sent} siswa berhasil diproses untuk dikirim ke mesin fingerprint.");
    }

    private function sendStudentToMachine(Request $request, FingerprintGateway $fingerprintGateway, Student $student, int $machineId): array
    {
        $machine = Fingerprint::findOrFail($machineId);
        $history = FingerprintSyncHistory::create([
            'fingerprint_id' => $machine->id,
            'user_id' => $request->user()->id,
            'action' => 'send-student',
            'status' => 'running',
            'processed_rows' => 0,
            'payload' => ['student_id' => $student->id, 'fingerprint_id' => $student->fingerprint_id],
        ]);

        try {
            $result = $fingerprintGateway->sendStudent($machine, $student);
            $machine->update(['is_online' => true, 'last_sync_at' => now()]);
            $history->update([
                'status' => 'success',
                'processed_rows' => $result['processed'] ?? 1,
                'message' => $result['message'] ?? 'Data siswa berhasil dikirim ke mesin.',
                'payload' => $result,
            ]);

            return ['type' => 'success', 'text' => 'Data siswa tersimpan dan proses kirim ke mesin fingerprint tercatat.'];
        } catch (\Throwable $exception) {
            $machine->update(['is_online' => false]);
            $history->update(['status' => 'failed', 'message' => $exception->getMessage()]);

            return ['type' => 'error', 'text' => 'Data siswa tersimpan, tetapi gagal menghubungi mesin fingerprint: '.$exception->getMessage()];
        }
    }

    private function nextFingerprintId(): int
    {
        $maxStudent = (int) Student::max('fingerprint_id');
        $maxTeacher = (int) Teacher::max('fingerprint_id');

        return max($maxStudent, $maxTeacher, 1000) + 1;
    }
}
