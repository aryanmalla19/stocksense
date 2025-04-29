<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User",
 *     description="Endpoints related to user profile management"
 * )
 */

 /**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User details",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-01-01T12:00:00Z"),
 *     @OA\Property(property="profile_image", type="string", format="url", nullable=true, example="https://example.com/storage/profile_images/avatar.jpg"),
 *     @OA\Property(property="phone_number", type="string", nullable=true, example="+12345678901"),
 *     @OA\Property(property="bio", type="string", nullable=true, example="Passionate stock trader."),
 *     @OA\Property(property="role", type="string", example="user"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 * )
 */
class UserController extends Controller
{
    /**
     * Get authenticated user's profile.
     *
     * @OA\Get(
     *     path="/api/v1/user",
     *     summary="Get authenticated user profile",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user()->load('setting');

        return new UserResource($user);
    }

     /**
     * Update authenticated user's profile.
     *
     * @OA\Post(
     *     path="/api/v1/user",
     *     summary="Update authenticated user profile",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","phone_number","bio"},
     *                 @OA\Property(property="profile_image", type="file", description="Profile image (jpg, jpeg, png, max 2MB)"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="phone_number", type="string", example="+12345678901"),
     *                 @OA\Property(property="bio", type="string", example="Love investing in tech stocks."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully updated profile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
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
