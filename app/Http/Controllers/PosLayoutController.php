<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\POSLayout;

class POSLayoutController extends Controller
{
    public function index()
    {
        return POSLayout::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pos_device_id' => 'required|exists:pos_devices,id',
            'layout_json' => 'required|json',
        ]);

        return POSLayout::create($validated);
    }

    public function show(POSLayout $posLayout)
    {
        return $posLayout;
    }

    public function update(Request $request, POSLayout $posLayout)
    {
        $posLayout->update($request->all());
        return $posLayout;
    }

    public function destroy(POSLayout $posLayout)
    {
        $posLayout->delete();
        return response()->noContent();
    }
}
