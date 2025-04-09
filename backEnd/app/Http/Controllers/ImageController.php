<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{
    public function uploadAndResize(Request $request)
    {
        // Validate the request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,bmp,tiff|max:10000',
            'width' => 'required|integer|min:10|max:6000',
            'height' => 'required|integer|min:10|max:6000',
        ]);

        // Get the uploaded file
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();

        // Resize the image
        $resizedImage = Image::make($file)->resize($request->width, $request->height);

        // Store the image
        $path = "public/resized/{$filename}";
        Storage::put($path, (string) $resizedImage->encode());

        // Generate the URL of the resized image
        $fileUrl = Storage::url("resized/{$filename}");

        // Return the URL of the resized image
        return response()->json([
            'message' => 'Image resized successfully',
            'url' => $fileUrl,
            'filename' => $filename // Send the file name so frontend can use it
        ], 200);
    }

    public function downloadResizedImage($filename, Request $request)
    {
        $path = storage_path("app/public/resized/$filename");

        if (!file_exists($path)) {
            abort(404);
        }

        $originalName = $request->query('name', $filename); // fallback to filename if not provided

        return response()->download($path, $originalName);
    }
}
