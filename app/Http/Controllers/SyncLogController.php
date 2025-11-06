<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SyncLog;

class SyncLogController extends Controller
{
    public function index()
    {
        return SyncLog::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pos_device_id' => 'required|exists:pos_devices,id',
            'sync_status' => 'required|string|max:255',
            'synced_at' => 'required|date',
            // other fields if needed
        ]);

        return SyncLog::create($validated);
    }

    public function show(SyncLog $syncLog)
    {
        return $syncLog;
    }

    public function update(Request $request, SyncLog $syncLog)
    {
        $syncLog->update($request->all());
        return $syncLog;
    }

    public function destroy(SyncLog $syncLog)
    {
        $syncLog->delete();
        return response()->noContent();
    }
}
