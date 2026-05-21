# Absensi Madrasah Fingerprint

Aplikasi absensi madrasah berbasis Laravel Blade, MySQL, Tailwind, Yajra DataTables server-side, Laravel Excel, DomPDF, dan REST API untuk komunikasi mesin fingerprint di jaringan LAN.

## Fitur

- Dashboard responsif bernuansa madrasah dengan kartu statistik dan ApexCharts.
- CRUD siswa dan guru dengan relasi `fingerprint_id`, server-side DataTables, export Excel/PDF.
- Modul mesin fingerprint: IP lokal, port, status koneksi, tes koneksi, download log, sync user, backup/restore template.
- Input kehadiran manual untuk lomba, kejuaraan, tugas luar, dan dispensasi resmi.
- Input izin, sakit, alfa, dan dispensasi dengan upload bukti.
- Kalender hari libur nasional, madrasah, cuti bersama, akademik, dan hari besar Islam.
- Laporan absensi dengan filter tanggal/status serta export Excel/PDF.
- Role administrator dan guru, middleware role, audit activity log, validasi upload, rate limit API.

## Instalasi Lokal

1. Salin `.env.example` menjadi `.env`.
2. Atur database MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensi_n5
DB_USERNAME=root
DB_PASSWORD=
```

3. Jalankan:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Akun demo:

- Admin: `admin@madrasah.test` / `password`
- Guru: `guru@madrasah.test` / `password`

## Integrasi Fingerprint LAN

Daftarkan mesin di menu **Fingerprint** dengan IP lokal dan port mesin, umumnya `4370`. Tombol **Tes** memakai koneksi TCP (`fsockopen`) untuk memastikan aplikasi dapat menjangkau mesin melalui LAN.

Endpoint API untuk bridge/agent fingerprint:

```http
POST /api/fingerprints/{fingerprint}/ping
POST /api/fingerprints/{fingerprint}/logs
```

Payload contoh log:

```json
{
  "machine_user_id": 1001,
  "attendance_date": "2026-05-21",
  "check_in_at": "06:48:00",
  "status": "hadir",
  "late_minutes": 0
}
```

Method `App\Services\FingerprintGateway` adalah boundary integrasi vendor. Pasang SDK mesin Solution/ZKTeco di service tersebut untuk implementasi nyata: tarik log, kirim user, enroll sidik jari, backup template, dan restore template.

## Struktur Penting

- `app/Models`: model domain absensi.
- `app/Http/Controllers/Admin`: controller Blade admin/guru.
- `app/Http/Controllers/Api`: REST API fingerprint.
- `app/Repositories`: repository dashboard/rekap.
- `app/Services`: gateway fingerprint dan activity logger.
- `database/migrations`: schema MySQL ter-normalisasi.
- `resources/views`: Blade UI modern.

## Catatan Produksi

- Aktifkan HTTPS dan batasi API fingerprint hanya dari subnet LAN madrasah.
- Tambahkan token/HMAC pada bridge fingerprint bila agent berjalan di mesin terpisah.
- Jalankan sinkronisasi besar melalui queue worker.
- Backup database otomatis dapat dipasang lewat scheduler Laravel dan tabel `backups`.
