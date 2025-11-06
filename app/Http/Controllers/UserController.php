<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company; // 👈 Make sure to import the Company model

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function store(Request $r)
    {
        $validated = $r->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $validated['password'] = bcrypt($validated['password']);

        return User::create($validated);
    }

    public function show(User $user)
    {
        return $user;
    }

    public function update(Request $r, User $user)
    {
        $user->update($r->except(['password']));
        return $user;
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }

    // 🚀 NEW METHOD TO FIX THE 500 ERROR 🚀
    public function getUsersForCompany($id)
    {
        // Find the company by its ID
        $company = Company::find($id);

        // If the company doesn't exist, return a 404 response
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        // Use the relationship to get all users for this company
        $users = $company->users;

        // Return the users as a JSON response
        return response()->json(['success' => true, 'data' => $users]);
    }
}