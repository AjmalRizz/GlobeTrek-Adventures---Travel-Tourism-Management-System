<?php
// logout.php - Terminate session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
header("Location: index.php?success=logged_out");
exit();
