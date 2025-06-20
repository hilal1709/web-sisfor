# Google OAuth Setup untuk Academy Bridge

## Langkah-langkah Setup Google OAuth

### 1. Buat Project di Google Cloud Console

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang sudah ada
3. Aktifkan Google+ API dan Google OAuth2 API

### 2. Konfigurasi OAuth Consent Screen

1. Di Google Cloud Console, pergi ke **APIs & Services** > **OAuth consent screen**
2. Pilih **External** untuk user type
3. Isi informasi aplikasi:
   - **App name**: Academy Bridge
   - **User support email**: email Anda
   - **Developer contact information**: email Anda
4. Tambahkan scope yang diperlukan:
   - `../auth/userinfo.email`
   - `../auth/userinfo.profile`
   - `openid`

### 3. Buat OAuth 2.0 Client ID

1. Pergi ke **APIs & Services** > **Credentials**
2. Klik **Create Credentials** > **OAuth client ID**
3. Pilih **Web application**
4. Isi informasi:
   - **Name**: Academy Bridge Web Client
   - **Authorized JavaScript origins**: 
     - `http://localhost:8000` (untuk development)
     - `https://yourdomain.com` (untuk production)
   - **Authorized redirect URIs**:
     - `http://localhost:8000/auth/google/callback` (untuk development)
     - `https://yourdomain.com/auth/google/callback` (untuk production)

### 4. Konfigurasi Environment Variables

Tambahkan ke file `.env`:

```env
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### 5. Testing

1. Jalankan aplikasi: `php artisan serve`
2. Buka halaman login: `http://localhost:8000/login`
3. Klik tombol "Masuk dengan Google"
4. Login dengan akun Google Anda
5. Anda akan diarahkan ke dashboard setelah berhasil login

## Fitur Google OAuth yang Diimplementasikan

- ✅ Login dengan Google
- ✅ Registrasi otomatis untuk user baru
- ✅ Sinkronisasi data profil (nama, email, avatar)
- ✅ Integrasi dengan sistem autentikasi Laravel
- ✅ Handling error dan redirect yang aman

## Troubleshooting

### Error: "redirect_uri_mismatch"
- Pastikan redirect URI di Google Console sama persis dengan yang di konfigurasi
- Periksa protokol (http vs https)
- Pastikan tidak ada trailing slash

### Error: "invalid_client"
- Periksa GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di file .env
- Pastikan credentials sudah benar dari Google Console

### User tidak bisa login
- Pastikan OAuth consent screen sudah dikonfigurasi
- Periksa scope yang diminta
- Pastikan user email sudah diverifikasi di Google

## Security Notes

- Jangan commit file .env ke repository
- Gunakan HTTPS di production
- Validasi data yang diterima dari Google
- Implementasikan rate limiting untuk endpoint OAuth