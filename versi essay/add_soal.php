<?php
// add_soal.php - Tambah Soal (Admin)
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
include 'config.php';

$mk_id = isset($_GET['mk_id']) ? (int)$_GET['mk_id'] : 0;

// Fetch mata kuliah name
$mata_kuliah_name = "Unknown";
if ($mk_id > 0) {
    $sql = "SELECT nama FROM mata_kuliah WHERE id = $mk_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $mata_kuliah_name = htmlspecialchars($row['nama']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $kunci = mysqli_real_escape_string($conn, $_POST['kunci']);
    $gambar_path = NULL;

    // Handle image upload
    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "Uploads/";
        // Create uploads directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $gambar = $_FILES['gambar'];
        $file_extension = strtolower(pathinfo($gambar['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        // Validate image
        if (!in_array($file_extension, $allowed_extensions)) {
            $error_message = "Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.";
        } elseif ($gambar['size'] > $max_file_size) {
            $error_message = "Ukuran gambar terlalu besar. Maksimum 5MB.";
        } else {
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($gambar['tmp_name'], $target_file)) {
                $gambar_path = $target_file;
            } else {
                $error_message = "Gagal mengunggah gambar.";
            }
        }
    }

    // Proceed with inserting data if no upload errors
    if (!isset($error_message) && !empty($pertanyaan) && !empty($kunci)) {
        $gambar_sql = $gambar_path ? "'$gambar_path'" : "NULL";
        $sql = "INSERT INTO soal (mata_kuliah_id, pertanyaan, kunci_jawaban, gambar) VALUES ($mk_id, '$pertanyaan', '$kunci', $gambar_sql)";
        if ($conn->query($sql)) {
            $success_message = "Soal berhasil ditambahkan!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    } elseif (!isset($error_message)) {
        $error_message = "Harap lengkapi semua field yang wajib diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
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
        
        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 15%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 60%;
            right: 15%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 2s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-25px) rotate(180deg);
            }
        }
        
        .textarea-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        }
        
        .char-counter {
            transition: color 0.3s ease;
        }
        
        .char-counter.warning {
            color: #f59e0b;
        }
        
        .char-counter.danger {
            color: #ef4444;
        }
        
        .preview-card {
            background: linear-gradient(145deg, #f8fafc, #e2e8f0);
            border-left: 4px solid #667eea;
        }

        .file-input-label {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background-color: #f3f4f6;
        }

        .preview-image {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
        }

        .drop-zone {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f9fafb;
            transition: all 0.3s ease;
        }

        .drop-zone.dragover {
            border-color: #667eea;
            background-color: #e0e7ff;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Header Navigation -->
    <nav class="glass-effect p-4 mb-6">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button onclick="goBack()" class="flex items-center text-gray-700 hover:text-purple-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Dashboard
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="text-gray-700 font-medium">Admin</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 pb-8">
        <!-- Page Header -->
        <div class="glass-effect rounded-2xl p-6 mb-6 fade-in">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-3xl font-bold text-gray-800">Tambah Soal Baru</h1>
                    <p class="text-gray-600">Mata Kuliah: <span id="mataKuliahName" class="font-semibold text-purple-600"><?php echo $mata_kuliah_name; ?></span></p>
                </div>
            </div>
            
            <!-- Progress Indicator -->
            <div class="flex items-center space-x-4 mt-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                    <span class="ml-2 text-sm text-gray-700">Tulis Pertanyaan</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 rounded">
                    <div id="progressBar" class="h-1 bg-gradient-to-r from-purple-600 to-blue-600 rounded transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                    <span class="ml-2 text-sm text-gray-600">Preview & Simpan</span>
                </div>
            </div>
        </div>

        <!-- Form and Preview Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Form Section -->
            <div class="glass-effect rounded-2xl p-6 fade-in">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Form Soal
                </h3>

                <form id="addSoalForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Pertanyaan Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pertanyaan Soal
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <textarea 
                                name="pertanyaan" 
                                id="pertanyaan"
                                class="textarea-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300 bg-white/80 resize-none"
                                placeholder="Masukkan pertanyaan soal di sini..."
                                rows="6"
                                maxlength="1000"
                                required
                            ><?php echo isset($_POST['pertanyaan']) ? htmlspecialchars($_POST['pertanyaan']) : ''; ?></textarea>
                            <div class="absolute bottom-2 right-2 text-xs char-counter" id="pertanyaanCounter">
                                0/1000
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Tulis pertanyaan yang jelas dan mudah dipahami
                        </p>
                    </div>

                    <!-- Image Upload Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gambar Soal (Opsional)
                        </label>
                        <div class="relative">
                            <input 
                                type="file" 
                                name="gambar" 
                                id="gambar"
                                accept="image/jpeg,image/png,image/gif"
                                class="hidden"
                                onchange="updateImagePreview(event)"
                            >
                            <div id="dropZone" class="drop-zone">
                                <p class="text-gray-500">Seret dan lepas gambar di sini, atau klik untuk memilih file, atau tekan Ctrl+V untuk menempelkan gambar dari clipboard</p>
                                <label for="gambar" class="file-input-label mt-2 inline-block px-4 py-2 border border-gray-300 rounded-lg bg-white/80 text-gray-500 cursor-pointer hover:bg-gray-100">
                                    <span id="fileName">Pilih file gambar...</span>
                                    <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                </label>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Format: JPG, PNG, GIF. Maksimum 5MB.
                        </p>
                    </div>

                    <!-- Kunci Jawaban Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kunci Jawaban
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <textarea 
                                name="kunci" 
                                id="kunci"
                                class="textarea-focus w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300 bg-white/80 resize-none"
                                placeholder="Masukkan kunci jawaban yang benar..."
                                rows="4"
                                maxlength="500"
                                required
                            ><?php echo isset($_POST['kunci']) ? htmlspecialchars($_POST['kunci']) : ''; ?></textarea>
                            <div class="absolute bottom-2 right-2 text-xs char-counter" id="kunciCounter">
                                0/500
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Berikan jawaban yang lengkap dan akurat
                        </p>
                    </div>

                    <!-- Error Message -->
                    <?php if (isset($error_message)): ?>
                    <div id="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span id="errorText"><?php echo htmlspecialchars($error_message); ?></span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span id="errorText">Harap lengkapi semua field yang wajib diisi</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Success Message -->
                    <?php if (isset($success_message)): ?>
                    <div id="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo htmlspecialchars($success_message); ?></span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Soal berhasil ditambahkan!</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <button 
                            type="submit" 
                            id="submitBtn"
                            class="flex-1 bg-gradient-to-r from-purple-600 to-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300 shadow-lg flex items-center justify-center"
                        >
                            <svg id="submitIcon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span id="submitText">Tambah Soal</span>
                            <svg id="loadingIcon" class="hidden animate-spin -mr-1 ml-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        
                        <button 
                            type="button" 
                            id="resetBtn"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-300 flex items-center justify-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div class="glass-effect rounded-2xl p-6 fade-in">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview Soal
                </h3>

                <!-- Preview Card -->
                <div class="preview-card rounded-xl p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                ?
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-600">Soal Baru</span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Pertanyaan:</h4>
                        <div id="previewPertanyaan" class="text-gray-800 bg-white/50 rounded-lg p-4 min-h-[100px] border-2 border-dashed border-gray-300">
                            <span class="text-gray-400 italic">Pertanyaan akan muncul di sini saat Anda mengetik...</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Gambar Soal:</h4>
                        <div id="previewGambar" class="bg-white/50 rounded-lg p-4 min-h-[100px] border-2 border-dashed border-gray-300 flex items-center justify-center">
                            <span class="text-gray-400 italic">Gambar akan muncul di sini saat Anda memilih...</span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Kunci Jawaban:</h4>
                        <div id="previewKunci" class="text-gray-800 bg-green-50 rounded-lg p-4 min-h-[80px] border-2 border-dashed border-green-300">
                            <span class="text-gray-400 italic">Kunci jawaban akan muncul di sini saat Anda mengetik...</span>
                        </div>
                    </div>
                </div>

                <!-- Tips Section -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-blue-800 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Tips Membuat Soal
                    </h4>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• Gunakan bahasa yang jelas dan mudah dipahami</li>
                        <li>• Pastikan pertanyaan tidak ambigu</li>
                        <li>• Berikan kunci jawaban yang lengkap</li>
                        <li>• Gunakan gambar yang relevan untuk mendukung pertanyaan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get form elements
        const pertanyaanTextarea = document.getElementById('pertanyaan');
        const kunciTextarea = document.getElementById('kunci');
        const gambarInput = document.getElementById('gambar');
        const addSoalForm = document.getElementById('addSoalForm');
        const submitBtn = document.getElementById('submitBtn');
        const resetBtn = document.getElementById('resetBtn');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const dropZone = document.getElementById('dropZone');

        // Get preview elements
        const previewPertanyaan = document.getElementById('previewPertanyaan');
        const previewKunci = document.getElementById('previewKunci');
        const previewGambar = document.getElementById('previewGambar');
        const progressBar = document.getElementById('progressBar');

        // Character counters
        const pertanyaanCounter = document.getElementById('pertanyaanCounter');
        const kunciCounter = document.getElementById('kunciCounter');
        const fileNameSpan = document.getElementById('fileName');

        // Update character counter
        function updateCharCounter(textarea, counter, maxLength) {
            const currentLength = textarea.value.length;
            counter.textContent = `${currentLength}/${maxLength}`;
            
            counter.classList.remove('warning', 'danger');
            if (currentLength > maxLength * 0.8) {
                counter.classList.add('warning');
            }
            if (currentLength > maxLength * 0.95) {
                counter.classList.add('danger');
            }
        }

        // Update image preview
        function updateImagePreview(event) {
            let file;
            if (event.type === 'change') {
                file = event.target.files[0];
            } else if (event.type === 'drop') {
                file = event.dataTransfer.files[0];
            } else if (event.type === 'paste') {
                file = event.clipboardData.files[0];
            }

            if (file) {
                // Validate file type
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(fileExtension)) {
                    errorMessage.classList.remove('hidden');
                    document.getElementById('errorText').textContent = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.';
                    return;
                }

                // Validate file size
                const maxFileSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxFileSize) {
                    errorMessage.classList.remove('hidden');
                    document.getElementById('errorText').textContent = 'Ukuran gambar terlalu besar. Maksimum 5MB.';
                    return;
                }

                fileNameSpan.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewGambar.innerHTML = `<img src="${e.target.result}" class="preview-image" alt="Preview Gambar Soal">`;
                    previewGambar.classList.remove('text-gray-400', 'italic');
                };
                reader.readAsDataURL(file);

                // Update the file input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                gambarInput.files = dataTransfer.files;
            } else {
                fileNameSpan.textContent = 'Pilih file gambar...';
                previewGambar.innerHTML = '<span class="text-gray-400 italic">Gambar akan muncul di sini saat Anda memilih...</span>';
            }
            updateProgress();
        }

        // Update progress bar
        function updateProgress() {
            const pertanyaanFilled = pertanyaanTextarea.value.trim().length > 0;
            const kunciFilled = kunciTextarea.value.trim().length > 0;
            const gambarFilled = gambarInput.files.length > 0;
            
            let progress = 0;
            if (pertanyaanFilled) progress += 40;
            if (kunciFilled) progress += 40;
            if (gambarFilled) progress += 20;
            
            progressBar.style.width = progress + '%';
        }

        // Update preview
        function updatePreview() {
            const pertanyaanText = pertanyaanTextarea.value.trim();
            const kunciText = kunciTextarea.value.trim();

            // Update pertanyaan preview
            if (pertanyaanText) {
                previewPertanyaan.innerHTML = pertanyaanText.replace(/\n/g, '<br>');
                previewPertanyaan.classList.remove('text-gray-400', 'italic');
                previewPertanyaan.classList.add('text-gray-800');
            } else {
                previewPertanyaan.innerHTML = '<span class="text-gray-400 italic">Pertanyaan akan muncul di sini saat Anda mengetik...</span>';
            }

            // Update kunci preview
            if (kunciText) {
                previewKunci.innerHTML = kunciText.replace(/\n/g, '<br>');
                previewKunci.classList.remove('text-gray-400', 'italic');
                previewKunci.classList.add('text-gray-800');
            } else {
                previewKunci.innerHTML = '<span class="text-gray-400 italic">Kunci jawaban akan muncul di sini saat Anda mengetik...</span>';
            }
        }

        // Handle drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            updateImagePreview(e);
        });

        // Handle paste event
        document.addEventListener('paste', function(e) {
            if (e.target !== pertanyaanTextarea && e.target !== kunciTextarea) {
                updateImagePreview(e);
            }
        });

        // Event listeners for real-time updates
        pertanyaanTextarea.addEventListener('input', function() {
            updateCharCounter(this, pertanyaanCounter, 1000);
            updatePreview();
            updateProgress();
        });

        kunciTextarea.addEventListener('input', function() {
            updateCharCounter(this, kunciCounter, 500);
            updatePreview();
            updateProgress();
        });

        gambarInput.addEventListener('change', updateImagePreview);

        // Reset form
        resetBtn.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin mengosongkan form?')) {
                addSoalForm.reset();
                updateCharCounter(pertanyaanTextarea, pertanyaanCounter, 1000);
                updateCharCounter(kunciTextarea, kunciCounter, 500);
                updateImagePreview({ target: { files: [] } });
                updatePreview();
                updateProgress();
                errorMessage.classList.add('hidden');
                successMessage.classList.add('hidden');
            }
        });

        // Form submission
        addSoalForm.addEventListener('submit', function(e) {
            const pertanyaan = pertanyaanTextarea.value.trim();
            const kunci = kunciTextarea.value.trim();
            const gambar = gambarInput.files[0];
            
            // Client-side validation
            if (!pertanyaan || !kunci) {
                e.preventDefault();
                errorMessage.classList.remove('hidden');
                document.getElementById('errorText').textContent = 'Harap lengkapi semua field yang wajib diisi';
                return;
            }
            
            if (pertanyaan.length < 10) {
                e.preventDefault();
                errorMessage.classList.remove('hidden');
                document.getElementById('errorText').textContent = 'Pertanyaan terlalu pendek (minimal 10 karakter)';
                return;
            }
            
            if (kunci.length < 5) {
                e.preventDefault();
                errorMessage.classList.remove('hidden');
                document.getElementById('errorText').textContent = 'Kunci jawaban terlalu pendek (minimal 5 karakter)';
                return;
            }

            if (gambar) {
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                const maxFileSize = 5 * 1024 * 1024; // 5MB
                const fileExtension = gambar.name.split('.').pop().toLowerCase();
                
                if (!allowedExtensions.includes(fileExtension)) {
                    e.preventDefault();
                    errorMessage.classList.remove('hidden');
                    document.getElementById('errorText').textContent = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau GIF.';
                    return;
                }
                
                if (gambar.size > maxFileSize) {
                    e.preventDefault();
                    errorMessage.classList.remove('hidden');
                    document.getElementById('errorText').textContent = 'Ukuran gambar terlalu besar. Maksimum 5MB.';
                    return;
                }
            }
            
            // Show loading state
            submitBtn.disabled = true;
            document.getElementById('submitText').textContent = 'Menyimpan...';
            document.getElementById('submitIcon').classList.add('hidden');
            document.getElementById('loadingIcon').classList.remove('hidden');
            
            // Let the form submit to PHP
            setTimeout(() => {
                <?php if (isset($success_message)): ?>
                document.getElementById('submitText').textContent = 'Berhasil!';
                document.getElementById('loadingIcon').classList.add('hidden');
                document.getElementById('submitIcon').innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                `;
                document.getElementById('submitIcon').classList.remove('hidden');
                submitBtn.classList.remove('from-purple-600', 'to-blue-600');
                submitBtn.classList.add('from-green-500', 'to-green-600');
                
                setTimeout(() => {
                    if (confirm('Soal berhasil ditambahkan! Apakah Anda ingin menambah soal lagi?')) {
                        addSoalForm.reset();
                        updateCharCounter(pertanyaanTextarea, pertanyaanCounter, 1000);
                        updateCharCounter(kunciTextarea, kunciCounter, 500);
                        updateImagePreview({ target: { files: [] } });
                        updatePreview();
                        updateProgress();
                        successMessage.classList.add('hidden');
                        
                        submitBtn.disabled = false;
                        document.getElementById('submitText').textContent = 'Tambah Soal';
                        document.getElementById('submitIcon').innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        `;
                        submitBtn.classList.remove('from-green-500', 'to-green-600');
                        submitBtn.classList.add('from-purple-600', 'to-blue-600');
                    } else {
                        window.location.href = 'list_soal.php?mk_id=<?php echo $mk_id; ?>';
                    }
                }, 2000);
                <?php endif; ?>
            }, 1000);
        });

        // Go back function
        function goBack() {
            if (pertanyaanTextarea.value.trim() || kunciTextarea.value.trim() || gambarInput.files.length > 0) {
                if (confirm('Anda memiliki perubahan yang belum disimpan. Apakah Anda yakin ingin kembali?')) {
                    window.location.href = 'dashboard.php';
                }
            } else {
                window.location.href = 'dashboard.php';
            }
        }

        // Initialize
        updatePreview();
        updateProgress();
        updateCharCounter(pertanyaanTextarea, pertanyaanCounter, 1000);
        updateCharCounter(kunciTextarea, kunciCounter, 500);
        updateImagePreview({ target: { files: [] } });
    </script>
</body>
</html>