<?php
$db_file = __DIR__ . '/database.db';
$db_created = !file_exists($db_file);

try {
    $db = new PDO("sqlite:" . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialize Schema if newly created
    if ($db_created || filesize($db_file) === 0) {
        // Create Doctors Table
        $db->exec("CREATE TABLE IF NOT EXISTS doctors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            specialty TEXT NOT NULL,
            availability TEXT NOT NULL
        )");

        // Create Appointments Table
        $db->exec("CREATE TABLE IF NOT EXISTS appointments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            doctor_id INTEGER NOT NULL,
            patient_name TEXT NOT NULL,
            patient_phone TEXT NOT NULL,
            appointment_date TEXT NOT NULL,
            time_slot TEXT NOT NULL,
            status TEXT DEFAULT 'pending', -- pending, confirmed, cancelled, completed
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        )");

        // Seed some specialized doctor profiles
        $default_doctors = [
            [
                "name" => "Dr. Charles Xavier",
                "specialty" => "Neurology & Brain Sciences",
                "availability" => "Mon - Wed (09:00 AM - 01:00 PM)"
            ],
            [
                "name" => "Dr. Stephen Strange",
                "specialty" => "Cardiovascular Surgery",
                "availability" => "Tue - Fri (02:00 PM - 06:00 PM)"
            ],
            [
                "name" => "Dr. Gregory House",
                "specialty" => "Diagnostics & Internal Medicine",
                "availability" => "Mon - Thu (10:00 AM - 03:00 PM)"
            ],
            [
                "name" => "Dr. Jean Grey",
                "specialty" => "Psychiatry & Therapy",
                "availability" => "Wed - Fri (09:00 AM - 05:00 PM)"
            ]
        ];

        $stmt = $db->prepare("INSERT INTO doctors (name, specialty, availability) VALUES (?, ?, ?)");
        foreach ($default_doctors as $doc) {
            $stmt->execute([$doc['name'], $doc['specialty'], $doc['availability']]);
        }

        // Seed some initial bookings to make the dashboard indicators active
        $bookStmt = $db->prepare("INSERT INTO appointments (doctor_id, patient_name, patient_phone, appointment_date, time_slot, status) VALUES (?, ?, ?, ?, ?, ?)");
        $bookStmt->execute([1, "Logan Howlett", "555-0199", date('Y-m-d'), "10:30 AM", "confirmed"]);
        $bookStmt->execute([3, "Wade Wilson", "555-0243", date('Y-m-d'), "11:00 AM", "pending"]);
    }
} catch (PDOException $e) {
    die("SQLite Connection failed: " . $e->getMessage());
}
?>
