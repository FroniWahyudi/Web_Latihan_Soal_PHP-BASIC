<?php
require_once 'functions.php'; // Menginclude file functions.php

// Asumsi subject_id dikirim melalui parameter GET
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 1;
$questions = getQuestionsBySubject($subject_id);
$current_question_index = isset($_GET['index']) ? (int)$_GET['index'] : 0;
$current_question_index = max(0, min($current_question_index, count($questions) - 1));

// Mengambil data soal berdasarkan index
$current_question = !empty($questions) ? $questions[$current_question_index] : null;

// Inisialisasi correct_option jika belum ada
$correct_option = $current_question && isset($current_question['correct_option']) ? $current_question['correct_option'] : 'A';

// Proses form submission untuk menyimpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    $question_id = $_POST['question_id'];
    $question_text = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $selected_correct = $_POST['correct_answer']; // Nilai dari dropdown
    $created_by = 1; // Asumsi user ID sementara, sesuaikan dengan sistem autentikasi

    // Menambahkan (correct) ke opsi yang dipilih berdasarkan dropdown
    $option_a = ($selected_correct === 'A') ? $option_a . ' (correct)' : $option_a;
    $option_b = ($selected_correct === 'B') ? $option_b . ' (correct)' : $option_b;
    $option_c = ($selected_correct === 'C') ? $option_c . ' (correct)' : $option_c;
    $option_d = ($selected_correct === 'D') ? $option_d . ' (correct)' : $option_d;

    // Validasi input
    if (!empty($question_text) && !empty($option_a) && !empty($option_b) && !empty($option_c) && !empty($option_d)) {
        // Update soal menggunakan fungsi updateQuestion dari functions.php
        $success = updateQuestion($question_id, $subject_id, $question_text, $option_a, $option_b, $option_c, $option_d, $created_by);
        if ($success) {
            header("Location: edit_soal.php?subject_id=$subject_id&index=$current_question_index&saved=1");
            exit;
        } else {
            $error = "Gagal menyimpan perubahan soal.";
        }
    } else {
        $error = "Semua kolom harus diisi.";
    }
}

