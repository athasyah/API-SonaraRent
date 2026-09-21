# SonaraRent API

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel\&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php\&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**SonaraRent** adalah sistem penyewaan alat musik berbasis web yang dirancang untuk membantu proses pengelolaan penyewaan instrumen musik secara terintegrasi.

Project ini berfokus pada pengembangan **backend REST API** yang menangani proses utama dalam sistem rental, mulai dari pengelolaan instrumen, pelanggan, transaksi penyewaan, pembayaran, jaminan, denda, hingga laporan dan aktivitas sistem.

---

## Tentang SonaraRent

Proses penyewaan alat musik dapat melibatkan banyak tahapan, seperti pengecekan ketersediaan instrumen, pencatatan data penyewa, pengelolaan transaksi, pembayaran, pengembalian barang, hingga pencatatan kondisi instrumen.

SonaraRent dikembangkan untuk menyediakan satu sistem terintegrasi yang dapat menangani proses tersebut secara terstruktur melalui REST API.

Sistem memiliki beberapa jenis pengguna dengan kebutuhan dan hak akses yang berbeda, yaitu **Admin, Staff, dan Customer**.

Customer dapat menggunakan sistem untuk mencari instrumen dan melakukan penyewaan, sedangkan Staff menangani proses operasional rental. Admin memiliki akses terhadap pengelolaan sistem dan data secara lebih luas.

---

## Tujuan Project

SonaraRent dikembangkan dengan beberapa tujuan utama:

* Mempermudah proses penyewaan alat musik.
* Mengelola data instrumen dan ketersediaannya secara terpusat.
* Mengelola transaksi rental secara terstruktur.
* Mendukung proses pembayaran dan jaminan penyewaan.
* Mencatat kondisi instrumen sebelum dan sesudah penyewaan.
* Mengelola denda apabila terjadi keterlambatan atau masalah pada rental.
* Menyediakan laporan dan statistik untuk membantu pengelolaan bisnis rental.
* Menyediakan backend API yang dapat digunakan oleh aplikasi frontend.

---

## Fitur Utama

### 🎸 Instrument Management

Mengelola seluruh data instrumen yang tersedia untuk disewakan, termasuk:

* Data instrumen
* Kategori instrumen
* Kondisi instrumen
* Harga rental
* Ketersediaan instrumen

Sistem juga menyediakan pengecekan ketersediaan instrumen sebelum proses rental dilakukan.

### 👤 User & Role Management

SonaraRent menerapkan sistem role untuk membedakan hak akses setiap pengguna.

| Role         | Tanggung Jawab                                                         |
| ------------ | ---------------------------------------------------------------------- |
| **Admin**    | Mengelola sistem, pengguna, data master, laporan, dan konfigurasi      |
| **Staff**    | Menangani operasional rental, pembayaran, instrumen, dan transaksi     |
| **Customer** | Melihat instrumen, melakukan rental, pembayaran, dan memberikan review |

### 🛒 Rental Management

Merupakan salah satu bagian utama SonaraRent yang menangani siklus penyewaan instrumen.

Proses rental mencakup:

```text
Pilih Instrumen
      ↓
Cek Ketersediaan
      ↓
Keranjang Rental
      ↓
Buat Transaksi
      ↓
Jaminan & Pembayaran
      ↓
Instrumen Disewakan
      ↓
Pengembalian
      ↓
Pengecekan Kondisi
      ↓
Selesai / Denda
```

### 💳 Payment

Sistem menangani proses pembayaran transaksi rental dan menyediakan mekanisme untuk mencatat status pembayaran.

Payment flow dirancang agar status transaksi dapat mengikuti proses rental secara terstruktur.

### 🛡️ Guarantee Management

Karena instrumen musik merupakan barang yang disewakan, sistem menyediakan mekanisme **jaminan rental**.

Data jaminan dikaitkan dengan transaksi sehingga dapat digunakan sebagai bagian dari proses verifikasi dan pengelolaan rental.

### ⚠️ Penalty Management

SonaraRent menyediakan pengelolaan denda yang berkaitan dengan transaksi rental.

Denda dapat digunakan untuk mencatat konsekuensi dari kondisi tertentu dalam proses penyewaan, seperti masalah pada pengembalian atau kondisi instrumen.

### ⭐ Review & Rating

