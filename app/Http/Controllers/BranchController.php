<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
class BranchController extends Controller {
    public function index() { return Branch::all(); }
    public function store(Request $r) { return Branch::create($r->validate(['name'=>'required','company_id'=>'required|exists:companies,id','brand_id'=>'nullable|exists:brands,id'])); }
    public function show(Branch $branch) { return $branch; }
    public function update(Request $r, Branch $branch) { $branch->update($r->all()); return $branch; }
    public function destroy(Branch $branch) { $branch->delete(); return response()->noContent(); }
}