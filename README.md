# Perpustakaan API

REST API berbasis Laravel 12 untuk registrasi dan login token Sanctum, CRUD buku, pencarian, paginasi, serta dokumentasi interaktif OpenAPI melalui L5-Swagger.

## Persyaratan

- PHP 8.2 atau lebih baru dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, dan `xml`
- Composer 2

## Menjalankan aplikasi

```bash
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan l5-swagger:generate
php artisan serve
```

Server berjalan di `http://localhost:8000`. Dokumentasi Swagger UI tersedia di `http://localhost:8000/api/documentation`; spesifikasi JSON tersedia di `http://localhost:8000/docs`.

Seeder membuat akun demo `admin@example.com` dengan kata sandi `password`, serta 20 buku. Gunakan kredensial demo hanya untuk pengembangan; ganti atau hapus sebelum deployment.

## Endpoint

| Method | Endpoint | Keterangan | Autentikasi |
|---|---|---|---|
| POST | `/api/register` | Daftar akun dan terbitkan token | Tidak |
| POST | `/api/login` | Login dan terbitkan token (maks. 6 percobaan/menit) | Tidak |
| GET | `/api/me` | Profil pengguna aktif | Bearer token |
| POST | `/api/logout` | Hapus token aktif | Bearer token |
| GET | `/api/books` | Daftar buku, paginasi, pencarian `search` | Bearer token |
| POST | `/api/books` | Tambah buku | Bearer token |
| GET | `/api/books/{book}` | Detail buku | Bearer token |
| PUT/PATCH | `/api/books/{book}` | Ubah buku / sebagian data | Bearer token |
| DELETE | `/api/books/{book}` | Hapus buku | Bearer token |

Semua request API sebaiknya menyertakan `Accept: application/json`. Gunakan token login pada header `Authorization: Bearer <token>` untuk endpoint terlindungi.

Contoh:

```bash
curl -X POST http://localhost:8000/api/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password"}'

curl 'http://localhost:8000/api/books?search=pelangi&per_page=10' \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer <token>'
```

## Pengujian

```bash
php artisan test
```

Tes menggunakan SQLite in-memory dan tidak mengubah database pengembangan.

## Produksi

Set `L5_SWAGGER_GENERATE_ALWAYS=false`, atur `L5_SWAGGER_CONST_HOST` ke URL API sebenarnya, lalu jalankan `php artisan l5-swagger:generate` saat deployment. Lindungi atau nonaktifkan halaman dokumentasi sesuai kebijakan aplikasi. Jangan gunakan akun demo maupun `migrate:fresh` pada database produksi.
