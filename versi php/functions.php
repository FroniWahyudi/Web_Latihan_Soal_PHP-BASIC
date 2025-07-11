<?php
// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'web_soal_php_basic');

// Membuat koneksi ke database menggunakan PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

/**
 * Fungsi untuk Pengguna (Users)
 */
function createUser($email, $username, $password, $name, $role) {
    global $pdo;
    $password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, username, password, name, role) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$email, $username, $password, $name, $role]);
}

function getAllUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateUser($id, $email, $username, $password, $name, $role) {
    global $pdo;
    $password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ?, password = ?, name = ?, role = ? WHERE user_id = ?");
    return $stmt->execute([$email, $username, $password, $name, $role, $id]);
}

function deleteUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    return $stmt->execute([$id]);
}

/**
 * Fungsi untuk Mata Kuliah (Subjects)
 */
function createSubject($name, $created_by) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (name, created_by) VALUES (?, ?)");
        return $stmt->execute([$name, $created_by]);
    } catch (PDOException $e) {
        error_log("Error creating subject: " . $e->getMessage());
        return false;
    }
}

function getAllSubjects() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM subjects");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSubjectById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateSubject($id, $name, $created_by) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE subjects SET name = ?, created_by = ? WHERE subject_id = ?");
    return $stmt->execute([$name, $created_by, $id]);
}

function deleteSubject($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
    return $stmt->execute([$id]);
}

/**
 * Fungsi untuk Soal (Questions)
 */
function createQuestion($subject_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $created_by, $option_e = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO questions (subject_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$subject_id, $question_text, $option_a, $option_b, $option_c, $option_d, $option_e, $correct_option, $created_by]);
}

function getAllQuestions() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM questions");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getQuestionsBySubject($subject_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE subject_id = ? ORDER BY created_at");
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getQuestionById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE question_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateQuestion($id, $subject_id, $question_text, $option_a, $option_b, $option_c, $option_d, $option_e, $correct_option, $created_by) {
    global $pdo;
    try {
        // Validasi correct_option
        if (!in_array($correct_option, ['A', 'B', 'C', 'D', 'E'])) {
            throw new Exception("Jawaban yang benar harus A, B, C, D, atau E.");
        }
        $stmt = $pdo->prepare("UPDATE questions SET subject_id = ?, question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, option_e = ?, correct_option = ?, created_by = ? WHERE question_id = ?");
        return $stmt->execute([$subject_id, $question_text, $option_a, $option_b, $option_c, $option_d, $option_e, $correct_option, $created_by, $id]);
    } catch (Exception $e) {
        error_log("Error updating question: " . $e->getMessage());
        return false;
    }
}

function deleteQuestion($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM questions WHERE question_id = ?");
    return $stmt->execute([$id]);
}

/**
 * Fungsi untuk Hasil Kuis (Quiz Results)
 */
function createQuizResult($user_id, $subject_id, $score, $total_questions) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, subject_id, score, total_questions) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $subject_id, $score, $total_questions]);
}

function getAllQuizResults() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM quiz_results");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getQuizResultsByUserId($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fungsi untuk Percobaan Kuis (Quiz Attempts)
 */
function createQuizAttempt($result_id, $question_id, $selected_option, $is_correct) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO quiz_attempts (result_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$result_id, $question_id, $selected_option, $is_correct]);
}

function getAllQuizAttempts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM quiz_attempts");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getQuizAttemptsByResultId($result_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE result_id = ?");
    $stmt->execute([$result_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function loginUser($email_or_username, $password) {
    $conn = new mysqli('localhost', 'root', '', 'web_soal_php_basic');

    if ($conn->connect_error) {
        error_log("Koneksi database gagal: " . $conn->connect_error);
        return ['status' => 'error', 'message' => 'Koneksi database gagal: ' . $conn->connect_error];
    }

    $stmt = $conn->prepare("SELECT user_id, name, password FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email_or_username, $email_or_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        error_log("User ditemukan: " . print_r($user, true)); // Debugging
        if ($password === $user['password']) {
            $update_stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
            $stmt->close();
            $conn->close();
            return ['status' => 'success', 'user' => ['user_id' => $user['user_id'], 'name' => $user['name']]];
        } else {
            error_log("Kata sandi salah untuk $email_or_username");
            $stmt->close();
            $conn->close();
            return ['status' => 'error', 'message' => 'Kata sandi salah'];
        }
    } else {
        error_log("Pengguna tidak ditemukan: $email_or_username");
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'Pengguna tidak ditemukan'];
    }
}
function deleteSubjectWithQuestions($id) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM questions WHERE subject_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
        $stmt->execute([$id]);
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting subject with questions: " . $e->getMessage());
        return false;
    }
}
?>