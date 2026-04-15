<?php
// 1. Session and Role Security
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ROLE GATE: Only admins can add users
if (strtolower($_SESSION['role']) !== 'admin') {
    header("Location: index.php?error=unauthorized"); 
    exit();
}

include 'db_connect.php'; //

$message = "";
$message_type = "";

// 2. Handle Form Submission Logic
if (isset($_POST['create_account'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($role)) {
        $message = "Please fill out all fields.";
        $message_type = "danger";
    } else {
        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $role])) {
                // Redirect to users.php with success status
                header("Location: users.php?status=created");
                exit();
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate username error
                $message = "Username already exists. Please choose another.";
            } else {
                $message = "Database error: " . $e->getMessage();
            }
            $message_type = "danger";
        }
    }
}

include 'includes/header.php'; //
include 'includes/sidebar.php'; //
?>

<div class="main-content">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="users.php" class="text-decoration-none">← Back to Users</a></li>
            </ol>
        </nav>
        <h2 class="fw-bold m-0">Add New System User</h2>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 5px solid <?= ($message_type == 'danger' ? '#dc3545' : '#28a745') ?> !important;">
                        <i class="bi <?= ($message_type == 'danger' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill') ?> me-2"></i>
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="add_user.php" method="POST" autocomplete="off">
                    
                    <div class="mb-3">
                        <label class="fw-bold mb-1 small text-muted">Username</label>
                        <input type="text" name="username" class="form-control bg-light border-0 py-2" 
                               placeholder="Enter username" required autocomplete="off" value="">
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-1 small text-muted">Temporary Password</label>
                        <input type="password" name="password" class="form-control bg-light border-0 py-2" 
                               placeholder="••••••••" required autocomplete="new-password" value="">
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold mb-1 small text-muted">System Role</label>
                        <select name="role" class="form-select bg-light border-0 py-2" required>
                            <option value="" disabled selected>Select a role</option>
                            <option value="Admin">Admin (Full System Access)</option>
                            <option value="Staff">Staff (View & Edit Inventory)</option>
                        </select>
                    </div>

                    <button type="submit" name="create_account" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-2"></i> Create Account
                    </button>
                </form>
            </div>
        </div>
        
        <div class="col-md-5 ms-md-auto">
            <div class="alert alert-info border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold"><i class="bi bi-info-circle-fill me-2 text-primary"></i> User Guidelines</h5>
                <hr class="opacity-10">
                <ul class="small mb-4 mt-2 list-unstyled">
                    <li class="mb-2"><strong><i class="bi bi-shield-lock-fill me-1"></i> Admins:</strong> Full access to inventory, logs, and user management.</li>
                    <li class="mb-0"><strong><i class="bi bi-pencil-square me-1"></i> Staff:</strong> Can manage assets and acquisitions but cannot access system settings.</li>
                </ul>

                <div class="bg-white bg-opacity-50 p-3 rounded-3">
                    <h6 class="fw-bold mb-2 small"><i class="bi bi-shield-check"></i> Password Security</h6>
                    <p class="small text-muted mb-0">
                        Always use a temporary password that includes at least one number and one capital letter.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>