<?php 
session_start();
include 'db_connect.php'; 

/**
 * 1. IMPROVED SECURITY GATE
 */
$is_admin = false;
if (isset($_SESSION['role'])) {
    $current_role = strtolower($_SESSION['role']);
    if (strpos($current_role, 'admin') !== false) {
        $is_admin = true;
    }
}

if (!isset($_SESSION['user_id']) || !$is_admin) { 
    header("Location: index.php?error=unauthorized");
    exit();
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

/**
 * 2. FETCH SYSTEM ACTIVITY
 */
$query = "SELECT h.*, u.username FROM history_log h 
          JOIN users u ON h.user_id = u.id 
          ORDER BY h.created_at DESC";
$stmt = $pdo->query($query);
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">System Activity History</h2>
            <p class="text-muted m-0">Audit trail of all property additions, modifications, and deletions.</p>
        </div>
        <div>
            <button class="btn btn-outline-primary shadow-sm px-4" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Print Logs
            </button>
        </div>
    </div>

    <div class="d-none d-print-block mb-4 text-center">
        <h2 class="fw-bold">ASSETFLOW SYSTEM LOGS</h2>
        <p class="text-muted">Generated on: <?php echo date('M d, Y h:i A'); ?></p>
        <hr>
    </div>

    <div class="inventory-table-card shadow-sm border-0 rounded-3 overflow-hidden">
        <table class="table table-hover align-middle mb-0 bg-white">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4 py-3">Timestamp</th>
                    <th class="py-3">User</th>
                    <th class="py-3">Action</th>
                    <th class="py-3">Property Code</th>
                    <th class="py-3">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php while($log = $stmt->fetch()): ?>
                <tr>
                    <td class="ps-4 text-muted small">
                        <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                    </td>
                    
                    <td>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($log['username']) ?></span>
                    </td>
                    
                    <td>
                        <?php 
                            $badge_class = 'bg-primary'; 
                            if ($log['action_type'] == 'Add') $badge_class = 'bg-success';
                            if ($log['action_type'] == 'Delete' || $log['action_type'] == 'UserDelete') $badge_class = 'bg-danger';
                        ?>
                        <span class="badge rounded-pill <?= $badge_class ?> px-3 py-2">
                            <?= $log['action_type'] ?>
                        </span>
                    </td>
                    
                    <td class="fw-bold text-primary">
                        <code><?= htmlspecialchars($log['property_code']) ?></code>
                    </td>
                    
                    <td class="text-secondary small">
                        <?= htmlspecialchars($log['details']) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    /* Hide the sidebar, the print button, and any other nav elements */
    .sidebar, .btn, .navbar, .nav-item {
        display: none !important;
    }

    /* Force the main content to the left and full width */
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* Ensure backgrounds (like badge colors) show up in print */
    .badge {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Style the table for paper */
    table {
        width: 100% !important;
        border: 1px solid #dee2e6 !important;
    }

    th {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>