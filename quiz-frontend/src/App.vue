<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

// State Manajemen Layar & Sesi
const currentView = ref('login');
const isLoading = ref(false); // State untuk loading button
const userName = ref('');

// State Kuis
const currentQuestion = ref(0);
const score = ref(0);
const timeLeft = ref(180);
const userAnswers = ref([]);
const quizDate = ref('');
const passingGrade = 70; // Standar kelulusan
let timerInterval = null;

// Form Kredensial
const emailInput = ref('');
const passInput = ref('');
const regName = ref('');
const regRoll = ref('');
const regEmail = ref('');
const regPass = ref('');

const questions = ref([
  { question: "Apa kepanjangan dari HTML?", options: ["Hypertext Markup Language", "Hypertext Machine Language", "Hypertext Meta Language", "Hyperlink Text Markup Language"], correctAnswer: "Hypertext Markup Language" },
  { question: "Apa kepanjangan dari CSS?", options: ["Cascading Style Sheets", "Cascading Scripting Sheets", "Cascading Server Sheets", "Creative Style Sheets"], correctAnswer: "Cascading Style Sheets" },
  { question: "Tag HTML mana yang digunakan untuk membuat tautan?", options: ["&lt;a&gt;", "&lt;link&gt;", "&lt;href&gt;", "&lt;url&gt;"], correctAnswer: "&lt;a&gt;" },
  { question: "Properti CSS untuk mengubah warna latar belakang adalah?", options: ["color", "background-color", "bgcolor", "background"], correctAnswer: "background-color" },
  { question: "Bahasa yang digunakan untuk memberikan interaktivitas pada web adalah?", options: ["HTML", "CSS", "JavaScript", "PHP"], correctAnswer: "JavaScript" },
  { question: "Simbol apa yang digunakan untuk ID selector di CSS?", options: [".", "#", "*", "@"], correctAnswer: "#" },
  { question: "Bagaimana cara menulis komentar di HTML?", options: ["// komentar", "/* komentar */", "&lt;!-- komentar --&gt;", "&lt;! komentar !&gt;"], correctAnswer: "&lt;!-- komentar --&gt;" },
  { question: "Atribut apa yang memberikan teks alternatif pada gambar?", options: ["title", "src", "alt", "href"], correctAnswer: "alt" },
  { question: "Fungsi JavaScript untuk mencetak teks ke konsol adalah?", options: ["console.log()", "print()", "echo", "document.write()"], correctAnswer: "console.log()" },
  { question: "Mana yang bukan merupakan tipe data di JavaScript?", options: ["String", "Boolean", "Float", "Undefined"], correctAnswer: "Float" }
]);

const shuffleArray = (array) => array.sort(() => Math.random() - 0.5);

// Cek Sesi saat Web Dimuat (Mencegah ter-logout otomatis saat Refresh)
onMounted(() => {
  const userId = sessionStorage.getItem("user_id");
  const storedName = sessionStorage.getItem("user_name");
  if (userId) {
    userName.value = storedName || "Pengguna";
    currentView.value = 'info';
  }
});

