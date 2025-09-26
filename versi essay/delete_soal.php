<?php
// delete_soal.php - Hapus Soal (Admin)
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
include 'config.php';

$id = $_GET['id'];
$mk_id = $_GET['mk_id'];

$sql = "DELETE FROM soal WHERE id = $id";
if ($conn->query($sql)) {
    header('Location: list_soal.php?mk_id=' . $mk_id);
    exit;
} else {
    echo "Error: " . $conn->error;
}
?>