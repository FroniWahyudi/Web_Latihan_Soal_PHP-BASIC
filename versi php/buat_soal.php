<?php
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectName = $_POST['subject'] ?? '';
    $questionsJson = $_POST['questions'] ?? '';
    
    // Validasi input
    if (empty($subjectName)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama mata kuliah tidak boleh kosong']);
        exit;
    }

    $questions = json_decode($questionsJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memproses data soal: ' . json_last_error_msg()]);
        exit;
    }

    if (empty($questions)) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada soal yang dikirim']);
        exit;
    }

    // Cari atau buat subject
    $subjectId = null;
    $subjects = getAllSubjects();
    foreach ($subjects as $subject) {
        if ($subject['name'] === $subjectName) {
            $subjectId = $subject['subject_id'];
            break;
        }
    }
    if (!$subjectId) {
        if (!createSubject(htmlspecialchars($subjectName, ENT_QUOTES, 'UTF-8'), 1)) {
            $errorInfo = $pdo->errorInfo();
            echo json_encode(['status' => 'error', 'message' => 'Gagal membuat mata kuliah: ' . $errorInfo[2]]);
            exit;
        }
        $subjectId = $pdo->lastInsertId();
    }

    // Simpan soal
    $savedCount = 0;
    foreach ($questions as $question) {
        if (!isset($question['question']) || !isset($question['options']) || !isset($question['correct']) || count($question['options']) !== 4) {
            continue; // Lewati soal yang tidak valid
        }

        // Acak urutan opsi tanpa encoding
        $options = $question['options'];
        $questionText = $question['question'];
        $correctIndex = $question['correct'];
        
        // Buat array indeks untuk acak
        $indices = [0, 1, 2, 3];
        shuffle($indices);
        
        // Buat array opsi baru berdasarkan indeks yang sudah diacak
        $shuffledOptions = [];
        $newCorrectIndex = -1;
        foreach ($indices as $newIndex => $oldIndex) {
            $shuffledOptions[$newIndex] = $options[$oldIndex];
            if ($oldIndex === $correctIndex) {
                $newCorrectIndex = $newIndex;
            }
        }

        // Gunakan indeks jawaban benar yang baru
        $correctOption = ['A', 'B', 'C', 'D'][$newCorrectIndex];

        // Simpan soal dengan opsi yang sudah diacak
        $result = createQuestion(
            $subjectId,
            $questionText,
            $shuffledOptions[0] . ($correctOption === 'A' ? ' (correct)' : ''),
            $shuffledOptions[1] . ($correctOption === 'B' ? ' (correct)' : ''),
            $shuffledOptions[2] . ($correctOption === 'C' ? ' (correct)' : ''),
            $shuffledOptions[3] . ($correctOption === 'D' ? ' (correct)' : ''),
            1
        );

        if ($result) {
            $savedCount++;
        } else {
            $errorInfo = $pdo->errorInfo();
            error_log("Failed to save question: " . $errorInfo[2]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => "$savedCount soal berhasil disimpan"]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .format-example {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-left: 4px solid #3b82f6;
        }
        
        .textarea-container {
            transition: all 0.3s ease;
        }
        
        .textarea-container:focus-within {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .preview-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .correct-answer {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }
        
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .preview-container {
            max-height: 600px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #3b82f6 #e2e8f0;
        }

        .preview-container::-webkit-scrollbar {
            width: 8px;
        }

        .preview-container::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 4px;
        }

        .preview-container::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 4px;
        }

        .preview-container::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }

        /* Penyesuaian untuk merapikan teks dan memberikan ruang di kanan */
        .option-text {
            display: inline-block;
            max-width: 90%;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-4">
        <button onclick="window.location.href='dashboard.php'" 
            class="mb-6 bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg font-medium flex items-center space-x-2 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Kembali ke Dashboard</span>
        </button>
    </div>
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - Input Area -->
            <div class="space-y-6">
                <!-- Subject Selection -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">📚 Nama Mata Kuliah</h3>
                    <input 
                        type="text" 
                        id="subject-input"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                        placeholder="Masukkan nama mata kuliah..."
                    >
                </div>

                <!-- Format Guide -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Format Penulisan Soal</h3>
                    <div class="format-example p-4 rounded-lg mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Gunakan format berikut:</p>
                        <div class="bg-white p-3 rounded border font-mono text-sm">
                            <div class="text-gray-600">Q: [Pertanyaan Anda]</div>
                            <div class="text-gray-600">A: [Pilihan A]</div>
                            <div class="text-gray-600">B: [Pilihan B]</div>
                            <div class="text-gray-600">C: [Pilihan C] (correct)</div>
                            <div class="text-gray-600">D: [Pilihan D]</div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium">Penting:</p>
                                <ul class="mt-1 space-y-1">
                                    <li>• Tambahkan <code class="bg-yellow-100 px-1 rounded">(correct)</code> setelah jawaban yang benar</li>
                                    <li>• Pisahkan setiap soal dengan baris kosong</li>
                                    <li>• Gunakan Q: untuk pertanyaan, A:B:C:D: untuk pilihan</li>
                                    <li>• Urutan opsi akan diacak saat disimpan</li>
                                    <li>• Karakter khusus seperti <, >, & akan ditangani dengan aman</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">✏️ Masukkan Soal</h3>
                        <button onclick="clearInput()" class="text-sm text-gray-500 hover:text-gray-700 underline">
                            Bersihkan
                        </button>
                    </div>
                    
                    <div class="textarea-container rounded-lg border-2 border-gray-300 focus-within:border-indigo-500">
                        <textarea 
                            id="questions-input" 
                            placeholder="Q: Apa ibu kota Indonesia?
A: Surabaya
B: Jakarta (correct)
C: Bandung
D: Medan
Q: Siapa presiden pertama Indonesia?
A: Soekarno (correct)
B: Suharto
C: B.J. Habibie
D: Megawati
Q: Berapa hasil dari 2 + 2?
A: 3
B: 4 (correct)
C: 5
D: 6"
                            class="w-full h-80 p-4 resize-none outline-none font-mono text-sm"
                            oninput="parseQuestions()"
                        >Q: Dalam Matematika Diskrit, apa itu himpunan?
A: Kumpulan bilangan bulat
B: Kumpulan objek tertentu yang terdefinisi dengan jelas (correct)
C: Kumpulan fungsi matematika
D: Kumpulan variabel acak

Q: Dalam Pemrograman Web, tag HTML apa yang digunakan untuk membuat tautan?
A: <link>
B: <a> (correct)
C: <href>
D: <url>

Q: Dalam Basis Data, apa fungsi utama perintah SQL SELECT?
A: Menghapus data
B: Menyisipkan data
C: Mengambil data (correct)
D: Memperbarui data

Q: Dalam Algoritma, apa itu Big-O notation?
A: Cara mengukur efisiensi memori
B: Cara mengukur kompleksitas waktu atau ruang suatu algoritma (correct)
C: Teknik pengurutan data
D: Struktur data pohon

Q: Dalam Jaringan Komputer, apa kepanjangan dari TCP?
A: Transmission Control Protocol (correct)
B: Transfer Control Process
C: Terminal Control Protocol
D: Transport Communication Protocol
</textarea>
                        <div class="flex justify-between items-center mt-4">
                            <div class="text-sm text-gray-600">
                                <span id="question-count">0</span> soal terdeteksi
                            </div>
                            <button onclick="saveQuestions()" id="save-btn" disabled 
                                    class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                💾 Simpan Soal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Preview -->
            <div class="space-y-6">
                <!-- Preview Header -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">👀 Preview Soal</h3>
                    <p class="text-gray-600 text-sm">Pratinjau soal yang akan disimpan</p>
                </div>

                <!-- Questions Preview -->
                <div id="questions-preview" class="space-y-4 preview-container">
                    <div class="bg-gray-50 rounded-xl p-8 text-center">
                        <div class="text-4xl mb-3">📝</div>
                        <p class="text-gray-600">Masukkan soal di sebelah kiri untuk melihat preview</p>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-xl p-6 shadow-lg" id="stats-card" style="display: none;">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">📊 Statistik</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600" id="total-questions-stat">0</div>
                            <div class="text-sm text-gray-600">Total Soal</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600" id="valid-questions-stat">0</div>
                            <div class="text-sm text-gray-600">Soal Valid</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" id="success-modal">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 fade-in text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Berhasil!</h3>
            <p class="text-gray-600 mb-6">
                <span id="saved-count">0</span> soal berhasil disimpan ke database
            </p>
            <div class="flex space-x-3">
                <button onclick="closeSuccessModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                    Tutup
                </button>
                <button onclick="addMoreQuestions()" 
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                    Tambah Lagi
                </button>
            </div>
        </div>
    </div>

    <script>
        let parsedQuestions = [];

        function parseQuestions() {
            const input = document.getElementById('questions-input').value;
            const lines = input.split('\n').map(line => line.trim()).filter(line => line);
            
            parsedQuestions = [];
            let currentQuestion = null;
            
            for (let line of lines) {
                if (line.startsWith('Q:')) {
                    if (currentQuestion && currentQuestion.question && currentQuestion.options.length === 4) {
                        parsedQuestions.push(currentQuestion);
                    }
                    
                    currentQuestion = {
                        question: line.substring(2).trim(),
                        options: [],
                        correct: -1
                    };
                } else if (line.match(/^[A-D]:/)) {
                    if (currentQuestion) {
                        const optionText = line.substring(2).trim();
                        const isCorrect = optionText.includes('(correct)');
                        const cleanText = optionText.replace('(correct)', '').trim();
                        
                        currentQuestion.options.push(cleanText);
                        
                        if (isCorrect) {
                            currentQuestion.correct = currentQuestion.options.length - 1;
                        }
                    }
                }
            }
            
            if (currentQuestion && currentQuestion.question && currentQuestion.options.length === 4) {
                parsedQuestions.push(currentQuestion);
            }
            
            updatePreview();
            updateStats();
        }

        function updatePreview() {
            const preview = document.getElementById('questions-preview');
            
            if (parsedQuestions.length === 0) {
                preview.innerHTML = `
                    <div class="bg-gray-50 rounded-xl p-8 text-center">
                        <div class="text-4xl mb-3">📝</div>
                        <p class="text-gray-600">Masukkan soal di sebelah kiri untuk melihat preview</p>
                    </div>
                `;
                return;
            }
            
            // Gunakan elemen dengan innerText untuk menampilkan teks secara normal
            preview.innerHTML = parsedQuestions.map((q, index) => `
                <div class="preview-card bg-white rounded-xl p-6 fade-in" data-question-index="${index}">
                    <div class="mb-4">
                        <div class="text-sm font-medium text-indigo-600 mb-2">Soal ${index + 1}</div>
                        <h4 class="font-semibold text-gray-800" id="preview-question-${index}">${q.question}</h4>
                    </div>
                    <div class="space-y-2">
                        ${q.options.map((option, optIndex) => `
                            <div class="p-3 rounded-lg border ${optIndex === q.correct ? 'correct-answer border-green-500' : 'border-gray-200 bg-gray-50'}" id="preview-option-${index}-${optIndex}">
                                <span class="font-medium">${String.fromCharCode(65 + optIndex)}.</span> <span class="option-text">${option}</span>
                                ${optIndex === q.correct ? '<span class="float-right">✓ Benar</span>' : ''}
                            </div>
                        `).join('')}
                    </div>
                    ${q.correct === -1 ? '<div class="mt-3 text-sm text-red-600">⚠️ Jawaban benar belum ditandai</div>' : ''}
                </div>
            `).join('');

            // Set innerText untuk memastikan teks ditampilkan sebagai teks biasa
            parsedQuestions.forEach((q, index) => {
                const questionElement = document.getElementById(`preview-question-${index}`);
                if (questionElement) questionElement.innerText = q.question;

                q.options.forEach((option, optIndex) => {
                    const optionElement = document.getElementById(`preview-option-${index}-${optIndex}`);
                    if (optionElement) {
                        const textSpan = optionElement.querySelector('.option-text');
                        if (textSpan) textSpan.innerText = option;
                    }
                });
            });
        }

        function updateStats() {
            const questionCount = parsedQuestions.length;
            const validQuestions = parsedQuestions.filter(q => q.correct !== -1).length;

            document.getElementById('question-count').textContent = questionCount;
            document.getElementById('total-questions-stat').textContent = questionCount;
            document.getElementById('valid-questions-stat').textContent = validQuestions;

            const statsCard = document.getElementById('stats-card');
            if (questionCount > 0) {
                statsCard.style.display = 'block';
            } else {
                statsCard.style.display = 'none';
            }

            const saveBtn = document.getElementById('save-btn');
            const subjectName = document.getElementById('subject-input').value.trim();
            saveBtn.disabled = !(validQuestions > 0 && subjectName);
        }

        function clearInput() {
            document.getElementById('questions-input').value = '';
            parseQuestions();
        }

        function saveQuestions() {
            const subjectName = document.getElementById('subject-input').value.trim();
            const validQuestions = parsedQuestions.filter(q => q.correct !== -1);

            if (!subjectName) {
                alert('⚠️ Masukkan nama mata kuliah terlebih dahulu!');
                return;
            }

            if (validQuestions.length === 0) {
                alert('⚠️ Tidak ada soal valid untuk disimpan!');
                return;
            }

            console.log('Sending data:', { subject: subjectName, questions: validQuestions });
            fetch('buat_soal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'subject=' + encodeURIComponent(subjectName) + '&questions=' + encodeURIComponent(JSON.stringify(validQuestions))
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('saved-count').textContent = validQuestions.length;
                    document.getElementById('success-modal').classList.remove('hidden');
                    document.getElementById('success-modal').classList.add('flex');
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Terjadi kesalahan saat menyimpan soal.');
            });

            const saveBtn = document.getElementById('save-btn');
            saveBtn.classList.add('success-animation');
            setTimeout(() => saveBtn.classList.remove('success-animation'), 600);
        }

        function closeSuccessModal() {
            document.getElementById('success-modal').classList.add('hidden');
            document.getElementById('success-modal').classList.remove('flex');
        }

        function addMoreQuestions() {
            closeSuccessModal();
            document.getElementById('questions-input').value = '';
            parseQuestions();
        }

        document.getElementById('subject-input').addEventListener('input', updateStats);

        document.getElementById('success-modal').addEventListener('click', function(e) {
            if (e.target === this) closeSuccessModal();
        });

        parseQuestions();
    </script>
</body>
</html>