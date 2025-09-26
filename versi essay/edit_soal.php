<?php
// edit_soal.php - Edit Soal (Admin)
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
include 'config.php';

$id = $_GET['id'];
$mk_id = $_GET['mk_id'];

$sql = "SELECT * FROM soal WHERE id = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $kunci = mysqli_real_escape_string($conn, $_POST['kunci']);
    if (!empty($pertanyaan) && !empty($kunci)) {
        $sql = "UPDATE soal SET pertanyaan = '$pertanyaan', kunci_jawaban = '$kunci' WHERE id = $id";
        if ($conn->query($sql)) {
            header('Location: list_soal.php?mk_id=' . $mk_id);
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Field tidak boleh kosong.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Soal</title>
    <style>
        form { max-width: 500px; margin: auto; }
        textarea { width: 100%; height: 100px; }
    </style>
</head>
<body>
    <h2>Edit Soal</h2>
    <form method="POST">
        <textarea name="pertanyaan" required><?php echo $row['pertanyaan']; ?></textarea>
        <textarea name="kunci" required><?php echo $row['kunci_jawaban']; ?></textarea>
        <button type="submit">Update</button>
    </form>
    <a href="list_soal.php?mk_id=<?php echo $mk_id; ?>">Kembali</a>
</body>
</html>