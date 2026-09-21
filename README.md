# API SonaraRent

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel\&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php\&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**SonaraRent API** adalah RESTful API untuk sistem penyewaan alat musik. API ini menyediakan layanan untuk mengelola pengguna, instrumen musik, kategori, keranjang, penyewaan, pembayaran, denda, ulasan, notifikasi, laporan, serta aktivitas sistem.

Backend dikembangkan menggunakan **Laravel 12** dengan autentikasi berbasis **Laravel Sanctum** dan manajemen role menggunakan **Spatie Laravel Permission**.

## Fitur Utama

* 🔐 **Authentication & Authorization**

  * Register dan login
  * Verifikasi OTP
  * Lupa dan reset password
  * Change password
  * Token authentication menggunakan Laravel Sanctum
  * Role-based access control

* 🎸 **Instrument Management**

  * Mengelola data instrumen musik
  * Kategori instrumen
  * Kondisi instrumen
  * Pengecekan ketersediaan instrumen

* 🛒 **Cart & Rental**

  * Mengelola keranjang penyewaan
  * Mengecek ketersediaan instrumen
  * Membuat dan mengelola transaksi rental
  * Melihat riwayat rental pengguna
  * Perubahan status rental
  * Pembayaran rental
  * Upload jaminan rental
  * Simulasi pembayaran

* ⭐ **Review**

  * Membuat dan mengelola ulasan
  * Melihat ulasan instrumen

* ⚠️ **Penalty**

  * Pengelolaan denda
  * Data denda berdasarkan transaksi rental

* 🔔 **Notification**

  * Melihat notifikasi
  * Menandai notifikasi sebagai telah dibaca
  * Menandai seluruh notifikasi sebagai telah dibaca
  * Menghapus notifikasi

* 📊 **Dashboard & Reporting**

  * Dashboard Admin
  * Dashboard Staff
  * Laporan rental
  * Laporan instrumen populer
  * Statistik pendapatan
  * Tren penjualan
  * Laporan denda

* 📥 **Export**

  * Export data rental
  * Export instrumen
  * Export pengguna
  * Export review
  * Export revenue
  * Export instrumen populer
  * Export sales trend
  * Export penalty

* 🧾 **Receipt**

  * Generate dan mencetak receipt transaksi rental

## Role & Access

SonaraRent menggunakan tiga role utama:

| Role       | Deskripsi                                                                                  |
| ---------- | ------------------------------------------------------------------------------------------ |
| `admin`    | Mengelola pengguna, kategori, sistem, laporan, export, dan konfigurasi                     |
| `staff`    | Mengelola operasional rental, pembayaran, kondisi instrumen, dan laporan                   |
| `customer` | Melihat instrumen, melakukan rental, pembayaran, review, dan mengelola data rental pribadi |

Authorization diterapkan menggunakan middleware role sehingga endpoint tertentu hanya dapat diakses oleh role yang sesuai.

## API Modules

Endpoint API dikelompokkan berdasarkan fungsi dan role pengguna.

### Authentication

```text
POST /api/login
POST /api/register
POST /api/auth/verify-otp
POST /api/auth/forgot-password
POST /api/auth/reset-password
POST /api/logout
GET  /api/me
```

### Instruments

```text
GET    /api/instrument
GET    /api/instrument/{id}
POST   /api/instrument
PUT    /api/instrument/{id}
DELETE /api/instrument/{id}

GET    /api/instrument/no-paginate
GET    /api/instrument-condition/no-paginate
```

### Categories

```text
GET    /api/category
POST   /api/category
GET    /api/category/{id}
PUT    /api/category/{id}
DELETE /api/category/{id}
```

### Rental

```text
GET    /api/rental
POST   /api/rental
GET    /api/rental/{id}
PUT    /api/rental/{id}
DELETE /api/rental/{id}

GET    /api/my/rental
PUT    /api/rental/{id}/status
PUT    /api/rental/{id}/pay
POST   /api/rental/{id}/guarantee
POST   /api/rental/{id}/simulate-payment
```

### Cart

```text
GET /api/cart/availability
```

### Review

```text
GET    /api/review
POST   /api/review
GET    /api/review/{id}
PUT    /api/review/{id}
DELETE /api/review/{id}

GET    /api/review/no-paginate
```

### Penalty

```text
GET    /api/penalty
POST   /api/penalty
GET    /api/penalty/{id}
PUT    /api/penalty/{id}
DELETE /api/penalty/{id}

GET    /api/penalty/no-paginate
```

### Notifications

```text
GET    /api/notifications
PUT    /api/notifications/{id}/read
PUT    /api/notifications/read-all
DELETE /api/notifications/{id}
```

### Dashboard

```text
GET /api/dashboard/admin
GET /api/dashboard/staff
```

### Reports & Export

