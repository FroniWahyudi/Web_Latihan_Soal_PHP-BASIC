<?php
require_once 'functions.php';
header('Content-Type: application/json');

function sendResponse($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Metode request harus POST.');
}

// (Sisanya kode PHP Anda untuk memproses subject dan questions)
// Sertakan file functions.php untuk menggunakan fungsi database
require_once 'functions.php';

// Set header untuk mengembalikan respons dalam format JSON
header('Content-Type: application/json');

// Fungsi untuk mengembalikan respons JSON


// Periksa apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Metode request harus POST.');
}

// Periksa apakah data subject dan questions dikirim
if (!isset($_POST['subject']) || !isset($_POST['questions'])) {
    sendResponse('error', 'Data subject atau questions tidak lengkap.');
}

// Ambil dan sanitasi data dari POST
$subjectName = trim($_POST['subject']);
$questions = json_decode($_POST['questions'], true);

// Validasi input
if (empty($subjectName)) {
    sendResponse('error', 'Nama mata kuliah tidak boleh kosong.');
}

if (!is_array($questions) || empty($questions)) {
    sendResponse('error', 'Tidak ada soal yang valid untuk disimpan.');
}

// Ambil ID pengguna (misalnya, dari sesi atau default untuk testing)
session_start();
$created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default ke user_id=1 jika tidak ada sesi

// Periksa apakah user_id valid
$user = getUserById($created_by);
if (!$user) {
    sendResponse('error', 'Pengguna tidak ditemukan.');
}

// Mulai transaksi database
try {
    $pdo->beginTransaction();

    // Periksa apakah mata kuliah sudah ada
    $stmt = $pdo->prepare("SELECT subject_id FROM subjects WHERE name = ?");
    $stmt->execute([$subjectName]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika mata kuliah tidak ada, buat baru
    if (!$subject) {
        if (!createSubject($subjectName, $created_by)) {
            throw new Exception('Gagal membuat mata kuliah baru.');
        }
        // Ambil subject_id yang baru dibuat
        $stmt = $pdo->prepare("SELECT subject_id FROM subjects WHERE name = ?");
        $stmt->execute([$subjectName]);
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $subject_id = $subject['subject_id'];
    $savedCount = 0;

    // Simpan setiap soal
    foreach ($questions as $index => $question) {
        // Validasi data soal
        if (empty($question['question']) || !isset($question['options']) || !isset($question['correct'])) {
            throw new Exception("Soal ke-" . ($index + 1) . ": Data tidak lengkap.");
        }

        if (count($question['options']) < 4 || count($question['options']) > 5) {
            throw new Exception("Soal ke-" . ($index + 1) . ": Jumlah opsi harus antara 4 dan 5.");
        }

        if (array_filter($question['options'], fn($opt) => empty(trim($opt)))) {
            throw new Exception("Soal ke-" . ($index + 1) . ": Ada opsi jawaban yang kosong.");
        }

        $correct_option = strtoupper(chr(65 + $question['correct'])); // Konversi indeks (0=A, 1=B, dst.)
        if (!in_array($correct_option, ['A', 'B', 'C', 'D', 'E'])) {
            throw new Exception("Soal ke-" . ($index + 1) . ": Jawaban benar tidak valid.");
        }

        // Siapkan data untuk createQuestion
        $option_e = isset($question['options'][4]) ? $question['options'][4] : null;

        // Simpan soal ke database
        $result = createQuestion(
            $subject_id,
            $question['question'],
            $question['options'][0],
            $question['options'][1],
            $question['options'][2],
            $question['options'][3],
            $correct_option,
            $created_by,
            $option_e
        );

        if (!$result) {
            throw new Exception("Gagal menyimpan soal ke-" . ($index + 1) . ".");
        }

        $savedCount++;
    }

    // Commit transaksi
    $pdo->commit();
    sendResponse('success', "$savedCount soal berhasil disimpan ke database.", ['saved_count' => $savedCount]);

} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $pdo->rollBack();
    sendResponse('error', 'Terjadi kesalahan: ' . $e->getMessage());
} catch (PDOException $e) {
    // Rollback transaksi jika ada error database
    $pdo->rollBack();
    sendResponse('error', 'Kesalahan database: ' . $e->getMessage());
}
?>