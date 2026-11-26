<?php
session_start();
require_once __DIR__ . '/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. BOOK APPOINTMENT (PATIENT)
if ($action === 'book_appointment') {
    $doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
    $patient_name = isset($_POST['patient_name']) ? trim($_POST['patient_name']) : '';
    $patient_phone = isset($_POST['patient_phone']) ? trim($_POST['patient_phone']) : '';
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
    $time_slot = isset($_POST['time_slot']) ? $_POST['time_slot'] : '';

    if ($doctor_id <= 0 || empty($patient_name) || empty($patient_phone) || empty($appointment_date) || empty($time_slot)) {
        header("Location: index.php?error=empty_fields");
        exit;
    }

    try {
        $stmt = $db->prepare("INSERT INTO appointments (doctor_id, patient_name, patient_phone, appointment_date, time_slot, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$doctor_id, $patient_name, $patient_phone, $appointment_date, $time_slot]);
        header("Location: index.php?success=booked");
        exit;
    } catch (PDOException $e) {
        die("Error booking appointment: " . $e->getMessage());
    }
}

// 2. UPDATE APPOINTMENT STATUS (STAFF AUTH + CSRF POST PROTECTED)
if ($action === 'update_status') {
    if (empty($_SESSION['medcare_staff'])) {
        header("Location: admin.php?error=unauthorized");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: admin.php?error=method_not_allowed");
        exit;
    }

    $postedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (empty($sessionToken) || !hash_equals($sessionToken, $postedToken)) {
        header("Location: admin.php?error=invalid_csrf");
        exit;
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($id <= 0 || !in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
        header("Location: admin.php");
        exit;
    }

    try {
        $stmt = $db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        header("Location: admin.php?success=status_updated");
        exit;
    } catch (PDOException $e) {
        die("Error updating appointment status: " . $e->getMessage());
    }
}

// Redirect on fallthrough
header("Location: index.php");
exit;
?>
