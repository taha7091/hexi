<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        return Notification::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'is_read' => 'boolean',
        ]);

        return Notification::create($validated);
    }

    public function show(Notification $notification)
    {
        return $notification;
    }

    public function update(Request $request, Notification $notification)
    {
        $notification->update($request->all());
        return $notification;
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->noContent();
    }
}
