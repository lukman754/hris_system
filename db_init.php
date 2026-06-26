<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS hris_system");
    $pdo->exec("USE hris_system");

    echo "Creating tables...\n";

    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(20) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('hrd', 'employee') DEFAULT 'employee',
            can_attendance TINYINT(1) DEFAULT 1,
            position VARCHAR(100),
            department VARCHAR(100),
            birth_date DATE,
            join_date DATE,
            salary INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(20),
            attendance_date DATE,
            attendance_time TIME,
            attendance_type ENUM('qr', 'photo'),
            location VARCHAR(255),
            photo_path VARCHAR(255),
            approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS leaves (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(20),
            leave_type ENUM('sick', 'annual', 'personal', 'maternity', 'other'),
            leave_start DATE,
            leave_end DATE,
            leave_reason TEXT,
            approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            priority ENUM('normal', 'high', 'important') DEFAULT 'normal',
            author_id VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS performance_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(20),
            reviewer_id VARCHAR(20),
            work_quality FLOAT,
            productivity FLOAT,
            communication FLOAT,
            teamwork FLOAT,
            initiative FLOAT,
            overall FLOAT,
            feedback TEXT,
            review_date DATE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS calendar_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            event_date DATE NOT NULL,
            category ENUM('meeting', 'holiday', 'activity') DEFAULT 'activity',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(20) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )"
    ];

    foreach ($queries as $q) {
        $pdo->exec($q);
    }

    echo "Inserting demo data...\n";

    $demo_users = [
        ['hrd001', 'Admin HRD', 'hrd@company.com', 'admin123', 'hrd', 1, 'HR Manager', 'Human Resources', '1985-03-15', '2020-01-15', 12000000],
        ['emp001', 'Ayu Lestari', 'ayu@company.com', 'emp123', 'employee', 1, 'Software Developer', 'IT', '1992-07-22', '2021-06-01', 8500000],
        ['emp002', 'Budi Santoso', 'budi@company.com', 'emp123', 'employee', 1, 'Marketing Manager', 'Marketing', '1988-11-10', '2019-03-15', 9000000],
        ['emp003', 'Citra Dewi', 'citra@company.com', 'emp123', 'employee', 1, 'Accountant', 'Finance', '1990-12-25', '2020-08-01', 7500000]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO users (id, name, email, password, role, can_attendance, position, department, birth_date, join_date, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($demo_users as $u) {
        $stmt->execute($u);
    }

    $demo_ann = [
        ['Meeting Bulanan', 'Meeting bulanan akan diadakan pada tanggal 20 Januari 2024 di ruang meeting lantai 2.', 'important', 'hrd001'],
        ['Libur Nasional', 'Kantor tutup tanggal 17 Agustus 2024 untuk memperingati Hari Kemerdekaan RI ke-79.', 'normal', 'hrd001']
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO announcements (title, content, priority, author_id) VALUES (?, ?, ?, ?)");
    foreach ($demo_ann as $a) {
        $stmt->execute($a);
    }

    $demo_events = [
        ['Team Building', '2024-02-10', 'activity', 'Acara team building tahunan di Puncak'],
        ['Hari Raya Nyepi', '2024-03-11', 'holiday', 'Libur nasional Hari Raya Nyepi'],
        ['HUT Perusahaan', '2024-04-15', 'activity', 'Ulang tahun perusahaan ke-10']
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO calendar_events (title, event_date, category, description) VALUES (?, ?, ?, ?)");
    foreach ($demo_events as $e) {
        $stmt->execute($e);
    }

    echo "Initial database setup complete!\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
