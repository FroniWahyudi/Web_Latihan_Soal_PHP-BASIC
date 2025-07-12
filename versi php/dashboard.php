<?php
session_start();
include 'functions.php';

// Pemeriksaan sesi: pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil nama pengguna dari sesi, fallback ke 'Pengguna' jika tidak ada
$user_name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Pengguna';

// Ambil semua mata kuliah
$subjects = getAllSubjects();

// Ambil semua soal
$questions = getAllQuestions();

// Hitung total soal per mata kuliah
$subjectQuestionCounts = [];
foreach ($questions as $question) {
    $subjectId = $question['subject_id'];
    $subjectQuestionCounts[$subjectId] = ($subjectQuestionCounts[$subjectId] ?? 0) + 1;
}

// Siapkan data untuk ditampilkan
$subjectsData = [];
foreach ($subjects as $subject) {
    $subjectId = $subject['subject_id'];
    $subjectsData[] = [
        'id' => $subjectId,
        'name' => $subject['name'],
        'questions' => $subjectQuestionCounts[$subjectId] ?? 0,
        'lastAccessed' => $subject['last_accessed'] ? date('d M Y', strtotime($subject['last_accessed'])) : 'Belum diakses',
        'emoji' => '📚', // Bisa disesuaikan
        'color' => 'from-blue-500 to-blue-600' // Bisa disesuaikan
    ];
}

