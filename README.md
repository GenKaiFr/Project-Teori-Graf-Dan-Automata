# Aplikasi Jadwal Rapat

Aplikasi web untuk mengelola jadwal rapat berbasis Laravel dengan fitur export PDF dan Excel.

## Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- XAMPP/WAMP/LAMP (untuk development lokal)

## Instalasi

### 1. Clone atau Download Project
```bash
git clone <repository-url>
cd jadwal_rapat
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Konfigurasi Environment
```bash
# Copy file environment
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=sqlite
# Atau untuk MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=jadwal_rapat
# DB_USERNAME=root
# DB_PASSWORD=
```

### 5. Migrasi Database
```bash
php artisan migrate
```

### 6. Build Assets
```bash
npm run build
```

## Menjalankan Aplikasi

### Development Mode
```bash
# Jalankan server Laravel
php artisan serve

# Atau gunakan script composer untuk development lengkap
composer run dev
```

### Production Mode
```bash
# Build assets untuk production
npm run build

# Jalankan server
php artisan serve --env=production
```

## Fitur Utama

- ✅ Manajemen jadwal rapat
- ✅ Export ke PDF menggunakan DomPDF
- ✅ Export ke Excel menggunakan Maatwebsite Excel
- ✅ Interface yang responsif
- ✅ Database SQLite (default) atau MySQL

## Struktur Project

```
jadwal_rapat/
├── app/                 # Aplikasi Laravel
├── config/             # File konfigurasi
├── database/           # Migrasi dan seeder
├── public/             # File publik (CSS, JS, gambar)
├── resources/          # Views, CSS, JS source
├── routes/             # Route definitions
└── storage/            # File storage dan logs
```

## Penggunaan

1. **Akses Aplikasi**: Buka browser dan kunjungi `http://localhost:8000`
2. **Tambah Rapat**: Gunakan form untuk menambah jadwal rapat baru
3. **Lihat Jadwal**: Daftar rapat akan ditampilkan di halaman utama
4. **Export Data**: 
   - Klik tombol "Export PDF" untuk download dalam format PDF
   - Klik tombol "Export Excel" untuk download dalam format Excel

## Commands Berguna

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Jalankan migrasi fresh
php artisan migrate:fresh

# Jalankan seeder (jika ada)
php artisan db:seed

# Jalankan tests
php artisan test
```

## Troubleshooting

### Error Permission
```bash
# Windows (XAMPP)
# Pastikan folder storage dan bootstrap/cache dapat ditulis

# Linux/Mac
chmod -R 775 storage bootstrap/cache
```

### Error Composer
```bash
# Update composer
composer update

# Install ulang dependencies
rm -rf vendor
composer install
```

### Error NPM
```bash
# Clear npm cache
npm cache clean --force

# Install ulang node modules
rm -rf node_modules
npm install
```

## Kontribusi

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -am 'Tambah fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## Lisensi

Project ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detail lengkap.

## Support

Jika mengalami masalah atau memiliki pertanyaan, silakan buat issue di repository ini.