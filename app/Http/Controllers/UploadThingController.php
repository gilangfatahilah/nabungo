<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadThingController extends Controller
{
    /**
     * Handle UploadThing callback after successful upload.
     * This endpoint receives the file information from UploadThing
     * and can be used for additional processing if needed.
     */
    public function callback(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'fileUrl' => 'required|url',
            'fileKey' => 'required|string',
            'fileName' => 'required|string',
            'fileSize' => 'required|integer',
        ]);

        // You can add additional processing here
        // For example, logging, virus scanning, etc.

        return response()->json([
            'success' => true,
            'message' => 'File upload callback processed successfully',
        ]);
    }

    /**
     * Get UploadThing configuration.
     * This endpoint provides the necessary configuration for the client.
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'token' => config('services.uploadthing.token'),
            'maxFileSize' => '2MB',
            'allowedFileTypes' => ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'],
        ]);
    }
}
