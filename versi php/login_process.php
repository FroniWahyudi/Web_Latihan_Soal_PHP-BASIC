<?php
include 'functions.php';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $result = loginUser($_POST['email'], $_POST['password']);
    if ($result['status'] == 'success') {
        header('Location: dashboard.php?login=success');
    } else {
        header('Location: login.php?login=error');
    }
} else {
    header('Location: Login.php');
}
?>