Customer dapat memberikan review terhadap instrumen yang telah digunakan.

Fitur ini membantu menyediakan feedback dari pengguna terhadap instrumen yang tersedia dalam sistem.

### 🔔 Notification

Sistem menyediakan notifikasi untuk memberikan informasi kepada pengguna terkait aktivitas atau perubahan yang terjadi dalam sistem rental.

### 📊 Dashboard & Reporting

Admin dan Staff dapat memperoleh informasi mengenai aktivitas rental melalui dashboard dan laporan.

Beberapa informasi yang tersedia meliputi:

* Statistik transaksi rental
* Pendapatan
* Instrumen populer
* Tren penjualan
* Data denda
* Aktivitas rental

---

## Backend Architecture

SonaraRent dibangun sebagai **RESTful API** menggunakan Laravel.

Struktur backend menerapkan pemisahan tanggung jawab antara beberapa layer:

```text
                    Client
                      │
                      ▼
                   API Route
                      │
                      ▼
                  Middleware
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
                    Model
                      │
                      ▼
                   Database
```

### Controller

Controller bertanggung jawab menerima HTTP request dan mengembalikan response kepada client.

### Service

Service menangani **business logic** dari sistem sehingga proses bisnis tidak menumpuk di dalam Controller.

### Repository

Repository digunakan sebagai layer untuk mengelola interaksi dengan database dan memisahkan database access dari business logic.

### Model

Model merepresentasikan data dan relasi yang digunakan dalam sistem.

Pendekatan ini digunakan agar setiap bagian aplikasi memiliki tanggung jawab yang lebih jelas serta lebih mudah dikembangkan dan dipelihara.

---

## Technology Stack

| Teknologi                     | Penggunaan           |
| ----------------------------- | -------------------- |
| **Laravel 12**                | Backend framework    |
| **PHP 8.2+**                  | Bahasa pemrograman   |
| **Laravel Sanctum**           | Authentication API   |
| **Spatie Laravel Permission** | Role & permission    |
| **MySQL**                     | Database             |
| **Maatwebsite Excel**         | Export data          |
| **DOMPDF**                    | Generate dokumen PDF |
| **Pest PHP**                  | Automated testing    |
| **Vite**                      | Asset bundling       |

---

## Sistem Secara Keseluruhan

SonaraRent dapat digambarkan sebagai ekosistem rental yang menghubungkan tiga sisi utama:

```text
                  SONARARENT
                      │
        ┌─────────────┼─────────────┐
        │             │             │
        ▼             ▼             ▼
    CUSTOMER        STAFF         ADMIN
        │             │             │
        │             │             │
   ┌────▼────┐   ┌────▼────┐   ┌────▼────┐
   │ Browse  │   │ Rental  │   │ Master  │
   │ Rental  │   │ Payment │   │ Report  │
   │ Payment │   │ Return  │   │ User    │
   │ Review  │   │ Penalty │   │ System  │
   └─────────┘   └─────────┘   └─────────┘
        │             │             │
        └─────────────┼─────────────┘
                      ▼
                 SONARARENT API
                      │
                      ▼
                   DATABASE
```

---

## Project Highlights

Beberapa hal yang menjadi fokus dalam pengembangan SonaraRent:

* RESTful API architecture
* Role-based access control
* Authentication menggunakan Laravel Sanctum
* Service & Repository Pattern
* Pemisahan business logic dan database access
* Rental lifecycle management
* Payment & guarantee management
* Instrument condition tracking
* Penalty management
* Reporting dan data export
* Automated testing

---

## Project Purpose

SonaraRent dibuat sebagai implementasi sistem backend untuk **digitalisasi proses bisnis penyewaan alat musik**.

Project ini tidak hanya berfungsi sebagai CRUD data instrumen, tetapi mencakup proses bisnis rental secara lebih lengkap, mulai dari **ketersediaan barang → transaksi → pembayaran → penyewaan → pengembalian → pengecekan kondisi → denda → laporan**.

Dengan pendekatan REST API, backend SonaraRent dapat digunakan sebagai fondasi untuk berbagai client application, seperti web application maupun aplikasi mobile.

---

## License

This project is licensed under the **MIT License**.

---

### Developed by

**Athasyah Addin Satriya Abdi**

Backend Developer — SonaraRent
