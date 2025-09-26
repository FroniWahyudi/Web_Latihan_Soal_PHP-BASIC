<?php
// list_soal.php - Lihat/CRUD Soal (Admin)
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
include 'config.php';

// Validate mk_id
$mk_id = isset($_GET['mk_id']) ? intval($_GET['mk_id']) : 0;
if ($mk_id <= 0) {
    die('Error: Invalid or missing mata kuliah ID.');
}

// Fetch soal
$sql = "SELECT * FROM soal WHERE mata_kuliah_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param('i', $mk_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch mata kuliah name
$mk_sql = "SELECT nama FROM mata_kuliah WHERE id = ?";
$stmt_mk = $conn->prepare($mk_sql);
if (!$stmt_mk) {
    die("Query preparation failed: " . $conn->error);
}
$stmt_mk->bind_param('i', $mk_id);
$stmt_mk->execute();
$mk_result = $stmt_mk->get_result();

$mata_kuliah_name = 'Unknown';
if ($mk_result && $mk_result->num_rows > 0) {
    $mata_kuliah_name = $mk_result->fetch_assoc()['nama'];
} else {
    error_log("No mata kuliah found for mk_id: $mk_id");
}
$stmt_mk->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Soal - <?php echo htmlspecialchars($mata_kuliah_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            position: relative;
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
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 70%;
            right: 15%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
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
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-3xl font-bold text-gray-800">Daftar Soal</h1>
                        <p class="text-gray-600">Mata Kuliah: <span id="mataKuliahName" class="font-semibold text-purple-600"><?php echo htmlspecialchars($mata_kuliah_name); ?></span></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Total Soal</p>
                        <p class="text-2xl font-bold text-gray-800" id="totalSoal"><?php echo $result->num_rows; ?></p>
                    </div>
                    <a href="add_soal.php?mk_id=<?php echo $mk_id; ?>" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg font-medium hover:from-green-600 hover:to-green-700 transition-all duration-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Tambah Soal
                    </a>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="glass-effect rounded-xl p-4 mb-6 fade-in">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input 
                            type="text" 
                            id="searchInput"
                            placeholder="Cari soal..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white/80"
                        >
                    </div>
                </div>
                <button id="clearSearch" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Soal List -->
        <div id="soalContainer" class="space-y-4">
            <?php $index = 1; while ($row = $result->fetch_assoc()): ?>
                <div class="soal-item glass-effect rounded-xl p-6 card-hover fade-in">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-<?php echo ['blue-500', 'green-500', 'purple-500'][$index % 3]; ?> text-white rounded-full flex items-center justify-center font-bold">
                                <?php echo $index; ?>
                            </div>
                            <div class="ml-3">
                                <span class="text-sm text-gray-500">Soal #<?php echo $index; ?></span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="edit_soal.php?id=<?php echo $row['id']; ?>&mk_id=<?php echo $mk_id; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Soal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button onclick="deleteSoal(<?php echo $row['id']; ?>)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Soal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Pertanyaan:</h4>
                        <p class="text-gray-800 bg-white/50 rounded-lg p-3"><?php echo htmlspecialchars($row['pertanyaan']); ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Kunci Jawaban:</h4>
                        <p class="text-gray-800 bg-green-50 rounded-lg p-3"><?php echo htmlspecialchars($row['kunci_jawaban']); ?></p>
                    </div>
                </div>
            <?php $index++; endwhile; ?>
            <?php $stmt->close(); ?>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="<?php echo $result->num_rows > 0 ? 'hidden' : ''; ?> glass-effect rounded-xl p-12 text-center fade-in">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Belum Ada Soal</h3>
            <p class="text-gray-600 mb-4">Mulai dengan menambahkan soal pertama untuk mata kuliah ini</p>
            <a href="add_soal.php?mk_id=<?php echo $mk_id; ?>" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:from-purple-700 hover:to-blue-700 transition-all duration-300">
                Tambah Soal Pertama
            </a>
        </div>

        <!-- No Search Results -->
        <div id="noResults" class="hidden glass-effect rounded-xl p-12 text-center fade-in">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Tidak Ada Hasil</h3>
            <p class="text-gray-600">Tidak ditemukan soal yang sesuai dengan pencarian Anda</p>
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        const soalItems = document.querySelectorAll('.soal-item');
        const soalContainer = document.getElementById('soalContainer');
        const noResults = document.getElementById('noResults');
        const emptyState = document.getElementById('emptyState');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;

            soalItems.forEach(item => {
                const pertanyaan = item.querySelector('.bg-white\\/50').textContent.toLowerCase();
                const kunci = item.querySelector('.bg-green-50').textContent.toLowerCase();
                
                if (pertanyaan.includes(searchTerm) || kunci.includes(searchTerm)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && searchTerm !== '') {
                noResults.classList.remove('hidden');
                emptyState.classList.add('hidden');
            } else {
                noResults.classList.add('hidden');
                emptyState.classList.toggle('hidden', visibleCount > 0 || searchTerm === '');
            }

            updateTotalCount();
        });

        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            soalItems.forEach(item => {
                item.style.display = 'block';
            });
            noResults.classList.add('hidden');
            emptyState.classList.toggle('hidden', soalItems.length > 0);
            updateTotalCount();
        });

        // CRUD Functions
        function goBack() {
            window.location.href = 'dashboard.php';
        }

        function deleteSoal(id) {
            if (confirm('Apakah Anda yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan.')) {
                window.location.href = `delete_soal.php?id=${id}&mk_id=<?php echo $mk_id; ?>`;
            }
        }

        // Update total count
        function updateTotalCount() {
            const visibleItems = document.querySelectorAll('.soal-item:not([style*="display: none"])').length;
            document.getElementById('totalSoal').textContent = visibleItems;
        }

        // Add animation delays for items
        const items = document.querySelectorAll('.fade-in');
        items.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
        });

        // Initialize
        updateTotalCount();
    </script>
</body>
</html>
```