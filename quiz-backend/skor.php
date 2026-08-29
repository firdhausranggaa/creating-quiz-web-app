<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';
header("Content-Type: application/json");

$user_id = $_POST['user_id'];
$skor = $_POST['skor'];

$stmt = $conn->prepare("INSERT INTO leaderboard (user_id, skor) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $skor);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Skor berhasil disimpan"]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal menyimpan skor"]);
}
$stmt->close();
$conn->close();
