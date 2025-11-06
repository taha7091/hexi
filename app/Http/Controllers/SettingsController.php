<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get current theme from session or default to light
        $currentTheme = Session::get('theme', 'light');
        
        // Get user preferences
        $userSettings = [
            'theme' => $user->theme_preference ?? $currentTheme,
            'notifications_enabled' => $user->notifications_enabled ?? true,
            'email_notifications' => $user->email_notifications ?? true,
            'sidebar_collapsed' => Session::get('sidebar_collapsed', false),
            'language' => $user->language_preference ?? Session::get('language', 'en'),
            'timezone' => $user->timezone ?? 'UTC',
            'date_format' => $user->date_format ?? 'Y-m-d',
            'time_format' => $user->time_format ?? 'H:i:s',
        ];
        
        return view('admin.settings.index', compact('userSettings'));
    }

    /**
     * Update theme setting
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark'
        ]);

        // Store theme in session
        Session::put('theme', $request->theme);
        
        // Also store in user preferences if user wants to persist
        if ($request->persist) {
            $user = Auth::user();
            $user->theme_preference = $request->theme;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully',
            'theme' => $request->theme
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'notifications_enabled' => 'boolean',
            'email_notifications' => 'boolean'
        ]);

        $user = Auth::user();
        $user->notifications_enabled = $request->notifications_enabled ?? false;
        $user->email_notifications = $request->email_notifications ?? false;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully'
        ]);
    }

    /**
     * Update display settings
     */
    public function updateDisplay(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,es,fr,de,ar',
            'timezone' => 'required|string',
            'date_format' => 'required|in:Y-m-d,d/m/Y,m/d/Y,d-m-Y',
            'time_format' => 'required|in:H:i:s,h:i:s A,H:i'
        ]);

        // Store in session
        Session::put('language', $request->language);
        
        // Store in user preferences
        $user = Auth::user();
        $user->timezone = $request->timezone;
        $user->date_format = $request->date_format;
        $user->time_format = $request->time_format;
        $user->language_preference = $request->language;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Display settings updated successfully'
        ]);
    }

    /**
     * Update sidebar preference
     */
    public function updateSidebar(Request $request)
    {
        $request->validate([
            'collapsed' => 'boolean'
        ]);

        Session::put('sidebar_collapsed', $request->collapsed);

        return response()->json([
            'success' => true,
            'message' => 'Sidebar preference updated'
        ]);
    }

    /**
     * Reset all settings to default
     */
    public function resetSettings(Request $request)
    {
        // Clear session settings
        Session::forget(['theme', 'language', 'sidebar_collapsed']);
        
        // Reset user preferences
        $user = Auth::user();
        $user->theme_preference = null;
        $user->notifications_enabled = true;
        $user->email_notifications = true;
        $user->timezone = 'UTC';
        $user->date_format = 'Y-m-d';
        $user->time_format = 'H:i:s';
        $user->language_preference = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'All settings have been reset to default'
        ]);
    }

    /**
     * Export user settings
     */
    public function exportSettings()
    {
        $user = Auth::user();
        
        $settings = [
            'user_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company_id' => $user->company_id,
            ],
            'preferences' => [
                'theme' => Session::get('theme', 'light'),
                'language' => Session::get('language', 'en'),
                'timezone' => $user->timezone ?? 'UTC',
                'date_format' => $user->date_format ?? 'Y-m-d',
                'time_format' => $user->time_format ?? 'H:i:s',
                'notifications_enabled' => $user->notifications_enabled ?? true,
                'email_notifications' => $user->email_notifications ?? true,
            ],
            'exported_at' => now()->toISOString()
        ];

        $filename = 'user_settings_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($settings)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Get system information
     */
    public function getSystemInfo()
    {
        $systemInfo = [
            'app_name' => config('app.name', 'ERP System'),
            'app_version' => '1.0.0',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'database_connection' => config('database.default'),
        ];

        return response()->json([
            'success' => true,
            'data' => $systemInfo
        ]);
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request)
    {
        try {
            // Clear different types of cache
            Cache::flush();
            
            // Clear config cache
            \Artisan::call('config:clear');
            
            // Clear route cache
            \Artisan::call('route:clear');
            
            // Clear view cache
            \Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => 'Application cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile settings
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;

        // Update password if provided
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || 
                !\Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }
            
            $user->password = \Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    }
}
