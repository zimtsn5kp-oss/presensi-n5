@extends('layouts.app', ['title' => 'Input Ketidakhadiran'])
@section('content')
<form method="post" action="{{ route('attendance.absence.store') }}" class="max-w-3xl rounded-3xl bg-white p-6 shadow-sm">@csrf
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            @php($selectedStudent = old('attendable_id') ? $students->firstWhere('id', (int) old('attendable_id')) : null)
            <label class="text-sm font-semibold">Nama Murid / NISN / Kelas</label>
            <div id="studentCombobox" class="relative mt-2">
                <input id="studentId" type="hidden" name="attendable_id" value="{{ $selectedStudent?->id }}">
                <button id="studentComboboxButton" type="button" aria-haspopup="listbox" aria-expanded="false" class="flex w-full items-center justify-between gap-3 rounded-2xl border bg-white px-4 py-3 text-left">
                    <span id="selectedStudentLabel">{{ $selectedStudent ? $selectedStudent->name.' - '.$selectedStudent->classroom : 'Pilih murid' }}</span>
                    <span class="text-slate-400">&#9662;</span>
                </button>
                <div id="studentDropdown" class="absolute z-30 mt-2 hidden w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                    <div class="border-b border-slate-100 p-3">
                        <input id="studentSearch" type="search" autocomplete="off" placeholder="Cari nama, NISN, atau kelas" class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-emerald-500">
                    </div>
                    <div id="studentOptions" role="listbox" class="max-h-64 overflow-y-auto p-2">
                        @foreach($students as $student)
                            <button type="button" role="option" data-student-id="{{ $student->id }}" data-student-label="{{ $student->name }} - {{ $student->classroom }}" data-search="{{ $student->name }} {{ $student->nisn }} {{ $student->classroom }}" class="student-option block w-full rounded-xl px-3 py-2 text-left hover:bg-emerald-50">
                                <strong class="block text-sm text-slate-800">{{ $student->name }}</strong>
                                <span class="text-xs text-slate-500">NISN: {{ $student->nisn }} | Kelas {{ $student->classroom }}</span>
                            </button>
                        @endforeach
                        <p id="studentSearchEmpty" class="hidden px-3 py-6 text-center text-sm text-slate-500">Murid tidak ditemukan.</p>
                    </div>
                </div>
            </div>
        </div>
        <label class="text-sm font-semibold">Tanggal<input type="date" name="absence_date" value="{{ now()->toDateString() }}" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
        <label class="text-sm font-semibold">Status<select name="type" class="mt-2 w-full rounded-2xl border px-4 py-3"><option value="izin">Izin</option><option value="sakit">Sakit</option><option value="alfa">Alfa</option></select></label>
        <label class="md:col-span-2 text-sm font-semibold">Keterangan<textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border px-4 py-3"></textarea></label>
    </div>
    <button class="mt-6 rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan Ketidakhadiran</button>
</form>
@endsection

@push('scripts')
<script>
const studentCombobox = document.getElementById('studentCombobox');
const studentComboboxButton = document.getElementById('studentComboboxButton');
const studentDropdown = document.getElementById('studentDropdown');
const studentSearch = document.getElementById('studentSearch');
const studentId = document.getElementById('studentId');
const selectedStudentLabel = document.getElementById('selectedStudentLabel');
const studentSearchEmpty = document.getElementById('studentSearchEmpty');
const studentOptions = Array.from(document.querySelectorAll('.student-option'));

function setStudentDropdown(open) {
    studentDropdown.classList.toggle('hidden', !open);
    studentComboboxButton.setAttribute('aria-expanded', open ? 'true' : 'false');

    if (open) {
        studentSearch.focus();
    }
}

studentComboboxButton.addEventListener('click', function () {
    setStudentDropdown(studentDropdown.classList.contains('hidden'));
});

studentSearch.addEventListener('input', function () {
    const keyword = this.value.trim().toLocaleLowerCase('id');
    let visibleCount = 0;

    studentOptions.forEach(function (option) {
        const matches = option.dataset.search.toLocaleLowerCase('id').includes(keyword);
        option.classList.toggle('hidden', !matches);
        visibleCount += matches ? 1 : 0;
    });

    studentSearchEmpty.classList.toggle('hidden', visibleCount > 0);
});

studentOptions.forEach(function (option) {
    option.addEventListener('click', function () {
        studentId.value = this.dataset.studentId;
        selectedStudentLabel.textContent = this.dataset.studentLabel;
        studentSearch.value = '';
        studentOptions.forEach((item) => item.classList.remove('hidden'));
        studentSearchEmpty.classList.add('hidden');
        setStudentDropdown(false);
    });
});

document.addEventListener('click', function (event) {
    if (!studentCombobox.contains(event.target)) {
        setStudentDropdown(false);
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        setStudentDropdown(false);
        studentComboboxButton.focus();
    }
});
</script>
@endpush
