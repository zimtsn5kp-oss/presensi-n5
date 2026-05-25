@extends('layouts.app', ['title' => 'Scan Barcode'])

@section('content')
<div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
    <section class="rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-emerald-950">Scanner Absensi</h2>
                <p class="text-sm text-slate-500">Gunakan webcam atau scanner USB. Kode dapat berupa Barcode ID/PIN, NISN, atau NIP.</p>
            </div>
            <span id="scannerState" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Siap</span>
        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-slate-950">
            <video id="cameraPreview" class="aspect-video w-full bg-slate-950 object-cover" muted playsinline></video>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" id="startCamera" class="rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-bold text-white shadow hover:bg-emerald-800">Aktifkan Webcam</button>
            <button type="button" id="stopCamera" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">Matikan</button>
        </div>

        <form id="barcodeForm" method="post" action="{{ route('attendance.barcode.store') }}" class="mt-6 rounded-3xl border border-emerald-100 bg-emerald-50/60 p-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-[1fr_220px]">
                <label class="text-sm font-semibold text-emerald-950">Input scanner USB / kode manual
                    <input id="barcodeInput" name="barcode" autocomplete="off" autofocus placeholder="Scan atau ketik kode barcode" class="mt-2 w-full rounded-2xl border border-emerald-100 bg-white px-4 py-3 text-lg font-bold tracking-wide outline-none focus:border-emerald-500">
                </label>
                <label class="text-sm font-semibold text-emerald-950">Mode
                    <select id="attendanceType" name="attendance_type" class="mt-2 w-full rounded-2xl border border-emerald-100 bg-white px-4 py-3">
                        <option value="auto">Otomatis</option>
                        <option value="check_in">Masuk</option>
                        <option value="check_out">Pulang</option>
                    </select>
                </label>
            </div>
            <button class="mt-4 rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-bold text-white shadow hover:bg-emerald-800">Simpan Scan</button>
        </form>
    </section>

    <aside class="rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="font-bold text-emerald-950">Hasil Terakhir</h2>
        <div id="scanResult" class="mt-5 rounded-3xl border border-slate-100 bg-slate-50 p-5">
            <p class="text-sm text-slate-500">Belum ada scan.</p>
        </div>
        <div class="mt-5 rounded-3xl border border-amber-100 bg-amber-50 p-5 text-sm text-amber-800">
            <p class="font-bold">Format barcode yang didukung</p>
            <p class="mt-2">Contoh: <strong>1001</strong>, <strong>0061234501</strong>, <strong>S:1001</strong>, atau <strong>T:2001</strong>.</p>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
const form = document.getElementById('barcodeForm');
const input = document.getElementById('barcodeInput');
const mode = document.getElementById('attendanceType');
const result = document.getElementById('scanResult');
const state = document.getElementById('scannerState');
const video = document.getElementById('cameraPreview');
let stream = null;
let detector = null;
let lastCode = '';
let lastScanAt = 0;
let scanning = false;

function setState(text, tone = 'slate') {
    const tones = {
        slate: 'rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700',
        amber: 'rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700',
        emerald: 'rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700',
        red: 'rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700',
    };

    state.textContent = text;
    state.className = tones[tone] || tones.slate;
}

function renderResult(payload, ok) {
    const data = payload.data || {};
    result.className = `mt-5 rounded-3xl border p-5 ${ok ? 'border-emerald-100 bg-emerald-50' : 'border-red-100 bg-red-50'}`;
    result.innerHTML = `
        <p class="text-sm font-bold ${ok ? 'text-emerald-800' : 'text-red-700'}">${payload.message}</p>
        ${data.name ? `<div class="mt-4 space-y-2 text-sm text-slate-700">
            <div class="flex justify-between gap-3"><span>Nama</span><strong>${data.name}</strong></div>
            <div class="flex justify-between gap-3"><span>Masuk</span><strong>${data.check_in_at || '-'}</strong></div>
            <div class="flex justify-between gap-3"><span>Pulang</span><strong>${data.check_out_at || '-'}</strong></div>
            <div class="flex justify-between gap-3"><span>Status</span><strong>${data.status || '-'}</strong></div>
        </div>` : ''}
    `;
}

async function submitBarcode(code) {
    const cleanCode = code.trim();
    const now = Date.now();

    if (!cleanCode || (cleanCode === lastCode && now - lastScanAt < 2500)) {
        return;
    }

    lastCode = cleanCode;
    lastScanAt = now;
    setState('Menyimpan', 'amber');

    const response = await fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({barcode: cleanCode, attendance_type: mode.value}),
    });
    const payload = await response.json();
    renderResult(payload, response.ok && payload.success);
    setState(response.ok && payload.success ? 'Berhasil' : 'Gagal', response.ok && payload.success ? 'emerald' : 'red');
    input.value = '';
    input.focus();
}

form.addEventListener('submit', function (event) {
    event.preventDefault();
    submitBarcode(input.value);
});

document.getElementById('startCamera').addEventListener('click', async function () {
    if (!('BarcodeDetector' in window)) {
        setState('Browser tidak mendukung', 'red');
        renderResult({message: 'BarcodeDetector belum tersedia di browser ini. Gunakan Chrome/Edge terbaru atau scanner USB.'}, false);
        return;
    }

    detector = new BarcodeDetector({formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'qr_code']});
    stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}, audio: false});
    video.srcObject = stream;
    await video.play();
    scanning = true;
    setState('Webcam aktif', 'emerald');
    detectLoop();
});

document.getElementById('stopCamera').addEventListener('click', function () {
    scanning = false;
    stream?.getTracks().forEach((track) => track.stop());
    stream = null;
    video.srcObject = null;
    setState('Siap', 'slate');
});

async function detectLoop() {
    if (!scanning || !detector) {
        return;
    }

    try {
        const codes = await detector.detect(video);
        if (codes.length > 0) {
            submitBarcode(codes[0].rawValue);
        }
    } catch (error) {
        setState('Kamera error', 'red');
    }

    requestAnimationFrame(detectLoop);
}
</script>
@endpush