// Mengambil informasi mata kuliah
$subject = getSubjectById($subject_id);
$subject_name = $subject ? $subject['name'] : 'Mata Kuliah Tidak Ditemukan';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Soal Ujian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-sans bg-gray-100">
    <main class="px-4 pb-8">
        <div class="max-w-7xl mx-auto">
            <!-- Page Title and Back Button -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Editor Soal Ujian</h1>
                    <p class="text-gray-600">Mata Kuliah: <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($subject_name); ?></span></p>
                </div>
                <a 
                    href="dashboard.php"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2 hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <span>←</span>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>

            <!-- Question List -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Daftar Soal</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($questions as $index => $q): ?>
                        <a 
                            href="edit_soal.php?subject_id=<?php echo $subject_id; ?>&index=<?php echo $index; ?>"
                            class="question-btn px-4 py-2 rounded-lg border-2 <?php echo $index === $current_question_index ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 text-gray-700 hover:border-indigo-300 hover:bg-indigo-50'; ?> transition-all duration-200"
                        >
                            Soal <?php echo $index + 1; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column: Edit Form -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                        <span class="mr-2">✏️</span>
                        Edit Soal
                    </h2>

                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="question_id" value="<?php echo $current_question ? htmlspecialchars($current_question['question_id']) : ''; ?>">
                        <!-- Question Field -->
                        <div>
                            <label for="question" class="block text-sm font-medium text-gray-700 mb-2">
                                Pertanyaan
                            </label>
                            <textarea 
                                id="question" 
                                name="question"
                                rows="3" 
                                class="w-full border-gray-300 rounded-lg p-3 focus:ring-indigo-300 focus:border-indigo-300 transition-all duration-200 focus:-translate-y-px focus:shadow-md"
                                placeholder="Masukkan pertanyaan soal..."
                            ><?php echo $current_question ? htmlspecialchars($current_question['question_text']) : ''; ?></textarea>
                        </div>

                        <!-- Answer Options -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-medium text-gray-700">Pilihan Jawaban</h3>
                            
                            <!-- Option A -->
                            <div>
                                <label for="option_a" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pilihan A
                                </label>
                                <input 
                                    type="text" 
                                    id="option_a" 
                                    name="option_a"
                                    class="w-full border-gray-300 rounded-lg bg-gray-50 p-2 focus:ring-indigo-300 focus:border-indigo-300 transition-all duration-200 focus:-translate-y-px focus:shadow-md"
                                    value="<?php echo $current_question ? str_replace(' (correct)', '', htmlspecialchars($current_question['option_a'])) : ''; ?>"
                                >
                            </div>

                            <!-- Option B -->
                            <div>
                                <label for="option_b" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pilihan B
                                </label>
                                <input 
                                    type="text" 
                                    id="option_b" 
                                    name="option_b"
                                    class="w-full border-gray-300 rounded-lg bg-gray-50 p-2 focus:ring-indigo-300 focus:border-indigo-300 transition-all duration-200 focus:-translate-y-px focus:shadow-md"
                                    value="<?php echo $current_question ? str_replace(' (correct)', '', htmlspecialchars($current_question['option_b'])) : ''; ?>"
                                >
                            </div>

                            <!-- Option C -->
                            <div>
                                <label for="option_c" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pilihan C
                                </label>
                                <input 
                                    type="text" 
                                    id="option_c" 
                                    name="option_c"
                                    class="w-full border-gray-300 rounded-lg bg-gray-50 p-2 focus:ring-indigo-300 focus:border-indigo-300 transition-all duration-200 focus:-translate-y-px focus:shadow-md"
                                    value="<?php echo $current_question ? str_replace(' (correct)', '', htmlspecialchars($current_question['option_c'])) : ''; ?>"
                                >
                            </div>

                            <!-- Option D -->
                            <div>
                                <label for="option_d" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pilihan D
                                </label>
                                <input 
                                    type="text" 
                                    id="option_d" 
                                    name="option_d"
                                    class="w-full border-gray-300 rounded-lg bg-gray-50 p-2 focus:ring-indigo-300 focus:border-indigo-300 transition-all duration-200 focus:-translate-y-px focus:shadow-md"
                                    value="<?php echo $current_question ? str_replace(' (correct)', '', htmlspecialchars($current_question['option_d'])) : ''; ?>"
                                >
                            </div>
                        </div>

                        <!-- Correct Answer Dropdown -->
                        <div>
                            <label for="correct_answer" class="block text-sm font-medium text-gray-700 mb-2">
                                Jawaban Benar
                            </label>
                            <select 
                                id="correct_answer" 
                                name="correct_answer"
                                class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-300 focus:border-indigo-300 transition-all duration-200"
                            >
                                <option value="A" <?php echo $correct_option === 'A' ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo $correct_option === 'B' ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo $correct_option === 'C' ? 'selected' : ''; ?>>C</option>
                                <option value="D" <?php echo $correct_option === 'D' ? 'selected' : ''; ?>>D</option>
                            </select>
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit" 
                                name="save_question"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2 hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                <span>💾</span>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Preview -->
                <div class="bg-white rounded-xl shadow-lg p-6 animate-fadeIn">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                        <span class="mr-2">👀</span>
                        Preview Soal
                    </h2>

                    <!-- Question Preview -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-indigo-600 mb-2">Soal</label>
                        <p id="previewQuestion" class="font-semibold text-gray-800 text-lg leading-relaxed">
                            <?php echo $current_question ? htmlspecialchars($current_question['question_text']) : 'Masukkan pertanyaan soal...'; ?>
                        </p>
                    </div>

                    <!-- Answer Options Preview -->
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-indigo-600 mb-3">Pilihan Jawaban</label>
                        
                        <!-- Preview Option A -->
                        <div id="previewOptionA" class="<?php echo $correct_option === 'A' ? 'bg-gradient-to-r from-green-500 to-green-700 text-white p-3 rounded-lg border-green-500 border' : 'border-gray-200 bg-gray-50 p-3 rounded-lg border'; ?> transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <span><strong>A.</strong> <span class="ml-2"><?php echo $current_question ? htmlspecialchars($current_question['option_a']) : 'Pilihan A'; ?></span></span>
                                <?php if ($correct_option === 'A'): ?>
                                    <span class="text-sm font-medium">✓ Benar</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Preview Option B -->
                        <div id="previewOptionB" class="<?php echo $correct_option === 'B' ? 'bg-gradient-to-r from-green-500 to-green-700 text-white p-3 rounded-lg border-green-500 border' : 'border-gray-200 bg-gray-50 p-3 rounded-lg border'; ?> transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <span><strong>B.</strong> <span class="ml-2"><?php echo $current_question ? htmlspecialchars($current_question['option_b']) : 'Pilihan B'; ?></span></span>
                                <?php if ($correct_option === 'B'): ?>
                                    <span class="text-sm font-medium">✓ Benar</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Preview Option C -->
                        <div id="previewOptionC" class="<?php echo $correct_option === 'C' ? 'bg-gradient-to-r from-green-500 to-green-700 text-white p-3 rounded-lg border-green-500 border' : 'border-gray-200 bg-gray-50 p-3 rounded-lg border'; ?> transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <span><strong>C.</strong> <span class="ml-2"><?php echo $current_question ? htmlspecialchars($current_question['option_c']) : 'Pilihan C'; ?></span></span>
                                <?php if ($correct_option === 'C'): ?>
                                    <span class="text-sm font-medium">✓ Benar</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Preview Option D -->
                        <div id="previewOptionD" class="<?php echo $correct_option === 'D' ? 'bg-gradient-to-r from-green-500 to-green-700 text-white p-3 rounded-lg border-green-500 border' : 'border-gray-200 bg-gray-50 p-3 rounded-lg border'; ?> transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <span><strong>D.</strong> <span class="ml-2"><?php echo $current_question ? htmlspecialchars($current_question['option_d']) : 'Pilihan D'; ?></span></span>
                                <?php if ($correct_option === 'D'): ?>
                                    <span class="text-sm font-medium">✓ Benar</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Question Navigation -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <a href="edit_soal.php?subject_id=<?php echo $subject_id; ?>&index=<?php echo max(0, $current_question_index - 1); ?>" 
                               class="nav-prev text-indigo-600 hover:text-indigo-700 font-medium text-sm transition-colors <?php echo $current_question_index === 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                ← Soal Sebelumnya
                            </a>
                            <span class="question-counter text-sm text-gray-500">Soal <?php echo $current_question_index + 1; ?> dari <?php echo count($questions); ?></span>
                            <a href="edit_soal.php?subject_id=<?php echo $subject_id; ?>&index=<?php echo min(count($questions) - 1, $current_question_index + 1); ?>" 
                               class="nav-next text-indigo-600 hover:text-indigo-700 font-medium text-sm transition-colors <?php echo $current_question_index === count($questions) - 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                Soal Berikutnya →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Modal (Hidden initially) -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 <?php echo isset($_GET['saved']) ? '' : 'hidden'; ?>">
        <div class="bg-white rounded-2xl p-8 max-w-sm mx-4 text-center transform transition-all duration-300">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Soal Berhasil Disimpan!</h3>
            <p class="text-gray-600 mb-6">Perubahan soal telah tersimpan ke database</p>
            <a href="edit_soal.php?subject_id=<?php echo $subject_id; ?>&index=<?php echo $current_question_index; ?>" 
               class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-2 px-4 rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-200">
                Tutup
            </a>
        </div>
    </div>

    <script>
        function updatePreview() {
            const question = document.getElementById('question').value;
            const optionA = document.getElementById('option_a').value;
            const optionB = document.getElementById('option_b').value;
            const optionC = document.getElementById('option_c').value;
            const optionD = document.getElementById('option_d').value;
            const correctAnswer = document.getElementById('correct_answer').value;

            document.getElementById('previewQuestion').textContent = question || 'Masukkan pertanyaan soal...';

            const options = ['A', 'B', 'C', 'D'];
            const optionValues = [optionA, optionB, optionC, optionD];

            options.forEach((letter, index) => {
                const previewElement = document.getElementById(`previewOption${letter}`);
                previewElement.className = 'border-gray-200 bg-gray-50 p-3 rounded-lg border transition-all duration-200';
                previewElement.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span><strong>${letter}.</strong> <span class="ml-2">${optionValues[index] || `Pilihan ${letter}`}</span></span>
                    </div>
                `;

                if (letter === correctAnswer) {
                    previewElement.className = 'bg-gradient-to-r from-green-500 to-green-700 text-white p-3 rounded-lg border-green-500 border transition-all duration-200';
                    previewElement.innerHTML = `
                        <div class="flex items-center justify-between">
                            <span><strong>${letter}.</strong> <span class="ml-2">${optionValues[index] || `Pilihan ${letter}`}</span></span>
                            <span class="text-sm font-medium">✓ Benar</span>
                        </div>
                    `;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
        });
    </script>

    <style>
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
</body>
</html>