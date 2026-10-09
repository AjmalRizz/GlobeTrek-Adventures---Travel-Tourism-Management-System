<?php
// header.php - GlobeTrek Adventures Global Header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// User details if logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['full_name'] : '';
$user_role_id = $is_logged_in ? $_SESSION['role_id'] : 0; // 1: Customer, 2: Staff, 3: Admin
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore Negombo and Sri Lanka with GlobeTrek Adventures. Book custom tours, hotels, and local transport options today!">
    <title>GlobeTrek Adventures - Travel & Tourism Management System</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Style System -->
    <link href="<?php echo (strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : ''; ?>assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Theme Preloader to prevent FOUC -->
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>

<!-- Dynamic Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo (strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : ''; ?>index.php">
            <i class="bi bi-compass-fill me-2 text-primary"></i>GlobeTrek<span>Adventures</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="bi bi-list fs-2 text-dark"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <?php
                // Base Path Helper
                $base = (strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : '';
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'packages.php' || $current_page === 'package_details.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>packages.php">Packages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'accommodation.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>accommodation.php">Accommodation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'transportation.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>transportation.php">Transportation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'guides.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>guides.php">Guides</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>contact.php">Contact</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center gap-2">
                <!-- Theme Toggle Button -->
                <button id="themeToggleBtn" class="btn border-0 rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: var(--primary-light); color: var(--primary); font-size: 1.25rem; transition: var(--transition);" aria-label="Toggle theme">
                    <i id="themeToggleIcon" class="bi bi-moon-fill"></i>
                </button>
                <!-- Currency Toggle Dropdown -->
                <select id="currencySelect" class="form-select border-0 rounded-pill px-3 py-1 fw-bold text-center" style="width: auto; background-color: var(--primary-light); color: var(--primary); font-size: 0.85rem; height: 40px; cursor: pointer; display: flex; align-items: center; justify-content: center; outline: none; transition: var(--transition);">
                    <option value="USD">USD ($)</option>
                    <option value="LKR">LKR (₨)</option>
                </select>
                <?php if ($is_logged_in): ?>
                    <!-- Logged In Dropdown based on Role -->
                    <!-- Logged In Dropdown based on Role -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 px-3 py-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50px; background-color: var(--surface); border: 1.5px solid var(--border); height: 40px;">
                            <i class="bi bi-list fs-5 text-dark"></i>
                            <i class="bi bi-person-circle fs-5 text-primary"></i>
                            <span class="small fw-semibold text-dark d-none d-sm-inline">Hi, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2" aria-labelledby="userMenu" style="border-radius:12px; min-width: 200px;">
                            <?php if ($user_role_id == 1): ?>
                                <li><a class="dropdown-item py-2" href="<?php echo $base; ?>customer/dashboard.php"><i class="bi bi-grid-fill me-2 text-primary"></i>My Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="<?php echo $base; ?>customer/bookings.php"><i class="bi bi-journal-check me-2 text-primary"></i>My Bookings</a></li>
                            <?php elseif ($user_role_id == 2): ?>
                                <li><a class="dropdown-item py-2" href="<?php echo $base; ?>staff/dashboard.php"><i class="bi bi-speedometer2 me-2 text-secondary"></i>Staff Dashboard</a></li>
                            <?php elseif ($user_role_id == 3): ?>
                                <li><a class="dropdown-item py-2" href="<?php echo $base; ?>admin/dashboard.php"><i class="bi bi-shield-lock-fill me-2 text-accent"></i>Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="<?php echo $base; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Normal Guest Login / Register Buttons (Desktop/Unscrolled View) -->
                    <div class="guest-normal-buttons d-flex align-items-center gap-2">
                        <a href="<?php echo $base; ?>login.php" class="btn btn-outline-secondary px-4 py-2 fw-semibold border-2" style="border-radius: 50px; height: 40px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;">Login</a>
                        <a href="<?php echo $base; ?>register.php" class="btn btn-custom-primary px-4 py-2 text-white fw-semibold" style="border-radius: 50px; height: 40px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;">Register</a>
                    </div>
                    
                    <!-- Scrolled Guest Account Menu Dropdown -->
                    <div class="dropdown guest-scrolled-dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 px-3 py-2" type="button" id="guestMenu" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50px; background-color: var(--surface); border: 1.5px solid var(--border); height: 40px;">
                            <i class="bi bi-list fs-5 text-dark"></i>
                            <i class="bi bi-person-circle fs-5 text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2" aria-labelledby="guestMenu" style="border-radius:12px; min-width: 160px;">
                            <li><a class="dropdown-item py-2" href="<?php echo $base; ?>login.php"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Login</a></li>
                            <li><a class="dropdown-item py-2" href="<?php echo $base; ?>register.php"><i class="bi bi-person-plus-fill me-2 text-secondary"></i>Register</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Glassmorphic Destination Search Pill Bar -->
<div class="nav-glass-search-bar" id="navGlassSearchBar">
    <div class="container d-flex justify-content-center">
        <form action="<?php echo $base; ?>packages.php" method="GET" class="nav-glass-search-form" role="search" aria-label="Destination Search">
            <div class="nav-glass-pill">
                <span class="nav-glass-icon"><i class="bi bi-geo-alt-fill text-primary"></i></span>
                <select name="destination" id="navDestSearch" class="nav-glass-select" aria-label="Select destination">
                    <option value="">Where do you want to go?</option>
                    <option value="Negombo">🌊 Negombo &amp; Golden Coast</option>
                    <option value="Sigiriya">🏔 Sigiriya &amp; Dambulla</option>
                    <option value="Galle">⚓ Galle &amp; Mirissa</option>
                    <option value="Kandy">🌿 Kandy &amp; Temple</option>
                    <option value="Ella">🚂 Ella &amp; Hill Country</option>
                    <option value="Maldives">🏝 Maldives Islands</option>
                    <option value="Dubai">🌇 Dubai, UAE</option>
                    <option value="Bali">🌺 Bali, Indonesia</option>
                </select>
                <button type="submit" class="btn-glass-search-pill" aria-label="Search">Search</button>
            </div>
        </form>
    </div>
</div>

<main>

