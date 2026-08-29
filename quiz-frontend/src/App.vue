<script setup>
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const currentView = ref('login');
const currentQuestion = ref(0);
const score = ref(0);
const timeLeft = ref(180);
const userAnswers = ref([]);
const quizDate = ref('');
let timerInterval = null;

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

const handleLogin = async () => {
  if (!emailInput.value || !passInput.value) {
    Swal.fire({ icon: 'warning', text: 'Isi email dan password!' });
    return;
  }
  const formData = new FormData();
  formData.append("action", "login");
  formData.append("email", emailInput.value);
  formData.append("password", passInput.value);

  try {
    const response = await fetch("http://localhost/quiz-backend/auth.php", { method: "POST", body: formData });
    const data = await response.json();
    if (data.status === "success") {
      sessionStorage.setItem("user_id", data.user_id);
      currentView.value = 'info';
    } else {
      Swal.fire({ icon: 'error', title: 'Login Gagal', text: data.message });
    }
  } catch (error) {
    Swal.fire({ icon: 'error', text: 'Koneksi ke backend gagal.' });
  }
};

const handleRegister = async () => {
  if (!regName.value || !regRoll.value || !regEmail.value || !regPass.value) {
    Swal.fire({ icon: 'warning', text: 'Lengkapi semua data!' });
    return;
  }
  const formData = new FormData();
  formData.append("action", "register");
  formData.append("nama", regName.value);
  formData.append("nomor", regRoll.value);
  formData.append("email", regEmail.value);
  formData.append("password", regPass.value);

  try {
    const response = await fetch("http://localhost/quiz-backend/auth.php", { method: "POST", body: formData });
    const data = await response.json();
    if (data.status === "success") {
      Swal.fire({ icon: 'success', text: 'Akun dibuat. Silakan login!' });
      currentView.value = 'login';
    } else {
      Swal.fire({ icon: 'error', text: data.message });
    }
  } catch (error) {
    Swal.fire({ icon: 'error', text: 'Gagal menghubungi server.' });
  }
};

const startQuiz = () => {
  questions.value = shuffleArray([...questions.value]);
  questions.value.forEach(q => q.options = shuffleArray([...q.options]));

  userAnswers.value = new Array(questions.value.length).fill(null);

  const now = new Date();
  const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
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
      Swal.fire({ icon: 'info', title: 'Waktu Habis!', text: 'Jawaban Anda telah dikirim secara otomatis.', timer: 2500 });
      finishQuiz(true);
    }
  }, 1000);
};

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0');
  const s = (timeLeft.value % 60).toString().padStart(2, '0');
  return `${m}m:${s}s`;
});

const goToQuestion = (index) => {
  currentQuestion.value = index;
};

const nextQuestion = () => {
  if (currentQuestion.value < questions.value.length - 1) {
    currentQuestion.value++;
  }
};

const finishQuiz = async (isTimeOut = false) => {

  if (!isTimeOut) {
    const belumDijawab = userAnswers.value.filter(ans => ans === null).length;
    if (belumDijawab > 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Belum Selesai',
        text: `Masih ada ${belumDijawab} soal yang belum dijawab. Cek kotak navigasi!`
      });
      return;
    }
  }

  clearInterval(timerInterval);

  let correctCount = 0;
  questions.value.forEach((q, index) => {
    if (userAnswers.value[index] === q.correctAnswer) {
      correctCount++;
    }
  });
  score.value = Math.round((correctCount / questions.value.length) * 100);

  currentView.value = 'result';

  const userId = sessionStorage.getItem("user_id");
  if (userId) {
    const formData = new FormData();
    formData.append("user_id", userId);
    formData.append("skor", score.value);
    try {
      await fetch("http://localhost/quiz-backend/skor.php", { method: "POST", body: formData });
    } catch (error) {
      console.error(error);
    }
  }
};

const restart = () => {
  currentView.value = 'info';
};
</script>

