# Full-Stack Quiz Web App 🚀

Aplikasi kuis pilihan ganda interaktif yang dibangun menggunakan arsitektur *decoupled* (terpisah antara Frontend dan Backend). Aplikasi ini dilengkapi dengan sistem autentikasi pengguna, penghitung waktu mundur, navigasi soal dinamis, dan sistem tinjauan hasil (analisis benar/salah).

## 🛠️ Tech Stack
* **Frontend:** Vue 3, Vite, SweetAlert2, Vanilla CSS
* **Backend:** PHP (Native)
* **Database:** MySQL
* **Server Lokal:** Laragon / XAMPP

## ✨ Fitur Utama
* **Autentikasi Pengguna:** Pendaftaran akun baru dan login yang tersimpan secara persisten di database.
* **Pengacakan Dinamis (Randomizer):** Urutan soal dan opsi jawaban (A, B, C, D) akan selalu diacak setiap kali kuis dimulai.
* **Navigasi Interaktif:** Sidebar tata letak *split-screen* yang memungkinkan pengguna melompat ke soal manapun.
* **Validasi Ujian:** Pengguna wajib menjawab seluruh soal sebelum dapat menekan tombol "Selesai Ujian".
* **Auto-Submit:** Jika timer 3 menit habis, sistem akan otomatis mengirimkan jawaban yang sudah terisi.
* **Evaluasi Hasil:** Menampilkan skor akhir (skala 0-100) beserta rincian tinjauan (review) jawaban benar dan salah.

## 📂 Struktur Direktori
Proyek ini memisahkan *logic* antarmuka dan *logic* basis data ke dalam dua folder utama:
```text
C:\laragon\www\
├── quiz-frontend/      # (Vue 3 UI, State Management)
│   ├── src/
│   │   ├── App.vue     # Komponen utama kuis
│   │   └── main.js
│   ├── package.json
│   └── vite.config.js
└── quiz-backend/       # (PHP API, Database Connection)
    ├── auth.php        # API Login & Register
    ├── skor.php        # API Simpan Skor
    └── koneksi.php     # Konfigurasi MySQL

```

## 🚀 Panduan Instalasi & Menjalankan Proyek

### 1. Persiapan Database (MySQL)

1. Buka aplikasi Laragon/XAMPP dan jalankan **Apache** serta **MySQL**.
2. Buka phpMyAdmin atau HeidiSQL.
3. Buat database baru bernama `db_quiz_app`.
4. Eksekusi *query* SQL berikut untuk membuat tabel:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    nomor_kuis VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skor INT NOT NULL,
    waktu_selesai TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

```

### 2. Konfigurasi Backend (PHP)

1. Pastikan folder `quiz-backend` berada di dalam *root directory* server lokal Anda (contoh: `C:\laragon\www\quiz-backend`).
2. API PHP sudah dilengkapi dengan pengaturan CORS (`Access-Control-Allow-Origin: *`) agar dapat menerima *request* dari port Vue.

### 3. Menjalankan Frontend (Vue 3)

1. Buka terminal atau *command prompt*.
2. Masuk ke direktori frontend:
```bash
cd C:\laragon\www\quiz-frontend

```

3. Instal semua dependensi proyek:
```bash
npm install

```

4. Jalankan server *development*:
```bash
npm run dev

```

5. Buka tautan yang muncul di terminal (biasanya `http://localhost:5173`) di browser Anda.

```


# Vue 3 + Vite

This template should help get you started developing with Vue 3 in Vite. The template uses Vue 3 `<script setup>` SFCs, check out the [script setup docs](https://v3.vuejs.org/api/sfc-script-setup.html#sfc-script-setup) to learn more.

Learn more about IDE Support for Vue in the [Vue Docs Scaling up Guide](https://vuejs.org/guide/scaling-up/tooling.html#ide-support).
