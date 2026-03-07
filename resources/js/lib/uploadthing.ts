import type { UploadThingFile } from '@/types/uploadthing';

/**
 * Upload file directly to UploadThing
 * This uses UploadThing's REST API for direct file uploads
 */
export async function uploadToUploadThing(
    file: File
): Promise<UploadThingFile> {
    const token = import.meta.env.VITE_UPLOADTHING_TOKEN;

    if (!token) {
        throw new Error('VITE_UPLOADTHING_TOKEN is not configured');
    }

    try {
        // Step 1: Request presigned URL from UploadThing
        const presignedUrlResponse = await fetch('https://api.uploadthing.com/v6/uploadFiles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Uploadthing-Api-Key': token,
            },
            body: JSON.stringify({
                files: [{
                    name: file.name,
                    size: file.size,
                    type: file.type,
                }],
                acl: 'public-read',
            }),
        });

        if (!presignedUrlResponse.ok) {
            const error = await presignedUrlResponse.text();
            throw new Error(`Failed to get upload URL: ${error}`);
        }

        const presignedData = await presignedUrlResponse.json();
        const uploadData = presignedData.data[0];

        // Step 2: Upload file to the presigned URL
        const formData = new FormData();

        // Add all required fields from UploadThing
        Object.entries(uploadData.fields || {}).forEach(([key, value]) => {
            formData.append(key, value as string);
        });

        formData.append('file', file);

        const uploadResponse = await fetch(uploadData.url, {
            method: 'POST',
            body: formData,
        });

        if (!uploadResponse.ok) {
            throw new Error('Failed to upload file');
        }

        // Step 3: Return the file URL
        return {
            url: uploadData.fileUrl || `${uploadData.url}/${uploadData.key}`,
            key: uploadData.key,
            name: file.name,
            size: file.size,
        };
    } catch (error) {
        console.error('UploadThing error:', error);
        throw error;
    }
}
