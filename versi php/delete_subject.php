<?php
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'])) {
    $subjectId = (int)$_POST['subject_id']; // Konversi ke integer untuk keamanan
    if (deleteSubjectWithQuestions($subjectId)) {
        echo json_encode(['status' => 'success', 'message' => 'Mata kuliah dan soalnya berhasil dihapus']);
    } else {
        $errorInfo = $pdo->errorInfo();
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus mata kuliah dan soalnya: ' . ($errorInfo[2] ?? 'Unknown error')]);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid']);
    exit;
}
?>