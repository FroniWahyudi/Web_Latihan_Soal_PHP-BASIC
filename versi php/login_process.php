<?php
include 'functions.php';
session_start(); // Tambahkan ini di awal

if (isset($_POST['email']) && isset($_POST['password'])) {
    $result = loginUser($_POST['email'], $_POST['password']);
    if ($result['status'] == 'success') {
        $_SESSION['user_id'] = $result['user']['user_id']; // Simpan user_id ke sesi
        header('Location: dashboard.php?login=success');
        exit();
    } else {
        header('Location: login.php?login=error');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>