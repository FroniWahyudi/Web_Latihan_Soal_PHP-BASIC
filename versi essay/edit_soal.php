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
            $error_message = "Error: " . $conn->error;
        }
    } else {
        $error_message = "Field tidak boleh kosong.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Soal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="card bg-white p-8 rounded-2xl shadow-xl max-w-lg w-full">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Edit Soal</h2>
        
        <!-- Form -->
        <form method="POST" class="space-y-6">
            <!-- Pertanyaan Field -->
            <div>
                <label for="pertanyaan" class="block text-sm font-semibold text-gray-700 mb-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pertanyaan
                    </div>
                </label>
                <textarea 
                    id="pertanyaan"
                    name="pertanyaan" 
                    required
                    class="textarea-focus w-full h-32 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none resize-none transition-all duration-300 text-gray-700"
                    placeholder="Masukkan pertanyaan soal..."
                ><?php echo htmlspecialchars($row['pertanyaan']); ?></textarea>
            </div>

            <!-- Kunci Jawaban Field -->
            <div>
                <label for="kunci" class="block text-sm font-semibold text-gray-700 mb-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2L4.257 9.257a6 6 0 017.743-7.743L15 5l2 2zM9 6h.01M11 14h.01"></path>
                        </svg>
                        Kunci Jawaban
                    </div>
                </label>
                <textarea 
                    id="kunci"
                    name="kunci" 
                    required
                    class="textarea-focus w-full h-32 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none resize-none transition-all duration-300 text-gray-700"
                    placeholder="Masukkan kunci jawaban..."
                ><?php echo htmlspecialchars($row['kunci_jawaban']); ?></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6">
                <button 
                    type="submit" 
                    class="btn-primary flex-1 bg-gradient-to-r from-indigo-500 to-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transition-all duration-300 flex items-center justify-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Update Soal
                </button>
                
                <a 
                    href="list_soal.php?mk_id=<?php echo htmlspecialchars($mk_id); ?>" 
                    class="back-link flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-all duration-300 flex items-center justify-center text-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </form>

        <!-- Success/Error Messages -->
        <?php if (isset($error_message)): ?>
        <div class="mt-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Add subtle animations on load
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.max(128, this.scrollHeight) + 'px';
            });
        });
    </script>
</body>
</html>