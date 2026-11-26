<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_GET['logout'])) {
    unset($_SESSION['medcare_staff']);
    header("Location: admin.php");
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_login'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($user === 'admin' && $pass === 'medcare123') {
        $_SESSION['medcare_staff'] = true;
        header("Location: admin.php");
        exit;
    }
    $loginError = 'Invalid staff credentials.';
}

if (empty($_SESSION['medcare_staff'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Staff Portal Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="med-care-theme d-flex align-items-center min-vh-100">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass-card-med">
                    <div class="text-center mb-4">
                        <h2 class="font-outfit text-white"><i class="fa-solid fa-hospital-user text-teal-med me-2"></i>Staff Portal</h2>
                        <p class="text-muted-med small mb-0">Sign in to manage clinical reservations.</p>
                    </div>
                    <?php if ($loginError): ?>
                        <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($loginError); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="staff_login" value="1">
                        <div class="mb-3">
                            <label class="form-label-med small">Username</label>
                            <input type="text" name="username" class="form-control-poll" value="admin" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label-med small">Password</label>
                            <input type="password" name="password" class="form-control-poll" placeholder="Enter staff password" required>
                        </div>
                        <button type="submit" class="btn btn-teal-med w-100 mb-3">Sign In</button>
                    </form>
                    <div class="text-center small text-muted-med">
                        Demo credentials: <code>admin</code> / <code>medcare123</code> &bull; <a href="index.php" class="text-teal-med">Back to Booking</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
exit;
endif;

// Fetch all appointments
try {
    $stmt = $db->query("SELECT appointments.*, doctors.name as doctor_name, doctors.specialty as doctor_specialty FROM appointments JOIN doctors ON appointments.doctor_id = doctors.id ORDER BY appointments.appointment_date DESC, appointments.time_slot ASC");
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

// Compute Metrics
$totalBookings = count($appointments);
$confirmedBookings = 0;
$pendingBookings = 0;

foreach ($appointments as $app) {
    if ($app['status'] === 'confirmed') $confirmedBookings++;
    elseif ($app['status'] === 'pending') $pendingBookings++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Doctor & Staff Booking Portal</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="med-care-theme">

    <!-- Top Sticky Bar -->
    <nav class="navbar navbar-dark pt-3 pb-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fa-solid fa-house-user me-3 text-muted-med fs-5"></i>
                <span class="logo-accent-med"><i class="fa-solid fa-hospital-user me-2"></i>STAFF</span><span class="logo-light-med">PANEL</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php" class="btn btn-outline-med btn-sm"><i class="fa-solid fa-calendar-days me-1"></i> Patient Booking</a>
                <a href="admin.php?logout=1" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container my-5">
        
        <!-- Header -->
        <div class="row align-items-center mb-5">
            <div class="col-md-12">
                <h1 class="welcome-text-med">Practitioner Schedule Manager</h1>
                <p class="subtitle-text-med">Confirm medical reservations, manage appointments status pipelines, and review clinical registers.</p>
            </div>
        </div>

        <!-- Success/Error Banners -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success-banner-med p-3 mb-4 d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-check fs-5 me-3"></i>
                <div>
                    <?php 
                        if($_GET['success'] === 'status_updated') echo "Success! Appointment status has been updated in the register.";
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- METRICS ROW -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="admin-metric-card-med">
                    <span class="metric-subtitle">TOTAL RESERVATIONS</span>
                    <div class="admin-metric-val-med text-teal-med"><?php echo $totalBookings; ?> Bookings</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-metric-card-med">
                    <span class="metric-subtitle">CONFIRMED SESSIONS</span>
                    <div class="admin-metric-val-med text-success"><?php echo $confirmedBookings; ?> Sessions</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-metric-card-med">
                    <span class="metric-subtitle">PENDING APPROVALS</span>
                    <div class="admin-metric-val-med text-warning"><?php echo $pendingBookings; ?> Pending</div>
                </div>
            </div>
        </div>

        <!-- RESERVATIONS TABLE -->
        <div class="row">
            <div class="col-12">
                <h3 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-receipt text-teal-med me-2"></i>Medical Scheduler Logs</h3>
                
<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
                <?php if (empty($appointments)): ?>
                    <div class="glass-card-med text-center py-5">
                        <i class="fa-solid fa-calendar-check fs-2 text-muted-med mb-2"></i>
                        <p class="text-muted-med small">No patient bookings recorded on our medical server.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-glass-med align-middle">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor Specialty</th>
                                    <th>Schedule Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $app): 
                                    $status = $app['status'];
                                ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-white"><?php echo htmlspecialchars($app['patient_name']); ?></div>
                                            <div class="text-muted-med small"><i class="fa-solid fa-phone text-xs me-1"></i> <?php echo htmlspecialchars($app['patient_phone']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-white"><?php echo htmlspecialchars($app['doctor_name']); ?></div>
                                            <div class="text-muted-med small text-teal-med"><?php echo htmlspecialchars($app['doctor_specialty']); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-white"><?php echo date('M d, Y', strtotime($app['appointment_date'])); ?></div>
                                            <div class="text-muted-med small"><i class="fa-regular fa-clock text-xs me-1"></i> <?php echo htmlspecialchars($app['time_slot']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge-status-med status-<?php echo $status; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <?php if ($status === 'pending'): ?>
                                                    <form action="action.php?action=update_status" method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                        <input type="hidden" name="id" value="<?php echo (int)$app['id']; ?>">
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" class="btn-action-med btn-confirm" title="Confirm Slot"><i class="fa-solid fa-check"></i></button>
                                                    </form>
                                                    <form action="action.php?action=update_status" method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                        <input type="hidden" name="id" value="<?php echo (int)$app['id']; ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn-action-med btn-cancel" title="Cancel Booking"><i class="fa-solid fa-xmark"></i></button>
                                                    </form>
                                                <?php elseif ($status === 'confirmed'): ?>
                                                    <form action="action.php?action=update_status" method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                        <input type="hidden" name="id" value="<?php echo (int)$app['id']; ?>">
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn-action-med btn-complete" title="Mark Completed"><i class="fa-solid fa-check-double"></i></button>
                                                    </form>
                                                    <form action="action.php?action=update_status" method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                        <input type="hidden" name="id" value="<?php echo (int)$app['id']; ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn-action-med btn-cancel" title="Cancel Booking"><i class="fa-solid fa-xmark"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted-med small italic">Locked</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-muted-med border-top-med">
        <p class="mb-0 small">MedCare • Secure SQLite Reservation Console</p>
    </footer>

    <!-- Bootstrap & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
