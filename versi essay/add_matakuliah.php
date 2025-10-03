<?php
// add_matakuliah.php - Tambah Mata Kuliah (Admin)
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    if (!empty($nama)) {
        $sql = "INSERT INTO mata_kuliah (nama) VALUES ('$nama')";
        if ($conn->query($sql)) {
            header('Location: index.php');
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Nama tidak boleh kosong.";
    }
}
?>