const handleLogin = async () => {
  if (!emailInput.value || !passInput.value) {
    Swal.fire({ icon: 'warning', text: 'Isi email dan password!' });
    return;
  }

  isLoading.value = true;
  const formData = new FormData();
  formData.append("action", "login");
  formData.append("email", emailInput.value);
  formData.append("password", passInput.value);

  try {
    const response = await fetch("http://localhost/quiz-app/quiz-backend/auth.php", { method: "POST", body: formData });
    const data = await response.json();
    if (data.status === "success") {
      sessionStorage.setItem("user_id", data.user_id);
      sessionStorage.setItem("user_name", data.nama);
      userName.value = data.nama;
      currentView.value = 'info';

      // SweetAlert Toast (Non-blocking)
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Selamat datang, ${data.nama}!`, showConfirmButton: false, timer: 3000 });
    } else {
      Swal.fire({ icon: 'error', title: 'Login Gagal', text: data.message });
    }
  } catch (error) {
    Swal.fire({ icon: 'error', text: 'Koneksi ke server gagal. Pastikan Laragon menyala.' });
  } finally {
    isLoading.value = false;
  }
};

const handleRegister = async () => {
  if (!regName.value || !regRoll.value || !regEmail.value || !regPass.value) {
    Swal.fire({ icon: 'warning', text: 'Lengkapi semua data!' });
    return;
  }

  isLoading.value = true;
  const formData = new FormData();
  formData.append("action", "register");
  formData.append("nama", regName.value);
  formData.append("nomor", regRoll.value);
  formData.append("email", regEmail.value);
  formData.append("password", regPass.value);

  try {
    const response = await fetch("http://localhost/quiz-app/quiz-backend/auth.php", { method: "POST", body: formData });
    const data = await response.json();
    if (data.status === "success") {
      Swal.fire({ icon: 'success', text: 'Akun berhasil dibuat. Silakan login!' });
      currentView.value = 'login';
      // Bersihkan form
      regName.value = ''; regRoll.value = ''; regEmail.value = ''; regPass.value = '';
    } else {
      Swal.fire({ icon: 'error', text: data.message });
    }
  } catch (error) {
    Swal.fire({ icon: 'error', text: 'Gagal menghubungi server.' });
  } finally {
    isLoading.value = false;
  }
};

const handleLogout = () => {
  Swal.fire({
    title: 'Keluar sesi?',
    text: "Anda harus login kembali untuk mengikuti kuis.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Ya, Logout'
  }).then((result) => {
    if (result.isConfirmed) {
      sessionStorage.clear();
      currentView.value = 'login';
      emailInput.value = '';
      passInput.value = '';
    }
  });
};

const startQuiz = () => {
  // Acak soal dan opsi jawaban
  questions.value = shuffleArray([...questions.value]);
  questions.value.forEach(q => q.options = shuffleArray([...q.options]));

  userAnswers.value = new Array(questions.value.length).fill(null);

  const now = new Date();
  const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
  quizDate.value = now.toLocaleDateString('id-ID', options).replace(/\./g, ':');

  currentQuestion.value = 0;
  score.value = 0;
  timeLeft.value = 180;
  currentView.value = 'quiz';
  startTimer();
};

const startTimer = () => {
  timerInterval = setInterval(() => {
    timeLeft.value--;
    if (timeLeft.value <= 0) {
      clearInterval(timerInterval);
      Swal.fire({ icon: 'info', title: 'Waktu Habis!', text: 'Sistem mengumpulkan jawaban secara otomatis.', timer: 3000, showConfirmButton: false });
      finishQuiz(true);
    }
  }, 1000);
};

// Computed Properties
const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0');
  const s = (timeLeft.value % 60).toString().padStart(2, '0');
  return `${m}m : ${s}s`;
});

const progressPercentage = computed(() => {
  const answeredCount = userAnswers.value.filter(ans => ans !== null).length;
  return Math.round((answeredCount / questions.value.length) * 100);
});

const isPassed = computed(() => score.value >= passingGrade);

// Aksi Navigasi Kuis
const goToQuestion = (index) => currentQuestion.value = index;
const nextQuestion = () => { if (currentQuestion.value < questions.value.length - 1) currentQuestion.value++; };

const finishQuiz = async (isTimeOut = false) => {
  if (!isTimeOut) {
    const belumDijawab = userAnswers.value.filter(ans => ans === null).length;
    if (belumDijawab > 0) {
      Swal.fire({ icon: 'warning', title: 'Belum Selesai', text: `Masih ada ${belumDijawab} soal yang belum dijawab.` });
      return;
    }

    // Konfirmasi sebelum submit manual
    const confirm = await Swal.fire({
      title: 'Kirim Jawaban?',
      text: "Anda tidak dapat mengubah jawaban setelah dikirim.",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#0097a7',
      confirmButtonText: 'Ya, Kirim!'
    });

    if (!confirm.isConfirmed) return;
  }

  clearInterval(timerInterval);

  let correctCount = 0;
  questions.value.forEach((q, index) => {
    if (userAnswers.value[index] === q.correctAnswer) correctCount++;
  });
  score.value = Math.round((correctCount / questions.value.length) * 100);

  currentView.value = 'result';

  const userId = sessionStorage.getItem("user_id");
  if (userId) {
    const formData = new FormData();
    formData.append("user_id", userId);
    formData.append("skor", score.value);
    try {
      await fetch("http://localhost/quiz-app/quiz-backend/skor.php", { method: "POST", body: formData });
    } catch (error) {
      console.error(error);
    }
  }
};

const restart = () => currentView.value = 'info';
</script>

<template>
  <div class="app-wrapper">
    <Transition name="fade" mode="out-in">

      <div class="auth-container" v-if="currentView === 'login' || currentView === 'register'" key="auth">

        <div class="auth-box" v-if="currentView === 'login'">
          <h2>Masuk ke Kuis</h2>
          <input type="email" v-model="emailInput" placeholder="Alamat Email" class="form-input"
            :disabled="isLoading" />
          <input type="password" v-model="passInput" placeholder="Kata Sandi" class="form-input"
            @keyup.enter="handleLogin" :disabled="isLoading" />

          <button class="btn-primary" @click="handleLogin" :disabled="isLoading">
            {{ isLoading ? 'Memproses...' : 'Login' }}
          </button>
          <p class="auth-link">Belum punya akun? <a href="#" @click.prevent="currentView = 'register'">Daftar
              Sekarang</a></p>
        </div>

        <div class="auth-box" v-if="currentView === 'register'">
          <h2>Pendaftaran Akun</h2>
          <input type="text" v-model="regName" placeholder="Nama Lengkap" class="form-input" :disabled="isLoading" />
          <input type="text" v-model="regRoll" placeholder="Nomor Induk / Kuis" class="form-input"
            :disabled="isLoading" />
          <input type="email" v-model="regEmail" placeholder="Alamat Email" class="form-input" :disabled="isLoading" />
          <input type="password" v-model="regPass" placeholder="Kata Sandi Baru" class="form-input"
            @keyup.enter="handleRegister" :disabled="isLoading" />

          <button class="btn-primary" @click="handleRegister" :disabled="isLoading">
            {{ isLoading ? 'Mendaftarkan...' : 'Register' }}
          </button>
          <p class="auth-link">Sudah punya akun? <a href="#" @click.prevent="currentView = 'login'">Masuk di sini</a>
          </p>
        </div>
      </div>

      <div class="auth-container" v-else-if="currentView === 'info'" key="info">
        <div class="auth-box info-box">
          <div class="user-welcome">Hai, <strong>{{ userName }}</strong>!</div>
          <h2>Persiapan Ujian</h2>
          <div class="info-card">
            <ul>
              <li><span class="highlight-text">Durasi Ujian:</span> 3 Menit.</li>
              <li>Gunakan navigasi nomor untuk meninjau ulang jawaban.</li>
              <li><strong>Semua soal wajib diisi</strong> sebelum tombol selesai aktif.</li>
              <li>Nilai KKM (Passing Grade) adalah <strong>{{ passingGrade }}</strong>.</li>
            </ul>
          </div>

          <div class="action-buttons">
            <button class="btn-secondary" @click="handleLogout">Logout</button>
            <button class="btn-primary" @click="startQuiz">Mulai Kuis Sekarang</button>
          </div>
        </div>
      </div>

      <div class="dicoding-layout" v-else-if="currentView === 'quiz'" key="quiz">
        <div class="d-topbar">
          <div class="progress-wrapper">
            <div class="progress-text">Progres: {{ progressPercentage }}%</div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
            </div>
          </div>
          <div :class="['d-timer', { 'timer-danger': timeLeft <= 30 }]">
            ⏱️ {{ formattedTime }}
          </div>
        </div>

        <div class="d-main">
          <div class="d-sidebar">
            <div class="sidebar-title">Kategori: Pemrograman Web</div>
            <div class="d-grid">
              <button v-for="(q, i) in questions" :key="i"
                :class="['nav-btn', { 'active': currentQuestion === i, 'answered': userAnswers[i] && currentQuestion !== i }]"
                @click="goToQuestion(i)">
                {{ i + 1 }}
              </button>
            </div>
          </div>

          <div class="d-content">
            <div class="question-header">Soal No. {{ currentQuestion + 1 }}</div>
            <div class="question-text">
              <span v-html="questions[currentQuestion].question"></span>
            </div>

            <div class="options-container">
              <label :class="['d-radio', { 'selected-radio': userAnswers[currentQuestion] === opt }]"
                v-for="(opt, index) in questions[currentQuestion].options" :key="index">
                <input type="radio" :name="'q' + currentQuestion" :value="opt" v-model="userAnswers[currentQuestion]">
                <span class="radio-text" v-html="opt"></span>
              </label>
            </div>

            <div class="d-footer">
              <button class="btn-next" v-if="currentQuestion < questions.length - 1" @click="nextQuestion">
                Selanjutnya &rsaquo;
              </button>
              <button class="btn-submit" v-else @click="finishQuiz(false)">
                Selesai Ujian &check;
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="dicoding-layout result-layout" v-else-if="currentView === 'result'" key="result">
        <div class="d-topbar result-topbar">
          <h2>Laporan Hasil Ujian</h2>
          <button class="btn-secondary logout-mini" @click="handleLogout">Logout</button>
        </div>

        <div class="d-main">
          <div class="d-sidebar result-sidebar">
            <p class="exam-date"><strong>Diselesaikan pada:</strong> <br> {{ quizDate }}</p>

            <div :class="['grade-status', isPassed ? 'status-passed' : 'status-failed']">
              {{ isPassed ? 'LULUS KOMPETENSI' : 'TIDAK LULUS' }}
            </div>

            <div class="score-summary">
              <div class="score-box">
                <p>Total Soal</p>
                <h3>{{ questions.length }}</h3>
              </div>
              <div class="score-box">
                <p :class="isPassed ? 'txt-green' : 'txt-red'">Skor Akhir</p>
                <h3 :class="isPassed ? 'txt-green' : 'txt-red'">{{ score }}</h3>
              </div>
            </div>

            <button class="btn-primary btn-block" style="margin-top: 20px;" @click="restart">Ulangi Ujian</button>
          </div>

          <div class="d-content result-content">
            <h3 class="category-title">Tinjauan Detail Jawaban</h3>

            <div class="review-item" v-for="(q, i) in questions" :key="i">
              <div class="review-header">
                <div class="review-q"><span style="font-weight: 600; color:#0097a7;">{{ i + 1 }}.</span> <span
                    v-html="q.question"></span></div>
              </div>

              <div class="review-options">
                <div v-for="(opt, optIdx) in q.options" :key="optIdx" :class="['review-opt', {
                  'correct-border': opt === q.correctAnswer,
                  'wrong-border': userAnswers[i] === opt && opt !== q.correctAnswer
                }]">
                  <div class="opt-letter">{{ String.fromCharCode(65 + optIdx) }}</div>
                  <div class="opt-text" v-html="opt"></div>

                  <div class="indicator-icon" v-if="opt === q.correctAnswer">✔️</div>
                  <div class="indicator-icon" v-else-if="userAnswers[i] === opt">❌</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </Transition>
  </div>
</template>