# Full-Stack Quiz Web App 🚀

Aplikasi kuis pilihan ganda interaktif yang dibangun menggunakan arsitektur *decoupled* (Frontend dan Backend terpisah). Proyek ini telah dioptimalkan dengan standar *Enterprise UI/UX*, menampilkan animasi transisi yang mulus, sistem manajemen sesi, dan fitur evaluasi *grading* otomatis.

## 🛠️ Tech Stack
* **Frontend:** Vue 3, Vite, SweetAlert2, CSS3 (Transitions & Animations)
* **Backend:** PHP (Native)
* **Database:** MySQL
* **Server Lokal:** Laragon / XAMPP

## ✨ Fitur Utama
* **Manajemen Sesi (Session Retention):** Pengguna tidak akan ter-logout secara otomatis jika tidak sengaja me-*refresh* halaman web (F5).
* **Enterprise UI/UX & Anti-Spam:** Efek transisi *fade/slide* antar halaman, serta state *loading* dinamis yang mematikan tombol saat menunggu respons *server* untuk mencegah *spam klik*.
* **Indikator Progres Visual:** *Progress bar* interaktif yang terisi otomatis seiring jumlah soal yang berhasil dijawab.
* **Sistem Grading Otomatis:** Laporan hasil kelulusan dinamis berdasarkan *Passing Grade* (KKM = 70), dilengkapi warna indikator.
* **Pengacakan Dinamis (Randomizer):** Urutan soal dan opsi jawaban akan selalu diacak setiap kali kuis dimulai.
* **Validasi & Auto-Submit:** Pengguna wajib menjawab seluruh soal untuk mengirim jawaban manual. Jika timer (3 Menit) habis, sistem akan otomatis mengirim jawaban yang ada.

## 📂 Struktur Direktori (Monorepo)
Proyek ini dibungkus dalam satu repositori utama dengan pemisahan folder sebagai berikut:
```text
quiz-app/
├── quiz-frontend/      # (Antarmuka Vue 3 & Logika Kuis)
│   ├── src/
│   │   ├── App.vue     # Komponen utama kuis (UI & State)
│   │   ├── style.css   # Styling dan Animasi
│   │   └── main.js
│   ├── package.json
│   └── vite.config.js
├── quiz-backend/       # (API PHP & Koneksi Database)
│   ├── auth.php        # API Login & Register
│   ├── skor.php        # API Rekam Skor
│   ├── koneksi.php     # Konfigurasi Database
│   └── db_quiz_app.sql # Skema Tabel Database
├── .gitignore
└── README.md

```

## 🚀 Panduan Instalasi & Cara Menjalankan

### 1. Persiapan Database (MySQL)

1. Buka aplikasi Laragon/XAMPP dan jalankan **Apache** serta **MySQL**.
2. Buka aplikasi manajemen database (phpMyAdmin atau HeidiSQL).
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
Atau import file .sql yang berada di dalam folder quiz backend ke dalam database
1. *Import* file `db_quiz_app.sql` yang berada di dalam folder `quiz-backend/` ke dalam database tersebut.

### 2. Konfigurasi Backend (PHP)

Pastikan seluruh folder proyek (`quiz-app`) ditempatkan di dalam *root directory* server lokal Anda:

* Jika menggunakan Laragon: `C:\laragon\www\quiz-app`
* Jika menggunakan XAMPP: `C:\xampp\htdocs\quiz-app`

*Catatan: File PHP sudah terkonfigurasi dengan CORS (`Access-Control-Allow-Origin: *`) untuk menerima request dari port Vue.*

### 3. Menjalankan Frontend (Vue 3)

1. Buka terminal atau *command prompt*.
2. Masuk ke direktori *frontend*:
```bash
cd quiz-frontend

```


3. Instal seluruh dependensi:
```bash
npm install

```


4. Jalankan server *development*:
```bash
npm run dev

```


5. Buka tautan lokal yang muncul di terminal (biasanya `http://localhost:5173`) di *browser* Anda.
