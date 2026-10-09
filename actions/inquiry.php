<?php
// inquiry.php - Process Customer Inquiries

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        header("Location: ../contact.php?error=csrf_invalid");
        exit();
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    $user_role = isset($_SESSION['role_id']) ? intval($_SESSION['role_id']) : 0;

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $redir = ($user_role === 1) ? '../customer/inquiries.php?error=empty_fields' : '../contact.php?error=empty_fields';
        header("Location: $redir");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $redir = ($user_role === 1) ? '../customer/inquiries.php?error=invalid_email' : '../contact.php?error=invalid_email';
        header("Location: $redir");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO inquiries (user_id, name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $email, $phone, $subject, $message, 'new']);

        log_event($pdo, 'INQUIRY_SUBMITTED', "Inquiry submitted by name: $name, email: $email");

        $redir = ($user_role === 1) ? '../customer/inquiries.php?success=inquiry_submitted' : '../contact.php?success=inquiry_submitted';
        header("Location: $redir");
        exit();

    } catch (PDOException $e) {
        $redir = ($user_role === 1) ? '../customer/inquiries.php?error=db_error' : '../contact.php?error=db_error';
        header("Location: $redir");
        exit();
    }
} else {
    header("Location: ../contact.php");
    exit();
}