<template>
  <div class="app-wrapper">

    <div class="auth-container" v-if="currentView === 'login' || currentView === 'register'">
      <div class="auth-box" v-if="currentView === 'login'">
        <h2>Login</h2>
        <input type="email" v-model="emailInput" placeholder="Email" class="form-input" />
        <input type="password" v-model="passInput" placeholder="Password" class="form-input"
          @keyup.enter="handleLogin" />
        <button class="btn-primary" @click="handleLogin">Login</button>
        <p class="auth-link">Belum punya akun? <a href="#" @click.prevent="currentView = 'register'">Daftar</a></p>
      </div>

      <div class="auth-box" v-if="currentView === 'register'">
        <h2>Daftar Akun</h2>
        <input type="text" v-model="regName" placeholder="Nama Lengkap" class="form-input" />
        <input type="text" v-model="regRoll" placeholder="Nomor Kuis" class="form-input" />
        <input type="email" v-model="regEmail" placeholder="Email" class="form-input" />
        <input type="password" v-model="regPass" placeholder="Password" class="form-input"
          @keyup.enter="handleRegister" />
        <button class="btn-primary" @click="handleRegister">Register</button>
        <p class="auth-link">Sudah punya akun? <a href="#" @click.prevent="currentView = 'login'">Login</a></p>
      </div>
    </div>

    <div class="auth-container" v-if="currentView === 'info'">
      <div class="auth-box info-box">
        <h2>Persiapan Ujian</h2>
        <ul>
          <li>Durasi pengerjaan adalah 3 menit.</li>
          <li>Gunakan navigasi di sisi kiri untuk berpindah soal secara acak.</li>
          <li><strong>Semua soal wajib dijawab</strong> untuk dapat menyelesaikan ujian.</li>
          <li>Jika waktu habis, jawaban yang sudah terisi akan terkirim otomatis.</li>
        </ul>
        <button class="btn-primary" style="margin-top:20px;" @click="startQuiz">Mulai Ujian</button>
      </div>
    </div>

    <div class="dicoding-layout" v-if="currentView === 'quiz'">
      <div class="d-topbar">
        <div class="d-timer">{{ formattedTime }}</div>
      </div>
      <div class="d-main">
        <div class="d-sidebar">
          <div class="sidebar-title">Soal kategori: Pemrograman Web</div>
          <div class="d-grid">
            <button v-for="(q, i) in questions" :key="i"
              :class="['nav-btn', { 'active': currentQuestion === i, 'answered': userAnswers[i] && currentQuestion !== i }]"
              @click="goToQuestion(i)">
              {{ i + 1 }}
            </button>
          </div>
        </div>

        <div class="d-content">
          <div class="question-text">
            <span v-html="questions[currentQuestion].question"></span>
          </div>
          <div class="options-container">
            <label class="d-radio" v-for="(opt, index) in questions[currentQuestion].options" :key="index">
              <input type="radio" :name="'q' + currentQuestion" :value="opt" v-model="userAnswers[currentQuestion]">
              <span class="radio-text" v-html="opt"></span>
            </label>
          </div>

          <div class="d-footer">
            <button class="btn-next" v-if="currentQuestion < questions.length - 1" @click="nextQuestion">Selanjutnya
              &rsaquo;</button>
            <button class="btn-submit" v-else @click="finishQuiz(false)">Selesai Ujian</button>
          </div>
        </div>
      </div>
    </div>

    <div class="dicoding-layout result-layout" v-if="currentView === 'result'">
      <div class="d-topbar result-topbar">
        <h2>Hasil Exam</h2>
        <button class="close-btn" @click="restart">&times;</button>
      </div>
      <div class="d-main">
        <div class="d-sidebar result-sidebar">
          <p class="exam-date"><strong>Tanggal Ujian :</strong> <br> {{ quizDate }}</p>
          <div class="score-summary">
            <div class="score-box">
              <p>Total soal</p>
              <h3>{{ questions.length }}</h3>
            </div>
            <div class="score-box">
              <p class="txt-green">Score</p>
              <h3 class="txt-green">{{ score }}</h3>
            </div>
          </div>
          <p class="exam-msg">Selamat! Anda telah menyelesaikan ujian ini.</p>
          <button class="btn-primary" style="margin-top: 30px;" @click="restart">Main Lagi</button>
        </div>

        <div class="d-content result-content">
          <h3 class="category-title">Kategori : Pemrograman Web</h3>

          <div class="review-item" v-for="(q, i) in questions" :key="i">
            <div class="review-header">
              <div class="review-q" v-html="q.question"></div>
              <div class="review-badge">{{ i + 1 }}</div>
            </div>

            <div class="review-options">
              <div v-for="(opt, optIdx) in q.options" :key="optIdx" :class="['review-opt', {
                'correct-border': opt === q.correctAnswer,
                'wrong-border': userAnswers[i] === opt && opt !== q.correctAnswer
              }]">
                <div class="opt-letter">{{ String.fromCharCode(65 + optIdx) }}</div>
                <div class="opt-text" v-html="opt"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>