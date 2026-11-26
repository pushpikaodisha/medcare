<?php
require_once __DIR__ . '/db.php';

// Fetch all doctors
try {
    $stmt = $db->query("SELECT * FROM doctors ORDER BY name ASC");
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Clinic Scheduling & Practitioner Portal</title>
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
                <span class="logo-accent-med"><i class="fa-solid fa-house-chimney-medical me-2"></i>MED</span><span class="logo-light-med">CARE</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="admin.php" class="btn btn-outline-med btn-sm"><i class="fa-solid fa-hospital-user me-1"></i> Doctor Portal</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container my-5">
        
        <!-- Header -->
        <div class="row align-items-center mb-5">
            <div class="col-md-12">
                <h1 class="welcome-text-med">Compassionate Clinical Care</h1>
                <p class="subtitle-text-med">Schedule appointments with leading clinical specialists, check practitioner availability, and secure reservations immediately.</p>
            </div>
        </div>

        <!-- Feedback banners -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success-banner-med p-3 mb-4 d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-check fs-5 me-3"></i>
                <div>
                    <?php 
                        if($_GET['success'] === 'booked') echo "Appointment booked successfully! Our medical team will review your reservation shortly.";
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-20 text-danger p-3 mb-4 d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-exclamation fs-5 me-3"></i>
                <div>
                    <?php 
                        if($_GET['error'] === 'empty_fields') echo "Validation Error: Please fill in all booking fields before submitting.";
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <!-- LEFT PANEL: Appointment Booking Form -->
            <div class="col-lg-5">
                <div class="glass-card-med">
                    <h3 class="card-title-med"><i class="fa-regular fa-calendar-check text-teal-med me-2"></i>Schedule Appointment</h3>
                    <p class="card-desc-med">Complete this booking form to queue your slot on our medical register.</p>

                    <form action="action.php?action=book_appointment" method="POST">
                        <div class="mb-3">
                            <label class="form-label-med small">Select Practitioner</label>
                            <select name="doctor_id" class="form-select-flow w-100" required>
                                <option value="" selected disabled>Choose Doctor...</option>
                                <?php foreach ($doctors as $doc): ?>
                                    <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['name']); ?> (<?php echo htmlspecialchars($doc['specialty']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-med small">Patient Name</label>
                            <input type="text" name="patient_name" class="form-control-poll" placeholder="Enter Full Name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-med small">Contact Phone</label>
                            <input type="tel" name="patient_phone" class="form-control-poll" placeholder="e.g. 555-0199" required>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label-med small">Booking Date</label>
                                <!-- Prevent picking past dates -->
                                <input type="date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" class="form-control-poll" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label-med small">Preferred Time Slot</label>
                                <select name="time_slot" class="form-select-flow w-100" required>
                                    <option value="" selected disabled>Select Time...</option>
                                    <option value="09:00 AM">09:00 AM</option>
                                    <option value="10:30 AM">10:30 AM</option>
                                    <option value="11:00 AM">11:00 AM</option>
                                    <option value="02:00 PM">02:00 PM</option>
                                    <option value="03:30 PM">03:30 PM</option>
                                    <option value="04:00 PM">04:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-med w-100 py-3"><i class="fa-solid fa-clipboard-check me-2"></i>Schedule Appointment</button>
                    </form>
                </div>
            </div>

            <!-- RIGHT PANEL: Doctor Specialty Profiles List -->
            <div class="col-lg-7">
                <h3 class="font-outfit fw-bold text-white mb-4"><i class="fa-solid fa-user-doctor text-teal-med me-2"></i>Practitioner Directory</h3>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($doctors as $doc): ?>
                        <div class="doc-card-item">
                            <div class="row align-items-center">
                                <div class="col-sm-2 text-center text-sm-start mb-3 mb-sm-0">
                                    <div class="fs-1 text-teal-med"><i class="fa-solid fa-user-md"></i></div>
                                </div>
                                <div class="col-sm-10">
                                    <div class="doc-card-name"><?php echo htmlspecialchars($doc['name']); ?></div>
                                    <div class="doc-card-spec"><?php echo htmlspecialchars($doc['specialty']); ?></div>
                                    <div class="doc-card-time"><i class="fa-regular fa-clock me-2"></i>Schedules: <?php echo htmlspecialchars($doc['availability']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-muted-med border-top-med">
        <p class="mb-0 small">MedCare • Responsive Clinic Scheduling Hub</p>
    </footer>

    <!-- Bootstrap & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
