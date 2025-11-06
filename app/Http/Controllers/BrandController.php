<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller {
    public function index() { return Brand::all(); }
    public function store(Request $r) {
        Brand::create($r->validate([
            'name' => 'required',
            'company_id' => 'required|exists:companies,id'
        ]));
        return redirect()->route('brands.index')->with('success', 'Brand created successfully!');
    }
    public function show(Brand $brand) { return $brand; }
    public function update(Request $r, Brand $brand) { $brand->update($r->all()); return $brand; }
    public function destroy(Brand $brand) { $brand->delete(); return response()->noContent(); }
}