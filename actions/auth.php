<?php
// auth.php - Process Authentication Actions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth_helper.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
            header("Location: ../register.php?error=empty_fields");
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../register.php?error=invalid_email");
            exit();
        }

        if ($password !== $confirm_password) {
            header("Location: ../register.php?error=password_mismatch");
            exit();
        }

        if (strlen($password) < 6) {
            header("Location: ../register.php?error=password_weak");
            exit();
        }

        try {
            // Check if email already registered
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                header("Location: ../register.php?error=email_exists");
                exit();
            }

            // Insert new user
            $hashed_pw = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (role_id, full_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([1, $full_name, $email, $phone, $hashed_pw]);
            
            $user_id = $pdo->lastInsertId();
            log_event($pdo, 'REGISTER_SUCCESS', "User registered successfully: $email");

            // Auto log in after registration
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role_id'] = 1;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;

            header("Location: ../customer/dashboard.php?success=registered");
            exit();

        } catch (PDOException $e) {
            header("Location: ../register.php?error=db_error");
            exit();
        }

    } elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            header("Location: ../login.php?error=empty_fields");
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    header("Location: ../login.php?error=account_deactivated");
                    exit();
                }

                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];

                log_event($pdo, 'LOGIN_SUCCESS', "User logged in: " . $user['email']);

                // Redirect based on role
                if ($user['role_id'] == 1) {
                    header("Location: ../customer/dashboard.php?success=logged_in");
                } elseif ($user['role_id'] == 2) {
                    header("Location: ../staff/dashboard.php?success=logged_in");
                } elseif ($user['role_id'] == 3) {
                    header("Location: ../admin/dashboard.php?success=logged_in");
                }
                exit();
            } else {
                log_event($pdo, 'LOGIN_FAILED', "Failed login attempt for: $email");
                header("Location: ../login.php?error=invalid_credentials");
                exit();
            }

        } catch (PDOException $e) {
            header("Location: ../login.php?error=db_error");
            exit();
        }
    } elseif ($action === 'forgot_password') {
        $email = trim($_POST['email']);
        if (empty($email)) {
            header("Location: ../login.php?error=forgot_empty_email");
            exit();
        }
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user_id = $stmt->fetchColumn();
            
            if ($user_id) {
                // In a real app we would send a reset email. For academic simulation, we reset to 'reset123'
                $default_pw = password_hash('reset123', PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$default_pw, $user_id]);
                
                log_event($pdo, 'PASSWORD_RESET', "Password reset for $email");
                header("Location: ../login.php?success=password_reset");
                exit();
            } else {
                header("Location: ../login.php?error=email_not_found");
                exit();
            }
        } catch (PDOException $e) {
            header("Location: ../login.php?error=db_error");
            exit();
        }
    }
}
header("Location: ../index.php");
exit();
