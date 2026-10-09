<?php
// admin_actions.php - Process Admin Actions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/db.php';
require_once '../../includes/auth_helper.php';

// Enforce admin role
require_role(3);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$admin_user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        header("Location: ../staff_management.php?error=csrf_invalid");
        exit();
    }

    if ($action === 'create_staff') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $department = trim($_POST['department']);
        $hire_date = trim($_POST['hire_date']);
        $password = $_POST['password'];

        if (empty($full_name) || empty($email) || empty($phone) || empty($department) || empty($hire_date) || empty($password)) {
            header("Location: ../staff_management.php?error=empty_fields");
            exit();
        }

        try {
            $pdo->beginTransaction();

            // Check if email already registered
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $pdo->rollBack();
                header("Location: ../staff_management.php?error=email_exists");
                exit();
            }

            // Insert into users
            $hashed_pw = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (role_id, full_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([2, $full_name, $email, $phone, $hashed_pw]);
            $new_user_id = $pdo->lastInsertId();

            // Insert into staff_details
            $stmt = $pdo->prepare("INSERT INTO staff_accounts (user_id, department, hire_date, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$new_user_id, $department, $hire_date]);

            $pdo->commit();

            log_event($pdo, 'STAFF_CREATED', "Admin created staff account: $email");
            header("Location: ../staff_management.php?success=staff_added");
            exit();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header("Location: ../staff_management.php?error=db_error");
            exit();
        }
    }

} else {
    // GET Actions
    if ($action === 'toggle_staff_status') {
        $staff_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($staff_id <= 0) {
            header("Location: ../staff_management.php?error=invalid_id");
            exit();
        }

        try {
            // Fetch current status
            $stmt = $pdo->prepare("SELECT status, email FROM users WHERE id = ? AND role_id = 2");
            $stmt->execute([$staff_id]);
            $user = $stmt->fetch();

            if (!$user) {
                header("Location: ../staff_management.php?error=invalid_id");
                exit();
            }

            $new_status = ($user['status'] === 'active') ? 'inactive' : 'active';
            
            $pdo->beginTransaction();

            // Update user status
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $staff_id]);

            // Update staff details status
            $stmt = $pdo->prepare("UPDATE staff_accounts SET status = ? WHERE user_id = ?");
            $stmt->execute([$new_status, $staff_id]);

            $pdo->commit();

            log_event($pdo, 'STAFF_STATUS_TOGGLED', "Admin toggled status of staff account: " . $user['email'] . " to $new_status");
            header("Location: ../staff_management.php?success=status_toggled");
            exit();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header("Location: ../staff_management.php?error=db_error");
            exit();
        }
    }
}
header("Location: ../staff_management.php");
exit();
