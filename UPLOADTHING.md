# UploadThing Setup

## Overview

Aplikasi ini menggunakan UploadThing untuk upload avatar. File di-upload **langsung ke server UploadThing**, bukan melalui backend Laravel.

## Setup

### 1. Dapatkan API Key

1. Daftar di https://uploadthing.com
2. Buat aplikasi baru
3. Dapatkan API Key dari dashboard

### 2. Tambahkan ke .env

```env
VITE_UPLOADTHING_TOKEN=your_uploadthing_token_here
```

**Penting**: Gunakan prefix `VITE_` agar dapat diakses di frontend.

### 3. Restart Dev Server

```bash
npm run dev
```

## Cara Kerja

```
User → Select File → Frontend validates → Upload ke UploadThing → Dapat URL → Simpan URL ke Laravel DB
```

### Flow Detail:

1. **User pilih file** di Profile page
2. **Frontend validasi** (type, size)
3. **Upload langsung ke UploadThing** via API:
   - Request presigned URL dari UploadThing
   - Upload file ke presigned URL
   - UploadThing return public URL
4. **Frontend kirim URL ke Laravel** via POST `/settings/profile/avatar`
5. **Laravel simpan URL** di kolom `users.avatar`

## File Structure

```
resources/js/
├── lib/
│   └── uploadthing.ts              # Direct upload ke UploadThing API
├── composables/
│   └── useUploadThing.ts           # Reusable upload logic dengan validasi
├── types/
│   └── uploadthing.ts              # TypeScript types
└── pages/settings/
    └── Profile.vue                  # Profile page dengan avatar upload

app/Http/Controllers/Settings/
└── ProfileController.php            # Simpan avatar_url ke database
```

## Usage

### Di Component Vue:

```vue
<script setup>
import { useUploadThing } from '@/composables/useUploadThing';

const { isUploading, uploadError, upload } = useUploadThing({
    maxFileSize: 2, // MB
    allowedFileTypes: ['image/jpeg', 'image/png'],
    onSuccess: (result) => {
        console.log('URL:', result.url);
        // Simpan ke backend
    },
    onError: (error) => {
        console.error(error);
    },
});

const handleUpload = async (file: File) => {
    await upload(file);
};
</script>
```

### Direct Upload:

```typescript
import { uploadToUploadThing } from '@/lib/uploadthing';

const file = document.querySelector('input[type="file"]').files[0];
const result = await uploadToUploadThing(file);
console.log('File URL:', result.url);
```

## API Endpoints (Laravel)

### Simpan Avatar URL
```
POST /settings/profile/avatar
Body: { avatar_url: "https://utfs.io/f/..." }
```

### Hapus Avatar
```
DELETE /settings/profile/avatar
```

## Konfigurasi UploadThing Dashboard

1. Login ke https://uploadthing.com
2. Pilih aplikasi Anda
3. Configure:
   - **Max File Size**: 2MB
   - **Allowed Types**: image/jpeg, image/png, image/jpg, image/gif, image/webp
   - **CORS**: Add your domain (untuk production)

## Troubleshooting

### Error: VITE_UPLOADTHING_TOKEN is not configured

**Solusi**: 
- Pastikan `VITE_UPLOADTHING_TOKEN` ada di `.env`
- Restart dev server: `npm run dev`

### Error 403: Forbidden

**Solusi**:
- Cek API key valid di UploadThing dashboard
- Pastikan aplikasi masih aktif

### CORS Error (production)

**Solusi**:
- Tambahkan domain production ke CORS settings di UploadThing dashboard
- Contoh: `https://yourdomain.com`

### Upload Gagal

**Debug**:
```javascript
// Cek di browser console
console.log('Token:', import.meta.env.VITE_UPLOADTHING_TOKEN);
```

## Security

✅ **Aman**:
- Upload langsung ke UploadThing (tidak through backend)
- Token hanya untuk upload, bukan secret key
- File size & type validation di client & server
- Public URL yang di-return aman untuk disimpan di database

❌ **Jangan**:
- Commit token ke git (sudah ada di .gitignore)
- Share token secara publik
- Gunakan token di server-side rendering

## Migration dari Local Storage

Jika sebelumnya pakai local storage (`storage/app/public/avatars/`):

1. Avatar lama: `/storage/avatars/filename.jpg`
2. Avatar baru: `https://utfs.io/f/abc123.jpg`
3. Kolom `avatar` bisa menyimpan keduanya (string)

## Cost

- **Free Tier**: 2GB storage, 2GB bandwidth/month
- Monitor usage di: https://uploadthing.com/dashboard

## Resources

- Docs: https://docs.uploadthing.com
- API Reference: https://docs.uploadthing.com/api-reference
- Support: https://discord.gg/uploadthing