<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\POSLimit;

class POSLimitController extends Controller
{
    public function index()
    {
        return POSLimit::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'brand_id' => 'nullable|exists:brands,id',
            'device_limit' => 'required|integer|min:1',
        ]);

        return POSLimit::create($validated);
    }

    public function show(POSLimit $posLimit)
    {
        return $posLimit;
    }

    public function update(Request $request, POSLimit $posLimit)
    {
        $posLimit->update($request->all());
        return $posLimit;
    }

    public function destroy(POSLimit $posLimit)
    {
        $posLimit->delete();
        return response()->noContent();
    }
}
