<?php
// dashboard.php - Halaman Utama
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';

$role = $_SESSION['role'];

// Ambil riwayat dari cookie jika ada
$riwayat = [];
if (isset($_COOKIE['riwayat'])) {
    $riwayat = json_decode($_COOKIE['riwayat'], true);
}

// Ambil daftar mata kuliah
$sql = "SELECT * FROM mata_kuliah";
$result = $conn->query($sql);

// Proses penghapusan mata kuliah jika ada permintaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_mk_id']) && $role === 'admin') {
    $mk_id = $_POST['delete_mk_id'];
    $delete_sql = "DELETE FROM mata_kuliah WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $mk_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
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
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 5%;
            animation-delay: 3s;
        }

        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 10%;
            left: 15%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .sidebar-hidden {
                transform: translateX(-100%);
            }
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: #333;
            width: 100%;
            max-width: 300px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        h1, h3 {
            color: #333;
        }

        a {
            text-decoration: none;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.5);
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="gradient-bg">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <!-- Mobile Menu Button -->
        <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-white/20 backdrop-blur-sm text-white p-2 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-transition fixed left-0 top-0 h-full w-64 glass-effect z-40 p-6 md:translate-x-0 sidebar-hidden">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800" id="userRole"><?php echo $role; ?></h3>
                <p class="text-sm text-gray-600">Sistem Manajemen</p>
            </div>
            <nav class="space-y-2">
                <a href="#dashboard" class="nav-item active flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-white/50 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="#mata-kuliah" class="nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-white/50 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Mata Kuliah
                </a>
                <a href="#riwayat" class="nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-white/50 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Riwayat
                </a>
            </nav>
            <div class="absolute bottom-6 left-6 right-6">
                <a href="logout.php">
                    <button id="logoutBtn" class="w-full flex items-center justify-center px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="md:ml-64 min-h-screen p-6">
            <!-- Header -->
            <div class="glass-effect rounded-2xl p-6 mb-6 fade-in">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Selamat Datang!</h1>
                        <p class="text-gray-600">Kelola sistem pembelajaran dengan mudah</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Hari ini</p>
                                <p class="font-semibold text-gray-800" id="currentDate"></p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="glass-effect rounded-xl p-6 card-hover fade-in">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Mata Kuliah</p>
                            <p class="text-2xl font-bold text-gray-800" id="totalMataKuliah"><?php echo $result->num_rows; ?></p>
                        </div>
                    </div>
                </div>
                <div class="glass-effect rounded-xl p-6 card-hover fade-in">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Soal Dikerjakan</p>
                            <p class="text-2xl font-bold text-gray-800" id="soalDikerjakan"><?php echo count($riwayat); ?></p>
                        </div>
                    </div>
                </div>
                <div class="glass-effect rounded-xl p-6 card-hover fade-in">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Aktivitas Hari Ini</p>
                            <p class="text-2xl font-bold text-gray-800" id="aktivitasHariIni"><?php echo count($riwayat); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Mata Kuliah Form (Admin Only) -->
            <?php if ($role == 'admin'): ?>
            <div id="adminSection" class="glass-effect rounded-2xl p-6 mb-6 fade-in">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Mata Kuliah Baru
                </h3>
                <form id="addMataKuliahForm" method="POST" action="add_matakuliah.php" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            name="nama" 
                            id="namaMataKuliah"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300 bg-white/80"
                            placeholder="Masukkan nama mata kuliah"
                            required
                        >
                    </div>
                    <button 
                        type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg font-medium hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300 shadow-lg"
                    >
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Tambah
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Mata Kuliah Cards -->
            <div class="glass-effect rounded-2xl p-6 mb-6 fade-in">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Daftar Mata Kuliah
                </h3>
                <div id="mataKuliahGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white/80 rounded-xl p-6 card-hover shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-800"><?php echo $row['nama']; ?></h4>
                                <p class="text-sm text-gray-600">Mata Kuliah</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <?php if ($role == 'user'): ?>
                            <a href="kerjakan_soal.php?mk_id=<?php echo $row['id']; ?>">
                                <button class="user-btn w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Kerjakan Soal
                                </button>
                            </a>
                            <?php endif; ?>
                            <?php if ($role == 'admin'): ?>
                            <div class="admin-btn space-y-2">
                                <a href="add_soal.php?mk_id=<?php echo $row['id']; ?>">
                                    <button class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Tambah Soal
                                    </button>
                                </a>
                                <a href="list_soal.php?mk_id=<?php echo $row['id']; ?>">
                                    <button class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Lihat/CRUD Soal
                                    </button>
                                </a>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?');">
                                    <input type="hidden" name="delete_mk_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="delete-btn w-full mt-2">Hapus Mata Kuliah</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Riwayat Section -->
            <div class="glass-effect rounded-2xl p-6 fade-in">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Riwayat Aktivitas Hari Ini
                </h3>
                <div id="riwayatContainer" class="space-y-4">
                    <?php if (!empty($riwayat)): ?>
                        <?php foreach ($riwayat as $item): ?>
                        <div class="flex items-center p-4 bg-white/60 rounded-lg">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-gray-800 font-medium"><?php echo $item; ?></p>
                                <p class="text-sm text-gray-600"><?php echo date('H:i A'); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div id="emptyRiwayat" class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-600">Tidak ada aktivitas hari ini</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Role Toggle (Demo Purpose) -->
        <div class="fixed bottom-6 right-6 z-50">
            <button id="roleToggle" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-4 py-2 rounded-full shadow-lg hover:from-purple-700 hover:to-blue-700 transition-all duration-300">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span id="roleToggleText">Switch to User</span>
            </button>
        </div>
    </div>

    <script>
        // Current role state
        let currentRole = '<?php echo $role; ?>';

        // Set current date
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) + ' ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');

        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-hidden');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768 && !sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                sidebar.classList.add('sidebar-hidden');
            }
        });

        // Navigation
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                navItems.forEach(nav => nav.classList.remove('active', 'bg-white/50'));
                this.classList.add('active', 'bg-white/50');
            });
        });

        // Role toggle functionality
        const roleToggle = document.getElementById('roleToggle');
        const roleToggleText = document.getElementById('roleToggleText');
        const userRole = document.getElementById('userRole');
        const adminSection = document.getElementById('adminSection');
        const adminBtns = document.querySelectorAll('.admin-btn');
        const userBtns = document.querySelectorAll('.user-btn');

        function updateRoleDisplay() {
            if (currentRole === 'admin') {
                userRole.textContent = 'Admin';
                if (adminSection) adminSection.classList.remove('hidden');
                adminBtns.forEach(btn => btn.classList.remove('hidden'));
                userBtns.forEach(btn => btn.classList.add('hidden'));
                roleToggleText.textContent = 'Switch to User';
            } else {
                userRole.textContent = 'User';
                if (adminSection) adminSection.classList.add('hidden');
                adminBtns.forEach(btn => btn.classList.add('hidden'));
                userBtns.forEach(btn => btn.classList.remove('hidden'));
                roleToggleText.textContent = 'Switch to Admin';
            }
        }

        roleToggle.addEventListener('click', function() {
            currentRole = currentRole === 'admin' ? 'user' : 'admin';
            updateRoleDisplay();
        });

        // Initialize role display
        updateRoleDisplay();

        // Add some animation delays for cards
        const cards = document.querySelectorAll('.fade-in');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    </script>
</body>
</html>