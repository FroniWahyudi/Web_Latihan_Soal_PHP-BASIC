<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}
include 'config.php';

// Set timezone ke WIB
date_default_timezone_set('Asia/Jakarta');

// Handle mode switch
if (isset($_GET['mode']) && $_GET['mode'] === 'repetitive') {
    $_SESSION['repetitive_mode'] = true;
} elseif (isset($_GET['mode']) && $_GET['mode'] === 'normal') {
    unset($_SESSION['repetitive_mode']);
}

$mk_id = isset($_GET['mk_id']) ? intval($_GET['mk_id']) : 0;
$soal_id = isset($_GET['soal_id']) ? intval($_GET['soal_id']) : 0;

// Ambil semua id soal
$sql_all = "SELECT id FROM soal WHERE mata_kuliah_id = ? ORDER BY id";
$stmt_all = $conn->prepare($sql_all);
$stmt_all->bind_param("i", $mk_id);
$stmt_all->execute();
$result_all = $stmt_all->get_result();
$ids = [];
while ($row = $result_all->fetch_assoc()) {
    $ids[] = $row['id'];
}
$stmt_all->close();

if ($soal_id == 0 && !empty($ids)) {
    $soal_id = $ids[0];
}

// Ambil soal
$sql = "SELECT * FROM soal WHERE mata_kuliah_id = ? AND id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $mk_id, $soal_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows == 0) {
    echo "Tidak ada soal.";
    exit;
}
$soal = $result->fetch_assoc();

// Hitung nomor soal
$nomor_soal = array_search($soal['id'], $ids) + 1;

// Ambil nama mata kuliah
$mk_nama = "Unknown";
$mk_sql = "SELECT nama FROM mata_kuliah WHERE id = ?";
$mk_stmt = $conn->prepare($mk_sql);
$mk_stmt->bind_param("i", $mk_id);
$mk_stmt->execute();
$mk_result = $mk_stmt->get_result();
if ($mk_result->num_rows > 0) {
    $mk_nama = htmlspecialchars($mk_result->fetch_assoc()['nama']);
}
$mk_stmt->close();

