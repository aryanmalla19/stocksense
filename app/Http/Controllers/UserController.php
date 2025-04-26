<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        $user = auth()->user()->load('setting');

        return new UserResource($user);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'name' => [
                'nullable',
                'string',
                'max:50',
                'min:3',
                'regex:/^[A-Za-z\s]+$/',
            ],
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'phone_number' => [
                'nullable',
                'string',
                'regex:/^\+?[0-9]{10,15}$/',
            ],
            'bio' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && \Storage::disk('public')->exists($user->profile_image)) {
                \Storage::disk('public')->delete($user->profile_image);
            }
            // Store new image
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Successfully updated profile',
        ]);
    }
}
