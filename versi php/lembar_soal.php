<?php
session_start();
include 'functions.php';

// Pemeriksaan sesi: pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Ambil subject_id dari URL atau default
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
if (!$subject_id) {
    die("Subject ID tidak valid.");
}

// Ambil data mata kuliah
$subject = getSubjectById($subject_id);
if (!$subject) {
    die("Mata kuliah tidak ditemukan.");
}

// Ambil semua soal untuk mata kuliah ini
$questions = getQuestionsBySubject($subject_id);
if (empty($questions)) {
    die("Belum ada soal untuk mata kuliah ini.");
}

// Konversi data soal ke format yang sesuai dengan JavaScript
$quizData = array_map(function($q) {
    $options = [
        $q['option_a'],
        $q['option_b'],
        $q['option_c'],
        $q['option_d'],
        $q['option_e']
    ];
   
    $correct = array_search($q['correct_option'], ['A', 'B', 'C', 'D', 'E']);
    return [
        'question' => $q['question_text'],
        'options' => $options,
        'correct' => $correct
    ];
}, $questions);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuis - <?php echo htmlspecialchars($subject['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        .pulse-green {
            animation: pulseGreen 1s ease-in-out !important;
        }
        
        .pulse-red {
            animation: pulseRed 0.6s ease-in-out !important;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @keyframes pulseGreen {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); background-color: #10b981 !important; }
            100% { transform: scale(1); }
        }
        
        @keyframes pulseRed {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); background-color: #ef4444 !important; }
            100% { transform: scale(1); }
        }
        
        .progress-bar {
            transition: width 0.3s ease-in-out;
        }
        
        .option-button {
            transition: all 0.2s ease-in-out;
        }
        
        .notification {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .navbar-sticky {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #a0aec0 #edf2f7;
        }
        .dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }
        .dropdown-menu::-webkit-scrollbar-track {
            background: #edf2f7;
        }
        .dropdown-menu::-webkit-scrollbar-thumb {
            background: #a0aec0;
            border-radius: 3px;
        }
        .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="navbar-sticky fixed top-0 w-full z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                            <span class="mr-2">🏠</span>
                            Dashboard
                        </a>
                        <!-- Dropdown for Pilih Mata Kuliah -->
                        <div class="relative">
                            <button onclick="toggleDropdown('desktopDropdown')" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                <span class="mr-2">📚</span>
                                Pilih Mata Kuliah
                            </button>
                            <div id="desktopDropdown" class="dropdown-menu hidden absolute mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="py-1">
                                    <?php
                                    $subjects = getAllSubjects();
                                    foreach ($subjects as $s) {
                                        echo '<a href="lembar_soal.php?subject_id=' . htmlspecialchars($s['subject_id']) . '" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">' . htmlspecialchars($s['name']) . '</a>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <a href="logout.php" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 transition-colors">
                            <span class="mr-2">🚪</span>
                            Keluar
                        </a>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button onclick="toggleMobileMenu()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 bg-white">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="dashboard.php" class="flex items-center px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                    <span class="mr-2">🏠</span>
                    Dashboard
                </a>
                <!-- Dropdown for Pilih Mata Kuliah in Mobile -->
                <div class="relative">
                    <button onclick="toggleDropdown('mobileDropdown')" class="flex items-center w-full px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                        <span class="mr-2">📚</span>
                        Pilih Mata Kuliah
                    </button>
                    <div id="mobileDropdown" class="dropdown-menu hidden mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <div class="py-1">
                            <?php
                            foreach ($subjects as $s) {
                                echo '<a href="lembar_soal.php?subject_id=' . htmlspecialchars($s['subject_id']) . '" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">' . htmlspecialchars($s['name']) . '</a>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <a href="logout.php" class="flex items-center px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 transition-colors">
                    <span class="mr-2">🚪</span>
                    Keluar
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pt-24">
        <!-- Quiz Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Kuis - <?php echo htmlspecialchars($subject['name']); ?></h1>
            <p class="text-gray-600">Jawab semua pertanyaan dengan benar untuk mendapatkan skor maksimal</p>
        </div>

        <!-- Progress Section -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-medium text-gray-600">Progress Kuis</span>
                <span class="text-sm font-semibold text-blue-600" id="questionCounter">Soal 1 dari <?php echo count($questions); ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full progress-bar" id="progressBar" style="width: <?php echo (1 / count($questions)) * 100; ?>%"></div>
            </div>
        </div>

        <!-- Question Card -->
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-8 fade-in border border-gray-100" id="questionCard">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-4">
                    <span class="text-blue-600 font-bold text-lg" id="questionNumber">1</span>
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 leading-relaxed" id="questionText">
                    <?php echo htmlspecialchars($questions[0]['question_text']); ?>
                </h2>
            </div>

            <!-- Answer Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="optionsContainer">
                <?php foreach ($quizData[0]['options'] as $i => $option): ?>
                    <button class="option-button bg-gray-50 hover:bg-gray-100 border-2 border-gray-200 rounded-xl p-6 text-left transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5" onclick="selectAnswer(<?php echo $i; ?>)">
                        <div class="flex items-center">
                            <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold mr-4"><?php echo chr(65 + $i); ?></span>
                            <span class="text-lg font-medium text-gray-700"><?php echo htmlspecialchars($option); ?></span>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quiz Results Card (Hidden initially) -->
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center hidden border border-gray-100" id="resultsCard">
            <div class="mb-6">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Kuis Selesai!</h2>
                <p class="text-gray-600 text-lg">Berikut adalah hasil kuis Anda</p>
            </div>
            
            <!-- Results Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                    <div class="text-2xl font-bold text-green-600" id="correctCount">0</div>
                    <div class="text-sm text-green-700">Jawaban Benar</div>
                </div>
                <div class="bg-red-50 rounded-xl p-4 border border-red-200">
                    <div class="text-2xl font-bold text-red-600" id="wrongCount">0</div>
                    <div class="text-sm text-red-700">Jawaban Salah</div>
                </div>
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                    <div class="text-2xl font-bold text-blue-600" id="scorePercentage">0%</div>
                    <div class="text-sm text-blue-700">Persentase</div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="startRetry()" id="retryButton" class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105">
                    🔄 Ulangi Soal yang Salah
                </button>
                <button onclick="restartQuiz()" class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105">
                    🔄 Mulai Ulang Kuis
                </button>
            </div>
        </div>

        <!-- Final Complete Card (Hidden initially) -->
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center hidden border border-gray-100" id="completeCard">
            <div class="mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Sempurna! 🎉</h2>
                <p class="text-gray-600 text-lg">Anda telah menyelesaikan semua soal dengan baik</p>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6 mb-6 border border-green-200">
                <p class="text-2xl font-bold text-green-600" id="finalScore">Skor Final: 0/<?php echo count($questions); ?></p>
            </div>
            <button onclick="restartQuiz()" class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-200 transform hover:scale-105">
                🔄 Mulai Kuis Baru
            </button>
        </div>
    </main>

    <!-- Notification (Hidden initially) -->
    <div id="notification" class="notification hidden">
        <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span class="font-medium" id="notificationText">Jawaban Salah!</span>
        </div>
    </div>

    <script>
        // Data kuis dari PHP
        const quizData = <?php echo json_encode($quizData); ?>;
        let currentQuestion = 0;
        let score = 0;
        let isAnswering = false;
        let wrongAnswers = [];
        let isRetryMode = false;
        let retryQuestions = [];
        let retryIndex = 0;

        // Toggle mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Toggle dropdown menu
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('hidden');
            const otherDropdownId = dropdownId === 'desktopDropdown' ? 'mobileDropdown' : 'desktopDropdown';
            const otherDropdown = document.getElementById(otherDropdownId);
            if (!otherDropdown.classList.contains('hidden')) {
                otherDropdown.classList.add('hidden');
            }
        }

        function updateProgress() {
            if (isRetryMode) {
                const progress = ((retryIndex + 1) / retryQuestions.length) * 100;
                document.getElementById('progressBar').style.width = progress + '%';
                document.getElementById('questionCounter').textContent = `Perbaikan ${retryIndex + 1} dari ${retryQuestions.length}`;
                document.getElementById('questionNumber').textContent = retryIndex + 1;
            } else {
                const progress = ((currentQuestion + 1) / quizData.length) * 100;
                document.getElementById('progressBar').style.width = progress + '%';
                document.getElementById('questionCounter').textContent = `Soal ${currentQuestion + 1} dari ${quizData.length}`;
                document.getElementById('questionNumber').textContent = currentQuestion + 1;
            }
        }

        function loadQuestion() {
            let question;
            
            if (isRetryMode) {
                if (retryIndex >= retryQuestions.length) {
                    showComplete();
                    return;
                }
                question = retryQuestions[retryIndex];
            } else {
                if (currentQuestion >= quizData.length) {
                    showResults();
                    return;
                }
                question = quizData[currentQuestion];
            }

            console.log('Question:', question);

            document.getElementById('questionText').innerText = question.question;
            
            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = ''; // Kosongkan container sebelum menambahkan opsi baru
            const letters = ['A', 'B', 'C', 'D', 'E'];

            question.options.forEach((optionText, index) => {
                const optionElement = document.createElement('button');
                optionElement.className = 'option-button bg-gray-50 hover:bg-gray-100 border-2 border-gray-200 rounded-xl p-6 text-left transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5';
                optionElement.onclick = () => selectAnswer(index);
                const optionContent = document.createElement('div');
                optionContent.className = 'flex items-center';
                optionContent.innerHTML = `
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold mr-4">${letters[index]}</span>
                    <span class="text-lg font-medium text-gray-700 option-text"></span>
                `;
                const textSpan = optionContent.querySelector('.option-text');
                textSpan.innerText = optionText || '[Opsi kosong]';
                optionElement.appendChild(optionContent);
                optionsContainer.appendChild(optionElement);
            });

            document.getElementById('questionCard').classList.add('fade-in');
            updateProgress();
            isAnswering = false;
        }

        function selectAnswer(selectedIndex) {
            if (isAnswering) return;
            isAnswering = true;

            let question;
            if (isRetryMode) {
                question = retryQuestions[retryIndex];
            } else {
                question = quizData[currentQuestion];
            }
            
            console.log('Selected:', selectedIndex, 'Correct:', question.correct);
            
            const options = document.querySelectorAll('.option-button');
            options.forEach(option => option.disabled = true);

            if (selectedIndex === question.correct) {
                const correctOption = options[selectedIndex];
                correctOption.classList.add('bg-green-500', 'text-white', 'border-green-500', 'pulse-green');
                const letterSpan = correctOption.querySelector('span:first-child');
                letterSpan.className = 'w-8 h-8 bg-white text-green-600 rounded-full flex items-center justify-center font-semibold mr-4';
                
                if (!isRetryMode) {
                    score++;
                }
                
                setTimeout(() => {
                    if (isRetryMode) {
                        retryIndex++;
                    } else {
                        currentQuestion++;
                    }
                    loadQuestion();
                }, 1500);
            } else {
                const wrongOption = options[selectedIndex];
                const correctOption = options[question.correct];
                
                wrongOption.classList.add('bg-red-500', 'text-white', 'border-red-500', 'pulse-red', 'shake');
                correctOption.classList.add('bg-green-500', 'text-white', 'border-green-500', 'pulse-green');
                
                const wrongLetterSpan = wrongOption.querySelector('span:first-child');
                const correctLetterSpan = correctOption.querySelector('span:first-child');
                
                wrongLetterSpan.className = 'w-8 h-8 bg-white text-red-600 rounded-full flex items-center justify-center font-semibold mr-4';
                correctLetterSpan.className = 'w-8 h-8 bg-white text-green-600 rounded-full flex items-center justify-center font-semibold mr-4';
                
                if (!isRetryMode) {
                    wrongAnswers.push(currentQuestion);
                }
                
                showNotification(question);
                
                setTimeout(() => {
                    if (isRetryMode) {
                        retryIndex++;
                    } else {
                        currentQuestion++;
                    }
                    loadQuestion();
                }, 3000);
            }
        }

        function showNotification(question) {
            const notification = document.getElementById('notification');
            notification.innerHTML = `
                <div class="bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="font-medium">Jawaban Salah!</span>
                </div>
            `;
            notification.classList.remove('hidden');
            
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 3000);
        }

        function showResults() {
            document.getElementById('questionCard').classList.add('hidden');
            document.getElementById('resultsCard').classList.remove('hidden');
            
            const correctAnswers = score;
            const wrongAnswersCount = quizData.length - score;
            const percentage = Math.round((score / quizData.length) * 100);
            
            document.getElementById('correctCount').textContent = correctAnswers;
            document.getElementById('wrongCount').textContent = wrongAnswersCount;
            document.getElementById('scorePercentage').textContent = percentage + '%';
            
            if (wrongAnswers.length === 0) {
                document.getElementById('retryButton').style.display = 'none';
            }
            
            document.getElementById('progressBar').style.width = '100%';
            document.getElementById('questionCounter').textContent = `Selesai - ${score}/${quizData.length} Benar`;
        }

        function startRetry() {
            isRetryMode = true;
            retryIndex = 0;
            retryQuestions = wrongAnswers.map(index => quizData[index]);
            
            document.getElementById('resultsCard').classList.add('hidden');
            document.getElementById('questionCard').classList.remove('hidden');
            loadQuestion();
        }

        function showComplete() {
            document.getElementById('questionCard').classList.add('hidden');
            document.getElementById('resultsCard').classList.add('hidden');
            document.getElementById('completeCard').classList.remove('hidden');
            document.getElementById('finalScore').textContent = `Skor Final: ${score}/${quizData.length}`;
            
            document.getElementById('progressBar').style.width = '100%';
            document.getElementById('questionCounter').textContent = `Sempurna! ${score}/${quizData.length}`;
        }

        function restartQuiz() {
            currentQuestion = 0;
            score = 0;
            wrongAnswers = [];
            isRetryMode = false;
            retryQuestions = [];
            retryIndex = 0;
            
            document.getElementById('questionCard').classList.remove('hidden');
            document.getElementById('resultsCard').classList.add('hidden');
            document.getElementById('completeCard').classList.add('hidden');
            document.getElementById('retryButton').style.display = 'inline-block';
            
            loadQuestion();
        }

        // Initialize quiz
        loadQuestion();

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const desktopDropdown = document.getElementById('desktopDropdown');
            const mobileDropdown = document.getElementById('mobileDropdown');
            const desktopButton = document.querySelector('button[onclick*="desktopDropdown"]');
            const mobileButton = document.querySelector('button[onclick*="mobileDropdown"]');

            if (!desktopDropdown.contains(event.target) && !desktopButton.contains(event.target)) {
                desktopDropdown.classList.add('hidden');
            }
            if (!mobileDropdown.contains(event.target) && !mobileButton.contains(event.target)) {
                mobileDropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>