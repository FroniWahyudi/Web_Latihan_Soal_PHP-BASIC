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

        .error-animation {
            animation: errorShake 0.3s ease-in-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
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

        .option-text {
            display: inline-block;
            max-width: 90%;
            vertical-align: middle;
        }

        .error-input {
            border-color: #ef4444 !important;
            animation: errorShake 0.3s ease-in-out;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
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
                            <div class="text-gray-600">E: [Pilihan E, opsional]</div>
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
                                    <li>• Gunakan Q: untuk pertanyaan, A:B:C:D:E: untuk pilihan (E opsional)</li>
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
                        <div class="space-x-2">
                            <button onclick="fixFormat()" class="text-sm text-blue-500 hover:text-blue-700 underline">
                                Perbaiki Format
                            </button>
                            <button onclick="clearInput()" class="text-sm text-gray-500 hover:text-gray-700 underline">
                                Bersihkan
                            </button>
                        </div>
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
E: Abdurrahman Wahid

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
E: Traffic Control Protocol
</textarea>
                        <div class="flex justify-between items-center mt-4">
                            <div class="text-sm text-gray-600">
                                <span id="question-count">0</span> soal terdeteksi
                            </div>
                            <button onclick="saveQuestions()" id="save-btn" 
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
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
    <div class="modal-overlay" id="success-modal">
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

    <!-- Error Modal -->
    <div class="modal-overlay" id="error-modal">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 fade-in text-center">
            <div class="text-6xl mb-4 text-red-500">⚠️</div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Terjadi Kesalahan</h3>
            <p class="text-gray-600 mb-6" id="error-message">Terjadi kesalahan saat menyimpan soal.</p>
            <button onclick="closeErrorModal()" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                Tutup
            </button>
        </div>
    </div>

    <script>
        let parsedQuestions = [];

        function fixFormat() {
            console.log('Fixing format at:', new Date().toLocaleString('id-ID'));
            const textarea = document.getElementById('questions-input');
            let input = textarea.value.trim();
            if (!input) return;

            const lines = input.split('\n').map(line => line.trim()).filter(line => line);
            let fixedLines = [];
            let currentQuestion = null;
            let expectedOption = 0;

            for (let line of lines) {
                if (line.match(/^Q:/i)) {
                    if (currentQuestion && currentQuestion.options.length > 0) {
                        // Complete the previous question
                        while (currentQuestion.options.length < 4) {
                            currentQuestion.options.push(`[Opsi ${String.fromCharCode(65 + currentQuestion.options.length)} kosong]`);
                        }
                        fixedLines.push(`Q: ${currentQuestion.question}`);
                        currentQuestion.options.forEach((option, index) => {
                            let optionText = option;
                            if (index === currentQuestion.correct) {
                                optionText += ' (correct)';
                            }
                            fixedLines.push(`${String.fromCharCode(65 + index)}: ${optionText}`);
                        });
                        fixedLines.push('');
                    }
                    currentQuestion = {
                        question: line.substring(2).trim(),
                        options: [],
                        correct: -1
                    };
                    expectedOption = 0;
                } else if (line.match(/^[A-E]:/i)) {
                    if (!currentQuestion) {
                        currentQuestion = { question: '[Pertanyaan kosong]', options: [], correct: -1 };
                    }
                    const prefix = line[0].toUpperCase();
                    const expectedPrefix = String.fromCharCode(65 + expectedOption);
                    if (prefix === expectedPrefix) {
                        let optionText = line.substring(2).trim();
                        const isCorrect = optionText.includes('(correct)');
                        optionText = optionText.replace('(correct)', '').trim();
                        if (optionText === '') {
                            optionText = `[Opsi ${prefix} kosong]`;
                        }
                        currentQuestion.options.push(optionText);
                        if (isCorrect) {
                            currentQuestion.correct = currentQuestion.options.length - 1;
                        }
                        expectedOption++;
                    }
                }
            }

            // Handle the last question
            if (currentQuestion && currentQuestion.options.length > 0) {
                while (currentQuestion.options.length < 4) {
                    currentQuestion.options.push(`[Opsi ${String.fromCharCode(65 + currentQuestion.options.length)} kosong]`);
                }
                fixedLines.push(`Q: ${currentQuestion.question}`);
                currentQuestion.options.forEach((option, index) => {
                    let optionText = option;
                    if (index === currentQuestion.correct) {
                        optionText += ' (correct)';
                    }
                    fixedLines.push(`${String.fromCharCode(65 + index)}: ${optionText}`);
                });
                fixedLines.push('');
            }

            // Update textarea with fixed format
            textarea.value = fixedLines.join('\n').trim();
            parseQuestions();
        }

        function parseQuestions() {
            console.log('Parsing questions at:', new Date().toLocaleString('id-ID'));
            const input = document.getElementById('questions-input').value;
            const lines = input.split('\n').map(line => line.trim()).filter(line => line);
            
            parsedQuestions = [];
            let currentQuestion = null;
            let expectedOption = 0; // Track expected option (0=A, 1=B, 2=C, 3=D, 4=E)
            let lineNumber = 0; // Track line number for error reporting

            for (let line of lines) {
                lineNumber++;
                if (line.startsWith('Q:')) {
                    if (currentQuestion && currentQuestion.options.length >= 4) {
                        parsedQuestions.push(currentQuestion);
                    }
                    currentQuestion = {
                        question: line.substring(2).trim(),
                        options: [],
                        correct: -1,
                        lineStart: lineNumber
                    };
                    expectedOption = 0;
                } else if (line.match(/^[A-E]:/)) {
                    if (!currentQuestion) {
                        parsedQuestions.push({
                            question: '',
                            options: [],
                            correct: -1,
                            errors: [`Baris ${lineNumber}: Tidak ada pertanyaan (Q:) sebelum opsi ${line[0]}:`]
                        });
                        continue;
                    }

                    const prefix = line[0].toUpperCase();
                    const expectedPrefix = String.fromCharCode(65 + expectedOption); // A, B, C, D, E
                    if (prefix !== expectedPrefix) {
                        currentQuestion.errors = currentQuestion.errors || [];
                        currentQuestion.errors.push(`Baris ${lineNumber}: Diharapkan opsi ${expectedPrefix}:, ditemukan ${prefix}:`);
                        continue;
                    }

                    const optionText = line.substring(2).trim();
                    const isCorrect = optionText.includes('(correct)');
                    const cleanText = optionText.replace('(correct)', '').trim();
                    
                    if (cleanText === '') {
                        currentQuestion.errors = currentQuestion.errors || [];
                        currentQuestion.errors.push(`Baris ${lineNumber}: Opsi ${prefix}: kosong`);
                    } else {
                        currentQuestion.options.push(cleanText);
                    }
                    
                    if (isCorrect) {
                        if (currentQuestion.correct !== -1) {
                            currentQuestion.errors = currentQuestion.errors || [];
                            currentQuestion.errors.push(`Baris ${lineNumber}: Hanya satu opsi boleh ditandai (correct)`);
                        } else {
                            currentQuestion.correct = currentQuestion.options.length - 1;
                        }
                    }
                    
                    expectedOption++;
                    if (expectedOption > 4 && currentQuestion.options.length >= 4) {
                        if (currentQuestion.correct === -1) {
                            currentQuestion.errors = currentQuestion.errors || [];
                            currentQuestion.errors.push(`Soal di baris ${currentQuestion.lineStart}: Tidak ada jawaban benar (correct)`);
                        }
                        parsedQuestions.push(currentQuestion);
                        currentQuestion = null;
                        expectedOption = 0;
                    }
                } else {
                    if (currentQuestion) {
                        currentQuestion.errors = currentQuestion.errors || [];
                        currentQuestion.errors.push(`Baris ${lineNumber}: Format tidak valid, harap gunakan Q: atau A:/B:/C:/D:/E:`);
                    } else {
                        parsedQuestions.push({
                            question: '',
                            options: [],
                            correct: -1,
                            errors: [`Baris ${lineNumber}: Format tidak valid, harap gunakan Q: atau A:/B:/C:/D:/E:`]
                        });
                    }
                }
            }
            
            if (currentQuestion && currentQuestion.options.length > 0) {
                currentQuestion.errors = currentQuestion.errors || [];
                currentQuestion.errors.push(`Soal di baris ${currentQuestion.lineStart}: Kurang dari 4 opsi jawaban`);
                parsedQuestions.push(currentQuestion);
            }
            
            console.log('Parsed questions:', parsedQuestions);
            updatePreview();
            updateStats();
        }

        function updatePreview() {
            console.log('Updating preview at:', new Date().toLocaleString('id-ID'));
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
            
            preview.innerHTML = parsedQuestions.map((q, index) => {
                const errors = q.errors || [];
                if (q.question.trim() === '' && !errors.some(e => e.includes('Tidak ada pertanyaan'))) {
                    errors.push(`Soal ${index + 1}: Pertanyaan kosong`);
                }
                if (q.options.length < 4 && !errors.some(e => e.includes('Kurang dari 4 opsi'))) {
                    errors.push(`Soal ${index + 1}: Harus memiliki minimal 4 pilihan jawaban (ditemukan: ${q.options.length})`);
                }
                if (q.options.length > 5 && !errors.some(e => e.includes('Terlalu banyak opsi'))) {
                    errors.push(`Soal ${index + 1}: Maksimal 5 pilihan jawaban diperbolehkan (ditemukan: ${q.options.length})`);
                }
                if (q.options.some(opt => opt.trim() === '') && !errors.some(e => e.includes('Opsi'))) {
                    errors.push(`Soal ${index + 1}: Ada pilihan jawaban kosong`);
                }
                if (q.correct === -1 && !errors.some(e => e.includes('Tidak ada jawaban benar'))) {
                    errors.push(`Soal ${index + 1}: Jawaban benar belum ditandai`);
                }

                console.log(`Preview for question ${index + 1}:`, { question: q.question, options: q.options, correct: q.correct, errors });
                
                return `
                    <div class="preview-card bg-white rounded-xl p-6 fade-in" data-question-index="${index}">
                        <div class="mb-4">
                            <div class="text-sm font-medium text-indigo-600 mb-2">Soal ${index + 1}</div>
                            <h4 class="font-semibold text-gray-800" id="preview-question-${index}">${q.question || '[Pertanyaan kosong]'}</h4>
                        </div>
                        <div class="space-y-2">
                            ${q.options.length > 0 ? q.options.map((option, optIndex) => `
                                <div class="p-3 rounded-lg border ${optIndex === q.correct ? 'correct-answer border-green-500' : 'border-gray-200 bg-gray-50'}" id="preview-option-${index}-${optIndex}">
                                    <span class="font-medium">${String.fromCharCode(65 + optIndex)}.</span> <span class="option-text">${option || '[Opsi kosong]'}</span>
                                    ${optIndex === q.correct ? '<span class="float-right">✓ Benar</span>' : ''}
                                </div>
                            `).join('') : '<div class="text-red-600">Tidak ada opsi jawaban</div>'}
                        </div>
                        ${errors.length > 0 ? `
                            <div class="mt-3 text-sm text-red-600">
                                ${errors.map(error => `⚠️ ${error}`).join('<br>')}
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');

            parsedQuestions.forEach((q, index) => {
                const questionElement = document.getElementById(`preview-question-${index}`);
                if (questionElement) questionElement.innerText = q.question || '[Pertanyaan kosong]';

                q.options.forEach((option, optIndex) => {
                    const optionElement = document.getElementById(`preview-option-${index}-${optIndex}`);
                    if (optionElement) {
                        const textSpan = optionElement.querySelector('.option-text');
                        if (textSpan) textSpan.innerText = option || '[Opsi kosong]';
                    }
                });
            });
        }

        function updateStats() {
            console.log('Updating stats at:', new Date().toLocaleString('id-ID'));
            const questionCount = parsedQuestions.length;
            const validQuestions = parsedQuestions.filter(q => 
                q.correct !== -1 && 
                q.question.trim() !== '' && 
                q.options.length >= 4 &&
                q.options.length <= 5 &&
                !q.options.some(opt => opt.trim() === '') &&
                !q.errors
            ).length;

            document.getElementById('question-count').textContent = questionCount;
            document.getElementById('total-questions-stat').textContent = questionCount;
            document.getElementById('valid-questions-stat').textContent = validQuestions;

            const statsCard = document.getElementById('stats-card');
            if (questionCount > 0) {
                statsCard.style.display = 'block';
            } else {
                statsCard.style.display = 'none';
            }

            console.log('Stats updated:', { questionCount, validQuestions });
        }

        function clearInput() {
            console.log('Clearing input at:', new Date().toLocaleString('id-ID'));
            document.getElementById('questions-input').value = '';
            document.getElementById('subject-input').classList.remove('error-input');
            document.getElementById('questions-input').classList.remove('error-input');
            parseQuestions();
        }

        function showErrorModal(message, focusElementId = null) {
            console.log('Attempting to show error modal:', message, { focusElementId });
            const errorModal = document.getElementById('error-modal');
            const errorMessage = document.getElementById('error-message');
            
            if (!errorModal || !errorMessage) {
                console.error('Error modal or message element not found in DOM');
                alert('⚠️ ' + message);
                return;
            }

            errorMessage.textContent = message;
            errorModal.classList.remove('hidden');
            errorModal.classList.add('active');
            errorModal.classList.add('error-animation');
            console.log('Error modal displayed:', { message, focusElementId });
            setTimeout(() => errorModal.classList.remove('error-animation'), 300);
            
            if (focusElementId) {
                const element = document.getElementById(focusElementId);
                if (element) {
                    element.classList.add('error-input');
                    element.focus();
                    setTimeout(() => element.classList.remove('error-input'), 1000);
                } else {
                    console.error('Focus element not found:', focusElementId);
                }
            }
        }

        function closeErrorModal() {
            console.log('Closing error modal at:', new Date().toLocaleString('id-ID'));
            const errorModal = document.getElementById('error-modal');
            if (errorModal) {
                errorModal.classList.add('hidden');
                errorModal.classList.remove('active');
                document.getElementById('subject-input').classList.remove('error-input');
                document.getElementById('questions-input').classList.remove('error-input');
            } else {
                console.error('Error modal not found in DOM');
            }
        }

        function saveQuestions() {
            console.log('Save button clicked at:', new Date().toLocaleString('id-ID'));
            const subjectName = document.getElementById('subject-input').value.trim();
            const validQuestions = parsedQuestions.filter(q => 
                q.correct !== -1 && 
                q.question.trim() !== '' && 
                q.options.length >= 4 &&
                q.options.length <= 5 &&
                !q.options.some(opt => opt.trim() === '') &&
                !q.errors
            );

            console.log('Validation started:', { subjectName, totalQuestions: parsedQuestions.length, validQuestions: validQuestions.length });

            // Validasi sisi klien
            if (!subjectName) {
                console.log('Validation failed: Nama mata kuliah kosong');
                showErrorModal('Nama mata kuliah tidak boleh kosong! Silakan masukkan nama mata kuliah.', 'subject-input');
                return;
            }

            if (parsedQuestions.length === 0) {
                console.log('Validation failed: Tidak ada soal yang dimasukkan');
                showErrorModal('Tidak ada soal yang dimasukkan! Silakan masukkan setidaknya satu soal.', 'questions-input');
                return;
            }

            for (let [index, q] of parsedQuestions.entries()) {
                if (q.errors && q.errors.length > 0) {
                    console.log(`Validation failed: Format soal tidak valid pada soal ke-${index + 1}`, q.errors);
                    showErrorModal(`Format soal ke-${index + 1} tidak valid: ${q.errors.join(', ')}`, 'questions-input');
                    return;
                }
                if (q.question.trim() === '') {
                    console.log(`Validation failed: Pertanyaan kosong pada soal ke-${index + 1}`);
                    showErrorModal(`Pertanyaan pada soal ke-${index + 1} kosong! Silakan isi pertanyaan.`, 'questions-input');
                    return;
                }
                if (q.options.length < 4) {
                    console.log(`Validation failed: Soal ke-${index + 1} memiliki ${q.options.length} pilihan jawaban`);
                    showErrorModal(`Soal ke-${index + 1} harus memiliki minimal 4 pilihan jawaban (ditemukan: ${q.options.length}).`, 'questions-input');
                    return;
                }
                if (q.options.length > 5) {
                    console.log(`Validation failed: Soal ke-${index + 1} memiliki terlalu banyak pilihan jawaban`);
                    showErrorModal(`Soal ke-${index + 1} hanya boleh memiliki maksimal 5 pilihan jawaban (ditemukan: ${q.options.length}).`, 'questions-input');
                    return;
                }
                for (let [optIndex, option] of q.options.entries()) {
                    if (option.trim() === '') {
                        console.log(`Validation failed: Pilihan jawaban ${String.fromCharCode(65 + optIndex)} pada soal ke-${index + 1} kosong`);
                        showErrorModal(`Pilihan jawaban ${String.fromCharCode(65 + optIndex)} pada soal ke-${index + 1} kosong! Silakan isi semua pilihan jawaban.`, 'questions-input');
                        return;
                    }
                }
                if (q.correct === -1) {
                    console.log(`Validation failed: Jawaban benar tidak ditandai pada soal ke-${index + 1}`);
                    showErrorModal(`Jawaban benar pada soal ke-${index + 1} belum ditandai! Gunakan (correct) untuk menandai jawaban benar.`, 'questions-input');
                    return;
                }
            }

            const dataToSend = { subject: subjectName, questions: validQuestions };
            console.log('Data to send:', dataToSend);

            fetch('buat_soal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'subject=' + encodeURIComponent(subjectName) + '&questions=' + encodeURIComponent(JSON.stringify(validQuestions))
            })
            .then(response => {
                console.log('Server response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Server response data:', data);
                if (data.status === 'success') {
                    document.getElementById('saved-count').textContent = validQuestions.length;
                    const successModal = document.getElementById('success-modal');
                    if (successModal) {
                        successModal.classList.remove('hidden');
                        successModal.classList.add('active');
                        console.log('Success modal displayed');
                    } else {
                        console.error('Success modal not found in DOM');
                        alert('🎉 ' + data.message);
                    }
                } else {
                    showErrorModal(data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showErrorModal('Terjadi kesalahan saat menyimpan soal. Silakan coba lagi.');
            });

            const saveBtn = document.getElementById('save-btn');
            saveBtn.classList.add('success-animation');
            setTimeout(() => saveBtn.classList.remove('success-animation'), 600);
        }

        function closeSuccessModal() {
            console.log('Closing success modal at:', new Date().toLocaleString('id-ID'));
            const successModal = document.getElementById('success-modal');
            if (successModal) {
                successModal.classList.add('hidden');
                successModal.classList.remove('active');
            } else {
                console.error('Success modal not found in DOM');
            }
        }

        function addMoreQuestions() {
            console.log('Adding more questions at:', new Date().toLocaleString('id-ID'));
            closeSuccessModal();
            document.getElementById('questions-input').value = '';
            parseQuestions();
        }

        document.getElementById('subject-input').addEventListener('input', () => {
            document.getElementById('subject-input').classList.remove('error-input');
            updateStats();
        });

        document.getElementById('questions-input').addEventListener('input', () => {
            document.getElementById('questions-input').classList.remove('error-input');
            parseQuestions();
        });

        document.getElementById('success-modal').addEventListener('click', function(e) {
            if (e.target === this) closeSuccessModal();
        });

        document.getElementById('error-modal').addEventListener('click', function(e) {
            if (e.target === this) closeErrorModal();
        });

        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, checking modals');
            if (!document.getElementById('error-modal')) {
                console.error('Error modal not found in DOM on load');
            }
            if (!document.getElementById('success-modal')) {
                console.error('Success modal not found in DOM on load');
            }
            parseQuestions();
        });
    </script>
</body>
</html>