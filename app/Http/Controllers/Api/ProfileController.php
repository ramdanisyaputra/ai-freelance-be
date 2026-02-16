<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    use \App\Traits\ResponseTrait;

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'avatar' => ['nullable', 'image', 'max:1024'], // 1MB Max
            ]);

            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            $user->update($validated);

            DB::commit();

            return $this->successResponse([
                'message' => 'Profile updated successfully.',
                'user' => $user,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile Update Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to update profile.');
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', 'min:8'],
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            DB::commit();

            return $this->successResponseMessage('Password updated successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password Update Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to update password.');
        }
    }
}
