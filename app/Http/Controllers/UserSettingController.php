<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserSettingResource;
use Illuminate\Http\Request;
use App\Models\UserSetting;

class UserSettingController extends Controller
{
    /**
     * Display a listing of the resource (Admin Only).
     */
    public function index()
    {
//        if (!auth()->user()->is_admin) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        return UserSettingResource::collection(UserSetting::all());
    }

    /**
     * Store a newly created user setting.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'notification_enabled' => 'nullable|boolean',
            'mode' => 'required|in:light,dark',
        ]);

        $user = auth()->user();
        if ($user->id != $data['user_id']) {
            return response()->json([
                'message' => 'You cannot create settings for another user'
            ], 403);
        }

        $setting = $user->setting()->updateOrCreate(['user_id' => $user->id], $data);

        return new UserSettingResource($setting);
    }

    /**
     * Display the specified resource (Fetch user settings).
     */
    public function show()
    {
        $user = auth()->user();
        $setting = $user->setting;

        if (!$setting) {
            return response()->json([
                'message' => 'No settings found for this user'
            ], 404);
        }

        return new UserSettingResource($setting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'notification_enabled' => 'nullable|boolean',
            'mode' => 'required|in:light,dark',
        ]);

        $user = auth()->user();
        $setting = $user->setting;

        if (!$setting) {
            return response()->json([
                'message' => 'No settings found for this user'
            ], 404);
        }

        $setting->update($data);

        return new UserSettingResource($setting);
    }

    /**
     * Remove the specified resource from storage (Delete settings).
     */
    public function destroy()
    {
        $user = auth()->user();
        $setting = $user->setting;

        if (!$setting) {
            return response()->json([
                'message' => 'No settings found for this user'
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'message' => 'Successfully deleted user settings'
        ]);
    }
}
