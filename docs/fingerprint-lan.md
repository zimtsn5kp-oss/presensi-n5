# Dokumentasi Integrasi Fingerprint LAN

## Alur

1. Administrator menambahkan mesin fingerprint dengan IP lokal dan port.
2. Sistem melakukan tes koneksi TCP.
3. Bridge/SDK vendor membaca user, template sidik jari, dan log absensi dari mesin.
4. Data dikirim ke aplikasi melalui REST API atau langsung melalui `FingerprintGateway`.
5. Sistem mencocokkan `machine_user_id` dengan `students.fingerprint_id` atau `teachers.fingerprint_id`.
6. Log disimpan ke `attendance_logs`.

## Mapping Data

- `fingerprints`: metadata mesin.
- `fingerprint_templates`: backup template sidik jari.
- `fingerprint_sync_histories`: histori download, sync, backup, restore.
- `attendance_logs`: hasil absensi harian.

## Endpoint Bridge

```http
POST /api/fingerprints/{id}/ping
POST /api/fingerprints/{id}/logs
```

Gunakan rate limit bawaan route API dan batasi akses pada jaringan lokal.

## Implementasi SDK Vendor

Edit `App\Services\FingerprintGateway`.

- `downloadAttendance`: baca log dari mesin, simpan ke `attendance_logs`.
- `syncUsers`: kirim daftar siswa/guru aktif ke mesin.
- `backupTemplates`: baca template sidik jari dari mesin, simpan ke database.
- `restoreTemplates`: kirim template dari database ke mesin.

Service ini sengaja dibuat tipis agar bisa memakai SDK PHP, command bridge Node/Python, atau service Windows terpisah tanpa mengubah controller Blade.
