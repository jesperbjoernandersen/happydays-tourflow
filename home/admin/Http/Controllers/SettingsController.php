<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display general settings.
     */
    public function general()
    {
        return view('settings.general');
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:3',
            'check_in_time' => 'nullable|string',
            'check_out_time' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Save settings to database or .env
        // For now, just flash success message
        return redirect()->route('settings.general')
            ->with('success', 'General settings updated successfully.');
    }

    /**
     * Display users settings.
     */
    public function users()
    {
        $users = User::paginate(20);
        return view('settings.users', compact('users'));
    }

    /**
     * Store a new user.
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('settings.users')
            ->with('success', 'User created successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleUser(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return redirect()->route('settings.users')
            ->with('success', 'User status updated.');
    }

    /**
     * Display integrations settings.
     */
    public function integrations()
    {
        return view('settings.integrations');
    }

    /**
     * Update integration settings.
     */
    public function updateIntegration(Request $request, $integration)
    {
        // Store integration credentials securely
        return redirect()->route('settings.integrations')
            ->with('success', "{$integration} integration updated.");
    }
}
