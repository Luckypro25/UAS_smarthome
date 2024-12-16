# UAS Smart Home

## Deskripsi
Proyek **UAS Smart Home** adalah aplikasi berbasis PHP yang dirancang untuk mengelola fitur *smart home* secara virtual. Aplikasi ini mendukung fitur kontrol suara dan dapat digunakan tanpa perangkat keras. Proyek ini dibuat menggunakan **Laragon** sebagai server lokal.

## Fitur
- **Kontrol Suara Virtual**: Menggunakan antarmuka sederhana untuk mengontrol fungsi smart home.
- **Manajemen Data**: Menyimpan dan mengelola data melalui database MySQL.
- **Antarmuka Responsif**: Dibangun menggunakan Bootstrap untuk tampilan yang responsif di berbagai perangkat.

## Teknologi yang Digunakan
- **PHP**: Untuk logika backend.
- **MySQL**: Untuk menyimpan data.
- **HTML, CSS, JavaScript**: Untuk antarmuka pengguna.
- **Bootstrap**: Untuk tampilan yang modern dan responsif.
- **Laragon**: Sebagai lingkungan pengembangan server lokal.

## Cara Install dan Menjalankan
Ikuti langkah-langkah berikut untuk menjalankan proyek ini di komputer Anda:

1. **Clone Repository**
   Clone repository ini ke komputer Anda:
   ```bash
   git clone https://github.com/username/UAS_SmartHome.git
1.Jalankan Server Lokal
Pastikan Laragon sudah terinstal di komputer Anda.
Buka Laragon dan jalankan semua layanan (Apache, MySQL, dll.).

3Import Database
Buka phpMyAdmin melalui Laragon.
Buat database baru (misalnya: smarthome).
Import file database yang terletak di folder database/smarthome.sql.

4.Akses Aplikasi

Buka browser dan akses aplikasi melalui URL:
Copy code
http://localhost/UAS_SmartHome
