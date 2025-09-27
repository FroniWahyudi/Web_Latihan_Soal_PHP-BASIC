<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit;
}
include 'config.php';

$mk_id = isset($_GET['mk_id']) ? intval($_GET['mk_id']) : 0;
$soal_id = isset($_GET['soal_id']) ? intval($_GET['soal_id']) : 0;

// Ambil semua id soal untuk penomoran rapi
$sql_all = "SELECT id FROM soal WHERE mata_kuliah_id = $mk_id ORDER BY id";
$result_all = $conn->query($sql_all);
$ids = [];
while ($row = $result_all->fetch_assoc()) {
    $ids[] = $row['id'];
}

// Kalau belum ada soal_id → mulai dari soal pertama
if ($soal_id == 0 && !empty($ids)) {
    $soal_id = $ids[0];
}

// Ambil soal berdasarkan mata_kuliah_id dan soal_id
$sql = "SELECT * FROM soal WHERE mata_kuliah_id = $mk_id AND id = $soal_id LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Tidak ada soal.";
    exit;
}
$soal = $result->fetch_assoc();

// Hitung nomor soal rapi
$nomor_soal = array_search($soal['id'], $ids) + 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    $kunci = $soal['kunci_jawaban'];
    
    similar_text($jawaban, $kunci, $percent);
    $percent = round($percent, 2);
    
    if ($percent >= 70) {
        // Jawaban benar, simpan riwayat
        $riwayat = isset($_COOKIE['riwayat']) ? json_decode($_COOKIE['riwayat'], true) : [];
        $mk_sql = "SELECT nama FROM mata_kuliah WHERE id = $mk_id";
        $mk_result = $conn->query($mk_sql);
        $mk_nama = $mk_result->fetch_assoc()['nama'];
        if (!in_array($mk_nama, $riwayat)) {
            $riwayat[] = $mk_nama;
        }
        setcookie('riwayat', json_encode($riwayat), time() + 86400, "/"); // 24 jam
        
        // Simpan skor ke sesi untuk ditampilkan di notifikasi
        $_SESSION['last_score'] = $percent;
        
        // Cari soal berikutnya
        $next_sql = "SELECT id FROM soal WHERE mata_kuliah_id = $mk_id AND id > " . $soal['id'] . " ORDER BY id LIMIT 1";
        $next_result = $conn->query($next_sql);
        
        if ($next_result->num_rows > 0) {
            $next_soal = $next_result->fetch_assoc();
            header("Location: kerjakan_soal.php?mk_id=$mk_id&soal_id=" . $next_soal['id']);
        } else {
            // Tidak ada soal lagi, arahkan ke halaman yang sama dengan parameter selesai
            header("Location: kerjakan_soal.php?mk_id=$mk_id&selesai=1");
        }
        exit;
    } else {
        $hasil = "Jawaban salah ($percent% mirip). Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerjakan Soal</title>
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
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-4 py-8">
        <?php if (isset($_GET['selesai']) && $_GET['selesai'] == 1) { ?>
            <!-- Notifikasi Selesai -->
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
                        <a href="kerjakan_soal.php?mk_id=<?php echo $mk_id; ?>" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-2 rounded-xl font-medium hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300">
                            Ulangi Soal
                        </a>
                        <a href="dashboard.php" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-2 rounded-xl font-medium hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <!-- Question Section -->
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
            </div>

            <!-- Answer Section -->
            <div class="glass-effect rounded-2xl p-6 fade-in">
                <form method="POST">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-xl font-bold text-gray-800">Jawaban Anda</h3>
                    </div>
                    <div class="relative">
                        <textarea 
                            name="jawaban" 
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
                        <a href="dashboard.php" class="flex items-center text-gray-700 hover:text-purple-600 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali
                        </a>
                        <button 
                            type="submit" 
                            class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-2 rounded-xl font-medium hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300"
                        >
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>