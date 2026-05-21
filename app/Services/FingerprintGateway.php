<?php

namespace App\Services;

use App\Models\Fingerprint;
use App\Models\Student;
use RuntimeException;

class FingerprintGateway
{
    public function testConnection(Fingerprint $machine): array
    {
        $start = microtime(true);
        $socket = @fsockopen($machine->ip_address, $machine->port, $errno, $errstr, 3);

        if (! $socket) {
            throw new RuntimeException("Tidak dapat terhubung ke {$machine->ip_address}:{$machine->port}. {$errstr}");
        }

        fclose($socket);

        return [
            'online' => true,
            'latency_ms' => round((microtime(true) - $start) * 1000),
        ];
    }

    public function downloadAttendance(Fingerprint $machine): array
    {
        $this->testConnection($machine);

        return [
            'rows' => [],
            'message' => 'Koneksi mesin valid. Pasang SDK fingerprint Solution/ZKTeco pada method ini untuk membaca log real.',
        ];
    }

    public function syncUsers(Fingerprint $machine): array
    {
        $this->testConnection($machine);

        return ['processed' => 0, 'message' => 'Sinkronisasi user siap dihubungkan ke SDK vendor.'];
    }

    public function sendStudent(Fingerprint $machine, Student $student): array
    {
        $this->testConnection($machine);

        $payload = [
            'pin' => $student->fingerprint_id,
            'name' => $student->name,
            'nisn' => $student->nisn,
            'classroom' => $student->classroom,
            'privilege' => 0,
            'password' => '',
            'card' => '',
            'enabled' => $student->is_active,
        ];

        /*
         * Hubungkan payload ini ke SDK/bridge mesin fingerprint yang dipakai.
         * Contoh pada SDK ZKTeco/Solution biasanya setUser($pin, $name, ...)
         * lalu enroll sidik jari dilakukan dari menu mesin atau command enroll SDK.
         */
        return [
            'processed' => 1,
            'payload' => $payload,
            'message' => 'Data siswa siap dikirim. Lengkapi method sendStudent() dengan SDK fingerprint vendor untuk push real ke mesin.',
        ];
    }

    public function backupTemplates(Fingerprint $machine): array
    {
        $this->testConnection($machine);

        return ['processed' => 0, 'message' => 'Backup template siap dihubungkan ke SDK vendor.'];
    }

    public function restoreTemplates(Fingerprint $machine): array
    {
        $this->testConnection($machine);

        return ['processed' => 0, 'message' => 'Restore template siap dihubungkan ke SDK vendor.'];
    }
}
