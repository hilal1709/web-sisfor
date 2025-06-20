# Panduan Mengatasi Masalah Google OAuth "Akses Diblokir"

## Masalah Umum dan Solusi

### 1. Domain localhost Diblokir
Google OAuth tidak mengizinkan `localhost` untuk aplikasi production. Solusi:

#### Opsi A: Gunakan ngrok (Recommended untuk Development)
```bash
# Install ngrok dari https://ngrok.com/
# Jalankan Laravel server
php artisan serve

# Di terminal baru, jalankan ngrok
ngrok http 8000
```

Ngrok akan memberikan URL seperti: `https://abc123.ngrok.io`

Update file `.env`:
```env
APP_URL=https://abc123.ngrok.io
GOOGLE_REDIRECT_URI=https://abc123.ngrok.io/auth/google/callback
```

#### Opsi B: Gunakan Domain Lokal
Edit file `C:\Windows\System32\drivers\etc\hosts` (sebagai Administrator):
```
127.0.0.1 academy-bridge.local
```

Update file `.env`:
```env
APP_URL=http://academy-bridge.local:8000
GOOGLE_REDIRECT_URI=http://academy-bridge.local:8000/auth/google/callback
```

### 2. Konfigurasi Google Cloud Console

1. **Buka Google Cloud Console**
   - Pergi ke https://console.cloud.google.com/
   - Pilih atau buat project

2. **Aktifkan Google+ API**
   - Pergi ke "APIs & Services" > "Library"
   - Cari "Google+ API" dan aktifkan

3. **Buat OAuth 2.0 Credentials**
   - Pergi ke "APIs & Services" > "Credentials"
   - Klik "Create Credentials" > "OAuth 2.0 Client IDs"
   - Pilih "Web application"

4. **Konfigurasi Authorized Redirect URIs**
   Tambahkan URI berikut:
   ```
   http://localhost:8000/auth/google/callback
   http://127.0.0.1:8000/auth/google/callback
   http://academy-bridge.local:8000/auth/google/callback
   https://your-ngrok-url.ngrok.io/auth/google/callback
   ```

5. **Konfigurasi Authorized JavaScript Origins**
   Tambahkan origin berikut:
   ```
   http://localhost:8000
   http://127.0.0.1:8000
   http://academy-bridge.local:8000
   https://your-ngrok-url.ngrok.io
   ```

### 3. Update Kredensial di .env

Setelah membuat OAuth credentials, update file `.env`:
```env
GOOGLE_CLIENT_ID=your-new-client-id
GOOGLE_CLIENT_SECRET=your-new-client-secret
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback
```

### 4. Clear Cache Laravel

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 5. Test Konfigurasi

1. Akses halaman debug: `http://your-domain/auth/google/debug`
2. Periksa apakah semua konfigurasi sudah benar
3. Test login Google

## Troubleshooting Tambahan

### Error: "redirect_uri_mismatch"
- Pastikan redirect URI di Google Console sama persis dengan yang di aplikasi
- Periksa protokol (http vs https)
- Periksa port (8000)

### Error: "access_blocked"
- Pastikan aplikasi tidak dalam mode "Testing" di Google Console
- Tambahkan email Anda sebagai test user jika masih dalam mode testing
- Atau ubah ke mode "Production"

### Error: "invalid_client"
- Periksa Client ID dan Client Secret
- Pastikan tidak ada spasi atau karakter tersembunyi

## Verifikasi Domain untuk Production

Untuk aplikasi production, Anda perlu:
1. Verifikasi domain di Google Search Console
2. Tambahkan domain terverifikasi ke OAuth consent screen
3. Submit aplikasi untuk review jika diperlukan

## Contoh Konfigurasi Lengkap

File `.env` yang benar:
```env
APP_URL=https://your-domain.com
GOOGLE_CLIENT_ID=123456789-abcdefghijklmnop.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-your-secret-key
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback
```

## Testing

Setelah konfigurasi:
1. Restart Laravel server: `php artisan serve`
2. Akses: `http://your-domain/login`
3. Klik "Masuk dengan Google"
4. Harus redirect ke Google tanpa error

Jika masih ada masalah, periksa log Laravel di `storage/logs/laravel.log`