<?php
// setup_db.php - GlobeTrek Adventures Database Setup & Seeder
// ---------------------------------------------------------------
// SECURITY GATE: This script must NEVER be accessible on a public
// server. It will only execute when run from the command line (CLI)
// or when accessed from localhost (127.0.0.1 / ::1) in a browser.
// ---------------------------------------------------------------
$is_cli = (php_sapi_name() === 'cli');
if (!$is_cli) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($remote, ['127.0.0.1', '::1'], true)) {
        http_response_code(403);
        die('403 Forbidden: This setup script cannot be run remotely.');
    }
}

// Read DB credentials from environment variables (same as db.php)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'globetrek_db');
$log = [];

function add_log($msg, $success = true) {
    global $log, $is_cli;
    $status = $success ? "SUCCESS" : "ERROR";
    $formatted = "[$status] $msg";
    $log[] = ['msg' => $msg, 'success' => $success];
    if ($is_cli) {
        echo $formatted . "\n";
    }
}

try {
    // 1. Connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    add_log("Connected to MySQL server.");

    // 2. Database Creation
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    add_log("Database '" . DB_NAME . "' verified/created.");
    
    $pdo->exec("USE `" . DB_NAME . "`");
    add_log("Selected database '" . DB_NAME . "'.");

    // 3. Drop existing tables to ensure clean rebuild
    $tables = [
        'audit_logs', 'reports', 'staff_accounts', 'custom_travel_requests',
        'inquiries', 'payments', 'booking_details', 'bookings',
        'package_images', 'travel_packages', 'transport_services',
        'accommodations', 'users', 'user_roles'
    ];
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    foreach ($tables as $tbl) {
        $pdo->exec("DROP TABLE IF EXISTS `$tbl`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    add_log("Cleared any pre-existing database tables to ensure fresh setup.");

    // 4. Create Tables
    // user_roles
    $pdo->exec("CREATE TABLE user_roles (
        id INT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB;");
    add_log("Table 'user_roles' created.");

    // users
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_id INT NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES user_roles(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB;");
    add_log("Table 'users' created.");

    // travel_packages
    $pdo->exec("CREATE TABLE travel_packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        destination VARCHAR(100) NOT NULL,
        duration_days INT NOT NULL,
        activities VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) DEFAULT 'available',
        description TEXT NOT NULL,
        itinerary TEXT NOT NULL,
        included TEXT NOT NULL,
        excluded TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    add_log("Table 'travel_packages' created.");

    // package_images
    $pdo->exec("CREATE TABLE package_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        FOREIGN KEY (package_id) REFERENCES travel_packages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    add_log("Table 'package_images' created.");

    // bookings
    $pdo->exec("CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_ref VARCHAR(20) NOT NULL UNIQUE,
        user_id INT NOT NULL,
        package_id INT NOT NULL,
        travel_date DATE NOT NULL,
        num_travelers INT NOT NULL,
        special_requests TEXT,
        total_price DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (package_id) REFERENCES travel_packages(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB;");
    add_log("Table 'bookings' created.");

    // booking_details
    $pdo->exec("CREATE TABLE booking_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        status VARCHAR(20) NOT NULL,
        notes TEXT,
        updated_by INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    add_log("Table 'booking_details' created.");

    // payments
    $pdo->exec("CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        card_holder VARCHAR(100) NOT NULL,
        payment_ref VARCHAR(50) NOT NULL UNIQUE,
        status VARCHAR(20) NOT NULL DEFAULT 'success',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    add_log("Table 'payments' created.");

    // inquiries
    $pdo->exec("CREATE TABLE inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(20) NULL,
        subject VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'new',
        response TEXT NULL,
        responded_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    add_log("Table 'inquiries' created.");

    // custom_travel_requests
    $pdo->exec("CREATE TABLE custom_travel_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        destination VARCHAR(100) NOT NULL,
        duration_days INT NOT NULL,
        accommodation_type VARCHAR(50) NOT NULL,
        transport_preference VARCHAR(50) NOT NULL,
        activities TEXT NOT NULL,
        budget DECIMAL(10,2) NOT NULL,
        special_requests TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        staff_response TEXT NULL,
        reviewed_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    add_log("Table 'custom_travel_requests' created.");

    // accommodations
    $pdo->exec("CREATE TABLE accommodations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        type VARCHAR(50) NOT NULL,
        rating DECIMAL(3,1) NOT NULL,
        location VARCHAR(100) NOT NULL,
        price_per_night DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    add_log("Table 'accommodations' created.");

    // transport_services
    $pdo->exec("CREATE TABLE transport_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        type VARCHAR(50) NOT NULL,
        capacity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        description TEXT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    add_log("Table 'transport_services' created.");

    // staff_accounts
    $pdo->exec("CREATE TABLE staff_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        department VARCHAR(100) NOT NULL,
        hire_date DATE NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    add_log("Table 'staff_accounts' created.");

    // reports
    $pdo->exec("CREATE TABLE reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_type VARCHAR(50) NOT NULL,
        generated_by INT NOT NULL,
        parameters TEXT NULL,
        file_path VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    add_log("Table 'reports' created.");

    // audit_logs
    $pdo->exec("CREATE TABLE audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    add_log("Table 'audit_logs' created.");

    // 5. Seed Data
    // User Roles
    $pdo->exec("INSERT INTO user_roles (id, role_name) VALUES 
        (1, 'Customer'),
        (2, 'Staff'),
        (3, 'Admin');");
    add_log("Seeded 'user_roles'.");

    // Users (admin123, staff123, traveler123)
    $pw_admin = password_hash('admin123', PASSWORD_BCRYPT);
    $pw_staff = password_hash('staff123', PASSWORD_BCRYPT);
    $pw_traveler = password_hash('traveler123', PASSWORD_BCRYPT);

    $pdo->exec("INSERT INTO users (role_id, full_name, email, phone, password, status) VALUES 
        (3, 'GlobeTrek Admin', 'admin@globetrek.com', '+94312221234', '$pw_admin', 'active'),
        (2, 'Sunil Perera (Staff)', 'staff@globetrek.com', '+94771234567', '$pw_staff', 'active'),
        (1, 'Jane Doe (Traveler)', 'traveler@gmail.com', '+15556667777', '$pw_traveler', 'active');");
    add_log("Seeded default users (Admin, Staff, Customer).");

    // Link staff account
    $staff_user_id = $pdo->query("SELECT id FROM users WHERE email = 'staff@globetrek.com'")->fetchColumn();
    $admin_user_id = $pdo->query("SELECT id FROM users WHERE email = 'admin@globetrek.com'")->fetchColumn();
    $customer_user_id = $pdo->query("SELECT id FROM users WHERE email = 'traveler@gmail.com'")->fetchColumn();

    $pdo->exec("INSERT INTO staff_accounts (user_id, department, hire_date, status) VALUES 
        ($staff_user_id, 'Tour Management', '2025-01-15', 'active');");
    add_log("Seeded staff details.");

    // Accommodations
    $pdo->exec("INSERT INTO accommodations (name, type, rating, location, price_per_night, image_path, description) VALUES 
        ('Jetwing Blue', 'Resort', 4.8, 'Negombo Beach, Sri Lanka', 150.00, 'assets/images/jetwing_blue.jpg', 'A luxury 5-star beachfront resort in Negombo featuring elegant rooms, two large swimming pools, a spa, and exceptional dining with gorgeous sunset views of the Indian Ocean.'),
        ('Heritance Negombo', 'Hotel', 4.7, 'Negombo Beach, Sri Lanka', 180.00, 'assets/images/heritance_negombo.jpg', 'Positioned in the heart of Negombo\'s coastal stretch, Heritance Negombo offers premium rooms, an infinity pool, state-of-the-art facilities, and premium seafood cuisines.'),
        ('Negombo Beach Guest House', 'Guest House', 4.3, 'Lewis Place, Negombo', 45.00, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&q=80&w=800', 'A cozy, clean, and budget-friendly guest house located just a 2-minute walk from Negombo beach. Exquisite Sri Lankan hospitality, breakfast included.');");
    add_log("Seeded accommodations catalog.");

    // Transport Services
    $pdo->exec("INSERT INTO transport_services (name, type, capacity, price, description, image_path) VALUES 
        ('Private Luxury SUV', 'Private Vehicle', 5, 80.00, 'Chauffeur-driven luxury SUV available for half-day or full-day hires. Includes English-speaking guide-driver, fuel, and highway tolls.', 'assets/images/transport_car.png'),
        ('Coaster Tour Bus', 'Tour Bus', 29, 250.00, 'Spacious, clean, air-conditioned tour bus perfect for larger groups, corporate trips, or extended family tours around Sri Lanka.', 'assets/images/transport_van.png'),
        ('Sri Lankan Tuk-Tuk City Ride', 'Tuk-Tuk', 3, 8.00, 'Experience the authentic Sri Lankan way of getting around. Our tuk-tuks are clean, fun, and perfect for short city rides around Negombo, markets, and beach strips.', 'assets/images/transport_tuktuk.png');");
    add_log("Seeded transportation services catalog.");

    // Travel Packages
    $pdo->exec("INSERT INTO travel_packages (title, destination, duration_days, activities, price, status, description, itinerary, included, excluded) VALUES 
        ('Negombo Lagoon & Heritage Experience', 'Negombo, Sri Lanka', 2, 'Boat Safari, Dutch Fort Visit, Fish Market Tour', 95.00, 'available', 
         'Explore the rich colonial history and pristine aquatic environment of Negombo. Sail through the Negombo Lagoon, cruise the historical Dutch canal, and visit the bustling fish market (Lellama).',
         'Day 1: Lagoon Boat Safari & Dutch Canal cruise. Evening visit to Negombo Dutch Fort.\r\nDay 2: Morning visit to Lellama Fish Market, St. Mary\'s Church, and shopping at Negombo beach road.',
         'AC transport, Professional boat guide, Entrance fees, Mineral water, Sri Lankan seafood lunch.',
         'Hotel accommodation, Dinner, Tips & Gratuities, Personal expenses.'),
         
        ('Sri Lanka Cultural Triangle Tour', 'Sigiriya & Dambulla', 4, 'Rock Climbing, Cave Temple Explorer, Village Safari', 290.00, 'available', 
         'Dive deep into ancient history by visiting the Sigiriya Lion Rock Fortress, the golden temple of Dambulla, and experiencing traditional village life in Habarana.',
         'Day 1: Transfer from Negombo to Sigiriya. Climb the fortress at sunset.\r\nDay 2: Morning safari in Minneriya National Park to watch wild elephants. Traditional bullock cart ride and village lunch.\r\nDay 3: Visit Polonnaruwa ancient city ruins. Overnight in Kandy.\r\nDay 4: Visit Temple of the Sacred Tooth Relic in Kandy. Return transfer to Negombo.',
         '3 Nights hotel accommodation (Half-Board), English-speaking chauffeur-guide, Safari jeep rental, Entry tickets.',
         'Lunch, Beverages, Cameras/video permits, Optional excursions.'),

        ('Southern Coastal Escape', 'Galle & Mirissa', 3, 'Whale Watching, Galle Fort Walk, Stilt Fishing', 220.00, 'available', 
         'Bask in the golden sun of Sri Lanka’s south coast. Spot giant blue whales in Mirissa, explore the UNESCO-listed Galle Dutch Fort, and take stunning photos of traditional stilt fishermen.',
         'Day 1: Drive from Negombo to Galle via Southern Highway. Evening guided walking tour of Galle Fort.\r\nDay 2: Early morning Whale Watching safari in Mirissa. Relax at Unawatuna beach. Sea Turtle hatchery visit.\r\nDay 3: Watch stilt fishermen in Koggala. Return drive to Negombo.',
         '2 Nights beach resort stay with breakfast, Transport in private AC sedan, Whale watching tickets, Entry fees.',
         'Lunch & dinner, Optional water sports, Personal guide tips.'),

        ('Dubai Desert Safari & Modern Wonders', 'Dubai, UAE', 5, 'Desert Safari, Burj Khalifa Visit, Dhow Cruise', 799.00, 'available',
         'Experience the ultimate fusion of futuristic skyscrapers and traditional desert adventures. Visit the iconic Burj Khalifa, enjoy a thrilling dune bashing experience, and cruise along Dubai Marina.',
         'Day 1: Arrival & airport transfer to luxury hotel. Marina Dhow Dinner Cruise.\r\nDay 2: City tour including Jumeirah Mosque, Burj Al Arab (photo stop), and Dubai Mall. Visit Burj Khalifa Observation Deck (124th floor).\r\nDay 3: Morning at leisure. Afternoon Desert Safari with dune bashing, camel riding, and BBQ dinner show.\r\nDay 4: Visit Dubai Miracle Garden and Global Village.\r\nDay 5: Souk shopping (Gold & Spice Souk) and departure transfer.',
         '4 Nights 4-Star Hotel, Airport Transfers, Burj Khalifa Tickets, Desert Safari BBQ Dinner, Professional Guide, Daily Breakfast',
         'International Flights, UAE Visa Fees, Personal Expenses, Lunches and Dinners not specified'),

        ('Tropical Paradise Luxury Getaway', 'Maldives', 4, 'Snorkeling, Water Villa Stay, Sunset Cruise', 1200.00, 'available',
         'Escape to a paradise of white sandy beaches, turquoise lagoons, and luxurious overwater villas. Swim with tropical fish, enjoy a relaxing spa treatment, and witness breathtaking sunsets.',
         'Day 1: Speedboat transfer from Male Airport to private resort. Check-in to Overwater Villa.\r\nDay 2: Morning snorkeling excursion to explore house reefs. Spa wellness session in the afternoon.\r\nDay 3: Guided sunset dolphin cruise with complimentary drinks. Private beach dinner.\r\nDay 4: Leisure morning and speedboat transfer back to Male Airport.',
         '3 Nights Overwater Villa, Roundtrip Speedboat Transfers, Daily Breakfast & Dinner, Snorkeling Gear, Sunset Cruise',
         'International Flights, Optional Excursions, Premium Alcoholic Beverages, Tips'),

        ('Bali Spiritual & Nature Retreat', 'Bali, Indonesia', 6, 'Temple Tour, Rice Terrace Hike, Beach Surfing', 650.00, 'available',
         'Immerse yourself in the cultural and natural beauty of Bali. Discover the serene temples of Ubud, hike through verdant rice terraces, and relax on the pristine beaches of Seminyak.',
         'Day 1: Arrival in Denpasar, transfer to Ubud jungle resort.\r\nDay 2: Ubud tour including Sacred Monkey Forest, Tegallalang Rice Terraces, and Tirta Empul temple.\r\nDay 3: Scenic sunrise hike to Mount Batur. Refresh at Toya Devasya hot springs.\r\nDay 4: Transfer to Seminyak beach hotel. Free afternoon on the beach.\r\nDay 5: Visit Tanah Lot Temple for sunset. Farewell seafood dinner at Jimbaran Bay.\r\nDay 6: Souvenir shopping and departure transfer.',
         '5 Nights Hotel Accommodations, English-speaking local guide, Mount Batur hiking fees, Daily breakfast, Airport transfers',
         'Flights, Travel Insurance, Lunches, Personal expenses'),

        ('Singapore Futuristic City Exploration', 'Singapore', 4, 'Gardens by the Bay, Night Safari, Sentosa Island', 850.00, 'available',
         'Explore the clean, green, and high-tech garden city-state. Walk among giant Supertrees, experience the thrilling Night Safari, and enjoy the sandy beaches of Sentosa.',
         'Day 1: Arrival & transfer to downtown hotel. Evening Gardens by the Bay light show (Supertrees).\r\nDay 2: City tour: Merlion Park, Chinatown, Little India. Evening tour of the world\'s first Night Safari.\r\nDay 3: Full day at Universal Studios Singapore on Sentosa Island.\r\nDay 4: Shopping at Orchard Road, Jewel Changi exploration, and departure.',
         '3 Nights Hotel, Airport Transfers, Gardens by the Bay tickets, Night Safari entry, Universal Studios pass, Breakfast',
         'Airfare, Visa, Lunch & Dinner, Guide tips'),

        ('Classic Japan: Tokyo to Kyoto Heritage', 'Japan', 7, 'Bullet Train, Mt. Fuji Tour, Ancient Temples', 1850.00, 'available',
         'Experience the perfect blend of ultra-modern cityscapes and deep-rooted history. Explore the neon lights of Tokyo, witness the majestic Mt. Fuji, and wander through Kyoto\'s historic temples.',
         'Day 1: Arrival in Tokyo (Narita/Haneda), transfer to hotel. Evening walk in Shinjuku.\r\nDay 2: Guided Tokyo tour: Senso-ji Temple, Shibuya Crossing, Meiji Shrine.\r\nDay 3: Day trip to Mount Fuji & Hakone. Ride the ropeway and cruise Lake Ashi.\r\nDay 4: Shinkansen (Bullet Train) to Kyoto. Check in. Afternoon tour of Fushimi Inari Shrine.\r\nDay 5: Kyoto tour: Kinkaku-ji (Golden Pavilion), Arashiyama Bamboo Grove, Gion district.\r\nDay 6: Bullet train back to Tokyo. Free afternoon for shopping in Akihabara or Ginza.\r\nDay 7: Checkout and airport transfer.',
         '6 Nights Hotel Accommodations, 7-Day Japan Rail Pass, Professional guide, Entry fees, Daily breakfast',
         'International Airfare, Lunches & Dinners, Travel Insurance, Personal expenses'),

        ('Brazil: Rio de Janeiro & Iguazu Falls', 'Brazil', 6, 'Christ the Redeemer, Sugarloaf Cable Car, Waterfall Tour', 1450.00, 'available',
         'Delight in the vibrant energy of Brazil. Gaze at the Christ the Redeemer statue in Rio de Janeiro, sunbathe on Copacabana Beach, and stand in awe before the majestic Iguazu Falls.',
         'Day 1: Arrival in Rio de Janeiro, transfer to hotel near Copacabana.\r\nDay 2: Rio tour: Christ the Redeemer (Corcovado), Sugarloaf Mountain cable car ride.\r\nDay 3: Free day in Rio. Enjoy Copacabana Beach or explore Santa Teresa neighborhood.\r\nDay 4: Flight to Iguazu Falls. Check in. Tour the Brazilian side of the falls.\r\nDay 5: Tour the Argentine side of Iguazu Falls including the Devil\'s Throat.\r\nDay 6: Transfer to airport for departure flight.',
         '5 Nights Hotel, Local flights Rio to Iguazu, English-speaking guides, Entrance tickets, Daily breakfast',
         'International flights, Visas, Personal items, Meals not specified');");
    add_log("Seeded travel packages catalog.");

    // Package Images
    $pdo->exec("INSERT INTO package_images (package_id, image_path, is_featured) VALUES 
        (1, 'assets/images/negombo_lagoon.jpg', 1),
        (2, 'assets/images/sigiriya.jpg', 1),
        (3, 'assets/images/galle.jpg', 1),
        (4, 'assets/images/dubai.png', 1),
        (5, 'assets/images/maldives.png', 1),
        (6, 'assets/images/bali.png', 1),
        (7, 'assets/images/singapore.png', 1),
        (8, 'assets/images/japan.png', 1),
        (9, 'assets/images/brazil.png', 1);");
    add_log("Seeded package images.");

    // Sample Inquiries
    $pdo->exec("INSERT INTO inquiries (user_id, name, email, phone, subject, message, status, response, responded_by) VALUES 
        ($customer_user_id, 'Jane Doe', 'traveler@gmail.com', '+15556667777', 'Whale Watching Season', 'Is the whale watching tour available in June? I would like to book the Southern Coastal Escape.', 'resolved', 'Hello Jane! Whale watching in Mirissa is best from November to April. In June, we can arrange a similar safari in Trincomalee on the East Coast where the sea is calmer. Let us know if you want us to adjust the package!', $staff_user_id),
        (NULL, 'Guest Visitor', 'guest@gmail.com', '+94711122233', 'Custom Package Request', 'Hi, do you provide customized group tours for 15 people from Negombo to Nuwara Eliya? Please let me know.', 'new', NULL, NULL);");
    add_log("Seeded sample inquiries.");

    // Sample Custom Travel Request
    $pdo->exec("INSERT INTO custom_travel_requests (user_id, destination, duration_days, accommodation_type, transport_preference, activities, budget, special_requests, status) VALUES 
        ($customer_user_id, 'Nuwara Eliya & Ella', 5, 'Resort', 'Private Vehicle', 'Tea factory visit, hiking Little Adams Peak, Train ride', 600.00, 'We prefer a scenic train ride from Nanu Oya to Ella in first class if possible.', 'pending');");
    add_log("Seeded sample custom travel requests.");

    // Sample Booking & Payment
    $pdo->exec("INSERT INTO bookings (booking_ref, user_id, package_id, travel_date, num_travelers, special_requests, total_price, status) VALUES 
        ('GT-984210', $customer_user_id, 2, '2026-07-15', 2, 'Requesting double bed and vegetarian meals.', 580.00, 'confirmed');");
    $booking_id = $pdo->lastInsertId();
    add_log("Seeded sample booking.");

    $pdo->exec("INSERT INTO booking_details (booking_id, status, notes, updated_by) VALUES 
        ($booking_id, 'pending', 'Booking submitted by customer.', $customer_user_id),
        ($booking_id, 'confirmed', 'Booking confirmed. Payment verified by system.', $staff_user_id);");
    add_log("Seeded sample booking audit trail.");

    $pdo->exec("INSERT INTO payments (booking_id, amount, card_holder, payment_ref, status) VALUES 
        ($booking_id, 580.00, 'JANE DOE', 'PAY-882049182', 'success');");
    add_log("Seeded sample payment.");

    $pdo->exec("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES 
        ($admin_user_id, 'DATABASE_INIT', 'System database initialized and seeded successfully.', '127.0.0.1');");
    add_log("Seeded initial audit log.");

    add_log("Database installation and seeding finished successfully!", true);

} catch (PDOException $e) {
    add_log("Setup Error: " . $e->getMessage(), false);
}

// Render Results
if (!$is_cli) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobeTrek Adventures - Database Setup</title>
    <!-- Custom style.css system -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .setup-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .log-list {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 8px;
            background: #1e293b;
            color: #f8fafc;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
            padding: 15px;
        }
        .log-success { color: #10b981; }
        .log-error { color: #f43f5e; font-weight: bold; }
        .btn-primary-custom {
            background-color: #0ea5e9;
            border-color: #0ea5e9;
            font-weight: 600;
        }
        .btn-primary-custom:hover {
            background-color: #0284c7;
            border-color: #0284c7;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">GlobeTrek Adventures</h2>
                    <p class="text-muted">Travel & Tourism Management System - Academic Installer</p>
                </div>
                <div class="card setup-card p-4">
                    <h4 class="mb-3 fw-bold">Database Setup Status</h4>
                    
                    <div class="log-list mb-4">
                        <?php foreach ($log as $l): ?>
                            <div class="<?php echo $l['success'] ? 'log-success' : 'log-error'; ?>">
                                <?php echo ($l['success'] ? '✔' : '✘') . ' ' . htmlspecialchars($l['msg']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-center">
                        <a href="../index.php" class="btn btn-primary btn-primary-custom px-4 py-2 rounded-pill">Go to Homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
}
?>
