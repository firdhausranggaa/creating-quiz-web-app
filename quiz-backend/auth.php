<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';
header("Content-Type: application/json");

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $nama = $_POST['nama'];
    $nomor = $_POST['nomor'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, nomor_kuis, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $nomor, $email, $password);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registrasi berhasil"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email sudah terdaftar"]);
    }
    $stmt->close();
} elseif ($action === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nama_lengkap, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            echo json_encode(["status" => "success", "user_id" => $user['id'], "nama" => $user['nama_lengkap']]);
        } else {
            echo json_encode(["status" => "error", "message" => "Password salah"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email tidak ditemukan"]);
    }
    $stmt->close();
}
$conn->close();
