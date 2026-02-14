<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $admin = $this->admin($request);

        return view('admin.profile.edit', compact('admin'));
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $this->admin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
        ]);

        $admin->update($validated);

        return redirect()->route('admin.profile.edit')->with('success', 'Profile updated.');
    }

    public function showChangePassword(Request $request): View
    {
        return view('admin.profile.change-password');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $admin = $this->admin($request);

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $admin->update(['password' => $request->password]);

        return redirect()->route('admin.profile.edit')->with('success', 'Password changed.');
    }

    protected function admin(Request $request): Admin
    {
        $id = session('admin_id');
        $admin = Admin::find($id);
        if (! $admin) {
            abort(403);
        }

        return $admin;
    }
}
