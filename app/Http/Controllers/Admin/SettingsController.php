<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get current theme from session or default to light
        $currentTheme = Session::get('theme', 'light');
        
        return view('admin.settings.index', compact('currentTheme'));
    }

    /**
     * Update theme setting
     */
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark'
        ]);

        // Store theme in session
        Session::put('theme', $validated['theme']);

        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully',
            'theme' => $validated['theme']
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'sidebar_collapsed' => 'boolean',
            'notifications_enabled' => 'boolean',
            'auto_refresh' => 'boolean',
            'items_per_page' => 'integer|min:10|max:100'
        ]);

        $user = Auth::user();

        // Store preferences in session for now
        // In a real application, you might want to store these in a user_preferences table
        foreach ($validated as $key => $value) {
            Session::put("preference_{$key}", $value);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Preferences updated successfully.');
    }

    /**
     * Reset settings to default
     */
    public function reset()
    {
        // Clear theme and preferences from session
        Session::forget(['theme', 'preference_sidebar_collapsed', 'preference_notifications_enabled', 
                        'preference_auto_refresh', 'preference_items_per_page']);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings reset to default.');
    }
}
