<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $groups = Setting::orderBy('group')->orderBy('id')->get()->groupBy('group');
        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings'        => 'required|array',
            'settings.*.key'  => 'required|string|exists:settings,key',
            'settings.*.value'=> 'nullable|string|max:1000',
        ]);

        foreach ($data['settings'] as $item) {
            $key   = $item['key'];
            $value = $item['value'] ?? '';

            // The earn rate is displayed in the UI as a percentage (e.g. 1 for 1%)
            // but stored as a decimal fraction (e.g. 0.01).
            if ($key === 'loyalty.default_rate') {
                $value = (string) round((float) $value / 100, 6);
            }

            Setting::where('key', $key)->update(['value' => $value]);
        }

        Setting::clearCache();

        return back()->with('success', 'Settings saved successfully.');
    }
}