// Hitung statistik
$totalSubjects = count($subjectsData);
$totalQuestions = count($questions);
$completedQuizzes = 0; // Sesuaikan dengan data yang ada, misalnya dari quiz_results
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Dashboard</title>
    <link href="tailwind.min.css" rel="stylesheet">
    <style>
        .subject-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .subject-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.15);
        }
        
        .search-bar {
            transition: all 0.3s ease;
        }
        
        .search-bar:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .delete-btn {
            transition: all 0.2s ease;
        }
        
        .delete-btn:hover {
            transform: scale(1.1);
        }
        
        .add-btn {
            transition: all 0.3s ease;
        }
        
        .add-btn:hover {
            transform: scale(1.05);
        }
        
        .modal {
            backdrop-filter: blur(8px);
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logout-btn {
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background-color: #dc2626;
        }

        .welcome-text {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Message -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 welcome-text">
                Selamat Datang, <?php echo $user_name; ?>!
            </h1>
            <p class="text-lg text-gray-600 welcome-text">
                Mau belajar apa hari ini?
            </p>
        </div>

        <!-- Header with Logout Button -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Quiz Dashboard</h1>
            <button onclick="logout()" class="logout-btn bg-red-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-600 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h3a3 3 0 013 3v1"></path>
                </svg>
                <span>Logout</span>
            </button>
        </div>

        <!-- Search and Add Section -->
        <div class="bg-white rounded-xl p-6 mb-8 shadow-lg">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Search Bar -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="search-input" placeholder="Cari mata kuliah..." 
                           class="search-bar w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                
                <!-- Add New Quiz Button -->
                <button onclick="window.location.href='buat_soal.php'" class="add-btn bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 flex items-center space-x-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Buat Soal Baru</span>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Mata Kuliah</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $totalSubjects; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Soal</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $totalQuestions; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Quiz Selesai</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $completedQuizzes; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="subjects-grid">
            <?php foreach ($subjectsData as $subject): ?>
                <div class="subject-card bg-white rounded-xl p-6 relative group">
                    <!-- Delete Button -->
                    <button onclick="openDeleteModal(<?php echo $subject['id']; ?>, '<?php echo $subject['name']; ?>')" 
                            class="delete-btn absolute top-3 right-3 w-8 h-8 bg-red-100 text-red-600 rounded-full hover:bg-red-200 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    
                    <!-- Subject Content -->
                    <div class="text-center cursor-pointer" onclick="openQuiz(<?php echo $subject['id']; ?>)">
                        <div class="w-16 h-16 bg-gradient-to-r <?php echo $subject['color']; ?> rounded-full flex items-center justify-center text-3xl text-white mx-auto mb-4">
                            <?php echo $subject['emoji']; ?>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg mb-2"><?php echo $subject['name']; ?></h3>
                        <div class="space-y-1 text-sm text-gray-600">
                            <p><?php echo $subject['questions']; ?> soal tersedia</p>
                            <p>Terakhir diakses: <?php echo $subject['lastAccessed']; ?></p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-4 flex space-x-2">
                        <a href="lembar_soal.php?subject_id=<?php echo $subject['id']; ?>" 
                           class="flex-1 bg-gradient-to-r <?php echo $subject['color']; ?> text-white py-2 px-4 rounded-lg font-medium hover:opacity-90 transition-opacity text-center block">
                            Mulai Quiz
                        </a>
                        <a href="edit_soal.php?subject_id=<?php echo $subject['id']; ?>" 
                           class="px-3 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State (Hidden initially) -->
        <div class="text-center py-12 hidden" id="empty-state">
            <div class="text-6xl mb-4">🔍</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak ada mata kuliah ditemukan</h3>
            <p class="text-gray-500">Coba gunakan kata kunci yang berbeda atau buat soal baru</p>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" id="delete-modal">
            <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 fade-in">
                <div class="text-center">
                    <div class="text-6xl mb-4">🗑️</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Mata Kuliah?</h3>
                    <p class="text-gray-600 mb-6">Semua soal dalam mata kuliah "<span id="delete-subject-name"></span>" akan dihapus permanen.</p>
                    
                    <div class="flex space-x-3">
                        <button onclick="closeDeleteModal()" 
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                            Batal
                        </button>
                        <button onclick="confirmDelete()" 
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let subjects = <?php echo json_encode($subjectsData); ?>;
    let filteredSubjects = [...subjects];
    let subjectToDelete = null;

    function renderSubjects() {
        const grid = document.getElementById('subjects-grid');
        const emptyState = document.getElementById('empty-state');
        
        if (filteredSubjects.length === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }
        
        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');
        
        grid.innerHTML = filteredSubjects.map(subject => `
            <div class="subject-card bg-white rounded-xl p-6 relative group">
                <button onclick="openDeleteModal(${subject.id}, '${subject.name}')" 
                        class="delete-btn absolute top-3 right-3 w-8 h-8 bg-red-100 text-red-600 rounded-full hover:bg-red-200 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                <div class="text-center cursor-pointer" onclick="openQuiz(${subject.id})">
                    <div class="w-16 h-16 bg-gradient-to-r ${subject.color} rounded-full flex items-center justify-center text-3xl text-white mx-auto mb-4">
                        ${subject.emoji}
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2">${subject.name}</h3>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p>${subject.questions} soal tersedia</p>
                        <p>Terakhir diakses: ${subject.lastAccessed}</p>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <a href="lembar_soal.php?subject_id=${subject.id}" 
                       class="flex-1 bg-gradient-to-r ${subject.color} text-white py-2 px-4 rounded-lg font-medium hover:opacity-90 transition-opacity text-center block">
                        Mulai Quiz
                    </a>
                    <a href="edit_soal.php?subject_id=${subject.id}" 
                       class="px-3 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        `).join('');
    }

    function searchSubjects() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        filteredSubjects = subjects.filter(subject => 
            subject.name.toLowerCase().includes(searchTerm)
        );
        renderSubjects();
    }

    function openDeleteModal(id, name) {
        subjectToDelete = id;
        document.getElementById('delete-subject-name').textContent = name;
        document.getElementById('delete-modal').classList.remove('hidden');
        document.getElementById('delete-modal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
        document.getElementById('delete-modal').classList.remove('flex');
        subjectToDelete = null;
    }

    function confirmDelete() {
        if (subjectToDelete) {
            fetch('delete_subject.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'subject_id=' + subjectToDelete
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Refresh halaman setelah penghapusan berhasil
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                    console.error('Server error:', data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('❌ Terjadi kesalahan saat menghapus mata kuliah dan soalnya. Periksa konsol untuk detail.');
            });
        }
    }

    function openQuiz(subjectId) {
        const subject = subjects.find(s => s.id === subjectId);
        if (subject.questions === 0) {
            alert('⚠️ Belum ada soal untuk mata kuliah ini. Silakan buat soal terlebih dahulu!');
            return;
        }
        window.location.href = `lembar_soal.php?subject_id=${subjectId}`;
    }

    function logout() {
        window.location.href = 'logout.php';
    }

    // Event listeners
    document.getElementById('search-input').addEventListener('input', searchSubjects);

    // Close modals when clicking outside
    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Initialize
    renderSubjects();
    </script>
    <footer class="mt-12 py-6 text-center text-gray-500 text-sm">
        © 2025 froniwahyudi. All rights reserved.
    </footer>
</body>
</html>