$hasil = '';
$percent = 0;
$show_answer = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    $kunci = $soal['kunci_jawaban'];
    
    similar_text($jawaban, $kunci, $percent);
    $percent = round($percent, 2);
    
    if (isset($_POST['toggle_answer'])) {
        $show_answer = true;
    }
    
    if (!isset($_SESSION['repetitive_mode'])) {
        if ($percent >= 70) {
            // Simpan riwayat
            $riwayat = isset($_COOKIE['riwayat']) ? json_decode($_COOKIE['riwayat'], true) : [];
            if (!is_array($riwayat)) {
                $riwayat = [];
            }
            $current_time = time();
            $eight_hours_ago = $current_time - (8 * 3600);
            $already_exists = false;
            foreach ($riwayat as $item) {
                if (isset($item['mata_kuliah'], $item['timestamp']) && 
                    $item['mata_kuliah'] === "Mengerjakan soal: $mk_nama" && 
                    $item['timestamp'] >= $eight_hours_ago) {
                    $already_exists = true;
                    break;
                }
            }
            if (!$already_exists) {
                $riwayat[] = [
                    'mata_kuliah' => "Mengerjakan soal: $mk_nama",
                    'timestamp' => $current_time
                ];
                setcookie('riwayat', json_encode($riwayat), time() + (24 * 3600), "/");
            }
            
            $_SESSION['last_score'] = $percent;
            
            // Cari soal berikutnya
            $next_sql = "SELECT id FROM soal WHERE mata_kuliah_id = ? AND id > ? ORDER BY id LIMIT 1";
            $next_stmt = $conn->prepare($next_sql);
            $next_stmt->bind_param("ii", $mk_id, $soal['id']);
            $next_stmt->execute();
            $next_result = $next_stmt->get_result();
            
            if ($next_result->num_rows > 0) {
                $next_soal = $next_result->fetch_assoc();
                header("Location: kerjakan_soal.php?mk_id=$mk_id&soal_id=" . $next_soal['id']);
            } else {
                header("Location: kerjakan_soal.php?mk_id=$mk_id&selesai=1");
            }
            $next_stmt->close();
            exit;
        } else {
            $hasil = "Jawaban salah ($percent% mirip). Silakan coba lagi.";
        }
    } else {
        $hasil = "Skor kemiripan: $percent%";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerjakan Soal - <?php echo $mk_nama; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
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
            border-color: #10b981;
        }
        
        .error {
            color: #ef4444;
            font-weight: 500;
        }

        .soal-image {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #10b981;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Mobile-friendly button styles */
        .btn-mobile {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        @media (max-width: 640px) {
            .btn-mobile {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
            }
        }

        .nav-button {
            min-width: 40px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8">
        <?php if (isset($_GET['selesai']) && $_GET['selesai'] == 1 && !isset($_SESSION['repetitive_mode'])) { ?>
            <div class="glass-effect rounded-2xl p-6 mb-6 fade-in">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center bg-green-500">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Selamat!</h2>
                    <p class="text-gray-600 mb-4">Anda telah menyelesaikan semua soal dengan benar.</p>
                    <p class="text-gray-600 mb-6">Skor terakhir Anda: <span class="font-semibold text-green-600"><?php echo isset($_SESSION['last_score']) ? $_SESSION['last_score'] . '%' : 'N/A'; ?></span></p>
                    <div class="flex justify-center gap-4">
                        <a href="kerjakan_soal.php?mk_id=<?php echo $mk_id; ?>" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white btn-mobile hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300">
                            Ulangi Soal
                        </a>
                        <a href="index.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white btn-mobile hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="glass-effect rounded-2xl p-6 mb-6 fade-in">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Mode Latihan Repetitif</h2>
                    <label class="toggle-switch">
                        <input type="checkbox" <?php echo isset($_SESSION['repetitive_mode']) ? 'checked' : ''; ?> onchange="window.location.href='kerjakan_soal.php?mk_id=<?php echo $mk_id; ?>&soal_id=<?php echo $soal_id; ?>&mode=' + (this.checked ? 'repetitive' : 'normal')">
                        <span class="slider"></span>
                    </label>
                </div>
                <?php if (isset($_SESSION['repetitive_mode'])): ?>
                    <div class="flex flex-wrap gap-2 justify-center mb-4">
                        <?php foreach ($ids as $index => $id): ?>
                            <a href="kerjakan_soal.php?mk_id=<?php echo $mk_id; ?>&soal_id=<?php echo $id; ?>&mode=repetitive" 
                               class="bg-gradient-to-r <?php echo $id == $soal_id ? 'from-blue-600 to-blue-700' : 'from-gray-400 to-gray-500'; ?> text-white btn-mobile nav-button hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300">
                                <?php echo $index + 1; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="glass-effect rounded-2xl p-6 mb-6 fade-in">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
                        <?php echo $nomor_soal; ?>
                    </div>
                    <h2 class="ml-3 text-2xl font-bold text-gray-800">Pertanyaan</h2>
                </div>
                <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                    <p class="text-gray-800 text-lg leading-relaxed">
                        <?php echo htmlspecialchars($soal['pertanyaan']); ?>
                    </p>
                    <?php if (!empty($soal['gambar']) && file_exists($soal['gambar'])): ?>
                        <div class="mt-4">
                            <img src="<?php echo htmlspecialchars($soal['gambar']); ?>" class="soal-image" alt="Gambar Soal">
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['repetitive_mode'])): ?>
                    <div class="mt-4">
                        <button type="button" onclick="document.getElementById('answer').classList.toggle('hidden')" class="flex items-center text-gray-700 hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <?php echo $show_answer ? 'Sembunyikan' : 'Tampilkan'; ?> Kunci Jawaban
                        </button>
                        <div id="answer" class="mt-2 bg-gray-100 rounded-lg p-4 <?php echo $show_answer ? '' : 'hidden'; ?>">
                            <p class="text-gray-800"><?php echo htmlspecialchars($soal['kunci_jawaban']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="glass-effect rounded-2xl p-6 fade-in">
                <form method="POST">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-xl font-bold text-gray-800">Jawaban Anda</h3>
                        </div>
                        <button 
                            type="button" 
                            onclick="document.getElementById('jawaban').value = ''"
                            class="bg-gradient-to-r from-gray-500 to-gray-600 text-white btn-mobile hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300"
                        >
                            Clear
                        </button>
                    </div>
                    <div class="relative">
                        <textarea 
                            name="jawaban" 
                            id="jawaban"
                            class="textarea-focus w-full px-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 bg-white/80 resize-none"
                            placeholder="Tulis jawaban Anda di sini..."
                            rows="6"
                            required
                        ><?php echo isset($_POST['jawaban']) ? htmlspecialchars($_POST['jawaban']) : ''; ?></textarea>
                    </div>
                    <?php if (isset($hasil)) { ?>
                        <div class="bg-red-50 rounded-lg p-4 mt-4 border-l-4 border-red-500">
                            <p class="error"><?php echo htmlspecialchars($hasil); ?></p>
                        </div>
                    <?php } ?>
                    <div class="flex items-center justify-between mt-4">
                        <a href="index.php" class="flex items-center text-gray-700 hover:text-purple-600 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali
                        </a>
                        <div class="flex gap-2">
                            <?php if (isset($_SESSION['repetitive_mode'])): ?>
                                <button 
                                    type="submit" 
                                    name="toggle_answer"
                                    value="1"
                                    class="bg-gradient-to-r from-blue-500 to-blue-600 text-white btn-mobile hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300"
                                >
                                    Cek Jawaban
                                </button>
                            <?php endif; ?>
                            <button 
                                type="submit" 
                                class="bg-gradient-to-r from-green-500 to-green-600 text-white btn-mobile hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300"
                            >
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>