<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\ResponseTrait;

class ImageUploadController extends Controller
{
    use ResponseTrait;

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $image = $request->file('image');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $url = Storage::url($path);

            return $this->successResponse([
                'url' => url($url),
                'path' => $path,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload image: ' . $e->getMessage(), 500);
        }
    }
}
