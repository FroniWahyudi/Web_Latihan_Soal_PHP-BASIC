<?php
include 'functions.php';
session_start(); // Memulai sesi

if (isset($_POST['email']) && isset($_POST['password'])) {
    error_log("Menerima email: " . $_POST['email']); // Debugging
    error_log("Menerima password: " . $_POST['password']); // Debugging
    $result = loginUser($_POST['email'], $_POST['password']);
    error_log("Hasil login: " . print_r($result, true)); // Debugging
    if ($result['status'] == 'success') {
        $_SESSION['user_id'] = $result['user']['user_id']; // Simpan user_id ke sesi
        $_SESSION['name'] = $result['user']['name']; // Simpan nama pengguna ke sesi
        header('Location: dashboard.php?login=success');
        exit();
    } else {
        error_log("Login gagal: " . $result['message']); // Debugging
        header('Location: login.php?login=error');
        exit();
    }
} else {
    error_log("Email atau password tidak diterima");
    header('Location: login.php');
    exit();
}
?>
