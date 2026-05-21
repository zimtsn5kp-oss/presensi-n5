<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FingerprintRequest;
use App\Models\Fingerprint;
use App\Models\FingerprintSyncHistory;
use App\Services\ActivityLogger;
use App\Services\FingerprintGateway;
use Illuminate\Http\Request;

class FingerprintController extends Controller
{
    public function index()
    {
        return view('fingerprint.index', [
            'machines' => Fingerprint::latest()->get(),
            'histories' => FingerprintSyncHistory::with('fingerprint')->latest()->limit(20)->get(),
        ]);
    }

    public function store(FingerprintRequest $request, ActivityLogger $logger)
    {
        $machine = Fingerprint::create($request->validated());
        $logger->record('fingerprint.created', $machine);

        return back()->with('success', 'Mesin fingerprint ditambahkan.');
    }

    public function update(FingerprintRequest $request, Fingerprint $fingerprint, ActivityLogger $logger)
    {
        $fingerprint->update($request->validated());
        $logger->record('fingerprint.updated', $fingerprint);

        return back()->with('success', 'Mesin fingerprint diperbarui.');
    }

    public function destroy(Fingerprint $fingerprint, ActivityLogger $logger)
    {
        $logger->record('fingerprint.deleted', $fingerprint);
        $fingerprint->delete();

        return back()->with('success', 'Mesin fingerprint dihapus.');
    }

    public function action(Request $request, Fingerprint $fingerprint, FingerprintGateway $gateway)
    {
        $action = $request->validate(['action' => ['required', 'in:test,download,sync-users,backup-templates,restore-templates']])['action'];
        $history = FingerprintSyncHistory::create([
            'fingerprint_id' => $fingerprint->id,
            'user_id' => $request->user()->id,
            'action' => $action,
            'status' => 'running',
        ]);

        try {
            $result = match ($action) {
                'test' => $gateway->testConnection($fingerprint),
                'download' => $gateway->downloadAttendance($fingerprint),
                'sync-users' => $gateway->syncUsers($fingerprint),
                'backup-templates' => $gateway->backupTemplates($fingerprint),
                'restore-templates' => $gateway->restoreTemplates($fingerprint),
            };

            $fingerprint->update([
                'is_online' => true,
                'last_sync_at' => now(),
                'machine_user_count' => $result['processed'] ?? $fingerprint->machine_user_count,
            ]);
            $history->update(['status' => 'success', 'processed_rows' => $result['processed'] ?? count($result['rows'] ?? []), 'message' => $result['message'] ?? 'Berhasil', 'payload' => $result]);

            return back()->with('success', $history->message);
        } catch (\Throwable $exception) {
            $fingerprint->update(['is_online' => false]);
            $history->update(['status' => 'failed', 'message' => $exception->getMessage()]);

            return back()->with('error', $exception->getMessage());
        }
    }
}
