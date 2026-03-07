import { ref } from 'vue';
import { uploadToUploadThing } from '@/lib/uploadthing';
import type { UploadThingFile } from '@/types/uploadthing';

export type UploadResult = UploadThingFile;

export interface UseUploadThingOptions {
    maxFileSize?: number; // in MB
    allowedFileTypes?: string[];
    onSuccess?: (result: UploadResult) => void;
    onError?: (error: Error) => void;
}

export function useUploadThing(options: UseUploadThingOptions = {}) {
    const {
        maxFileSize = 2,
        allowedFileTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'],
        onSuccess,
        onError,
    } = options;

    const isUploading = ref(false);
    const uploadError = ref<string | null>(null);
    const uploadProgress = ref(0);

    const validateFile = (file: File): boolean => {
        uploadError.value = null;

        // Validate file type
        if (!allowedFileTypes.includes(file.type)) {
            const allowedExtensions = allowedFileTypes
                .map(type => type.split('/')[1].toUpperCase())
                .join(', ');
            uploadError.value = `File type must be (${allowedExtensions})`;
            return false;
        }

        // Validate file size
        const maxSizeInBytes = maxFileSize * 1024 * 1024;
        if (file.size > maxSizeInBytes) {
            uploadError.value = `Max file size is ${maxFileSize}MB`;
            return false;
        }

        return true;
    };

    const upload = async (file: File): Promise<UploadResult | null> => {
        if (!validateFile(file)) {
            return null;
        }

        isUploading.value = true;
        uploadError.value = null;
        uploadProgress.value = 0;

        try {
            // Upload file directly to UploadThing
            const result = await uploadToUploadThing(file);

            if (onSuccess) {
                onSuccess(result);
            }

            return result;
        } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Failed to upload file';
            uploadError.value = errorMessage;

            if (onError) {
                onError(error instanceof Error ? error : new Error(errorMessage));
            }

            console.error('Upload error:', error);
            return null;
        } finally {
            isUploading.value = false;
            uploadProgress.value = 0;
        }
    };

    const reset = () => {
        isUploading.value = false;
        uploadError.value = null;
        uploadProgress.value = 0;
    };

    return {
        isUploading,
        uploadError,
        uploadProgress,
        upload,
        validateFile,
        reset,
    };
}
