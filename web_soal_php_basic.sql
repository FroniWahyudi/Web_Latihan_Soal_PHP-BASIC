-- Menghapus database jika sudah ada untuk memastikan keadaan bersih
DROP DATABASE IF EXISTS web_soal_php_basic;

-- Membuat database baru
CREATE DATABASE web_soal_php_basic
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE web_soal_php_basic;

-- Tabel untuk pengguna
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Tabel untuk mata kuliah
CREATE TABLE subjects (
    subject_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    last_accessed DATETIME,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_name (name)
);

-- Tabel untuk soal
CREATE TABLE questions (
    question_id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) GENERATED ALWAYS AS (
        CASE 
            WHEN option_a LIKE '%(correct)%' THEN 'A'
            WHEN option_b LIKE '%(correct)%' THEN 'B'
            WHEN option_c LIKE '%(correct)%' THEN 'C'
            WHEN option_d LIKE '%(correct)%' THEN 'D'
            ELSE NULL
        END
    ) STORED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_subject_id (subject_id)
);

-- Tabel untuk hasil kuis
CREATE TABLE quiz_results (
    result_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    subject_id INT,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage FLOAT GENERATED ALWAYS AS (score / total_questions * 100) STORED,
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_subject_id (subject_id)
);

-- Tabel untuk percobaan menjawab soal
CREATE TABLE quiz_attempts (
    attempt_id INT PRIMARY KEY AUTO_INCREMENT,
    result_id INT,
    question_id INT,
    selected_option CHAR(1),
    is_correct BOOLEAN,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (result_id) REFERENCES quiz_results(result_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
    INDEX idx_result_id (result_id),
    INDEX idx_question_id (question_id)
);

-- Insert data pengguna (dengan hash password yang valid)
INSERT INTO users (email, username, password, name, role)
SELECT 'admin@edukasi.com', 'admin', 'admin123', 'Admin User', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@edukasi.com');

INSERT INTO users (email, username, password, name, role)
SELECT 'demo@edukasi.com', 'demo', 'demo123', 'Demo User', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'demo@edukasi.com');

-- Insert data mata kuliah
INSERT INTO subjects (name, created_by)
SELECT 'Matematika Diskrit', 1
WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE name = 'Matematika Diskrit');

INSERT INTO subjects (name, created_by)
SELECT 'Pemrograman Web', 1
WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE name = 'Pemrograman Web');

INSERT INTO subjects (name, created_by)
SELECT 'Basis Data', 1
WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE name = 'Basis Data');

INSERT INTO subjects (name, created_by)
SELECT 'Algoritma', 1
WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE name = 'Algoritma');

INSERT INTO subjects (name, created_by)
SELECT 'Jaringan Komputer', 1
WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE name = 'Jaringan Komputer');

-- Insert data soal
INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, created_by)
SELECT 
    (SELECT subject_id FROM subjects WHERE name = 'Matematika Diskrit'),
    'Dalam Matematika Diskrit, apa itu himpunan?',
    'Kumpulan bilangan bulat',
    'Kumpulan objek tertentu yang terdefinisi dengan jelas (correct)',
    'Kumpulan fungsi matematika',
    'Kumpulan variabel acak',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM questions WHERE question_text = 'Dalam Matematika Diskrit, apa itu himpunan?'
);

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, created_by)
SELECT 
    (SELECT subject_id FROM subjects WHERE name = 'Pemrograman Web'),
    'Dalam Pemrograman Web, tag HTML apa yang digunakan untuk membuat tautan?',
    '<link>',
    '<a> (correct)',
    '<href>',
    '<url>',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM questions WHERE question_text = 'Dalam Pemrograman Web, tag HTML apa yang digunakan untuk membuat tautan?'
);

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, created_by)
SELECT 
    (SELECT subject_id FROM subjects WHERE name = 'Basis Data'),
    'Dalam Basis Data, apa fungsi utama perintah SQL SELECT?',
    'Menghapus data',
    'Menyisipkan data',
    'Mengambil data (correct)',
    'Memperbarui data',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM questions WHERE question_text = 'Dalam Basis Data, apa fungsi utama perintah SQL SELECT?'
);

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, created_by)
SELECT 
    (SELECT subject_id FROM subjects WHERE name = 'Algoritma'),
    'Dalam Algoritma, apa itu Big-O notation?',
    'Cara mengukur efisiensi memori',
    'Cara mengukur kompleksitas waktu atau ruang suatu algoritma (correct)',
    'Teknik pengurutan data',
    'Struktur data pohon',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM questions WHERE question_text = 'Dalam Algoritma, apa itu Big-O notation?'
);

INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, created_by)
SELECT 
    (SELECT subject_id FROM subjects WHERE name = 'Jaringan Komputer'),
    'Dalam Jaringan Komputer, apa kepanjangan dari TCP?',
    'Transmission Control Protocol (correct)',
    'Transfer Control Process',
    'Terminal Control Protocol',
    'Transport Communication Protocol',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM questions WHERE question_text = 'Dalam Jaringan Komputer, apa kepanjangan dari TCP?'
);