```text
GET /api/report
GET /api/report/popular

GET /api/export/rentals
GET /api/export/instruments
GET /api/export/users
GET /api/export/reviews
GET /api/export/revenue
GET /api/export/popular-instruments
GET /api/export/sales-trend
GET /api/export/penalties
```

## Technology Stack

| Technology            | Usage                        |
| --------------------- | ---------------------------- |
| **Laravel 12**        | Backend framework            |
| **PHP 8.2+**          | Programming language         |
| **Laravel Sanctum**   | API authentication           |
| **Spatie Permission** | Role & permission management |
| **Maatwebsite Excel** | Data export                  |
| **DOMPDF**            | PDF generation               |
| **MySQL / MariaDB**   | Database                     |
| **Pest PHP**          | Automated testing            |
| **Vite**              | Frontend asset bundling      |

Dependency utama project dapat dilihat pada `composer.json`, termasuk Laravel 12, Sanctum, Spatie Permission, Maatwebsite Excel, dan DOMPDF.

## Architecture

Project menggunakan pendekatan pemisahan tanggung jawab agar proses bisnis tidak seluruhnya berada di dalam Controller.

Struktur utama:

```text
app/
├── Enums/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Repositories/
├── Services/
└── ...

database/
├── factories/
├── migrations/
└── seeders/

routes/
└── api.php

resources/
├── css/
└── js/
```

Alur umum request:

```text
Client
   │
   ▼
API Route
   │
   ▼
Middleware
(Authentication / Role)
   │
   ▼
Controller
   │
   ▼
Service
   │
   ▼
Repository
   │
   ▼
Model / Database
   │
   ▼
JSON Response
```

Pendekatan ini membantu memisahkan logic HTTP, business logic, dan akses database sehingga kode lebih mudah dikembangkan dan dipelihara.

## Authentication

API menggunakan **Laravel Sanctum** untuk autentikasi.

Endpoint yang membutuhkan autentikasi menggunakan:

```text
Authorization: Bearer <token>
```

Contoh:

```http
GET /api/me
Authorization: Bearer 1|xxxxxxxxxxxxxxxx
Accept: application/json
```

Endpoint authentication seperti login dan register tidak membutuhkan token.

## Installation

### Requirements

Pastikan environment sudah memiliki:

* PHP 8.2+
* Composer
* Node.js & NPM
* MySQL / MariaDB
* Git

### Clone Repository

```bash
git clone https://github.com/athasyah/API-SonaraRent.git
cd API-SonaraRent
```

### Install PHP Dependencies

```bash
composer install
```

### Environment Configuration

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Untuk Windows:

```cmd
copy .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### Database

Buat database baru, kemudian sesuaikan konfigurasi database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sonararent
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration:

```bash
php artisan migrate
```

Jika project menyediakan seeder:

```bash
php artisan db:seed
```

Atau:

```bash
php artisan migrate --seed
```

### Install Frontend Dependencies

```bash
npm install
```

Untuk development:

```bash
npm run dev
```

### Run Application

Jalankan Laravel development server:

```bash
php artisan serve
```

API dapat diakses melalui:

```text
http://127.0.0.1:8000
```

Endpoint API:

```text
http://127.0.0.1:8000/api
```

## Development

Untuk menjalankan environment development secara bersamaan, project menyediakan script Composer:

```bash
composer run dev
```

Script tersebut menjalankan beberapa proses seperti Laravel server, queue listener, log viewer, dan Vite development server.

## Testing

Project menggunakan **Pest PHP** untuk automated testing.

Jalankan test:

```bash
php artisan test
```

Atau:

```bash
composer test
```

## API Authorization

Endpoint API dilindungi menggunakan kombinasi:

```text
Laravel Sanctum
        +
Spatie Laravel Permission
        +
Role Middleware
```

Contoh pembagian akses:

```text
ADMIN
 ├── User Management
 ├── Category Management
 ├── Activity Log
 ├── Dashboard
 ├── Reports
 ├── Export
 └── Settings

STAFF
 ├── Rental Operations
 ├── Payment
 ├── Guarantee
 ├── Instrument Condition
 ├── Dashboard
 └── Reports

CUSTOMER
 ├── Browse Instruments
 ├── Rental
 ├── Cart
 ├── Review
 ├── Payment
 └── Notifications
```

Route API pada project secara langsung menerapkan middleware `auth:sanctum` dan role middleware untuk membatasi akses berdasarkan role.

## Project Structure

```text
API-SonaraRent/
│
├── app/
│   ├── Enums/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Requests/
│   ├── Models/
│   ├── Repositories/
│   └── Services/
│
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
├── resources/
├── routes/
│   └── api.php
│
├── storage/
├── tests/
│
├── .env.example
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```
**SonaraRent API**
RESTful backend for instrument rental management.

Developed by **Athasyah Addin Satriya Abdi**.
