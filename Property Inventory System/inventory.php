<?php 
// 1. Session Security & Headers
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// 2. Handling Filters and Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// 3. Build the Combined SQL Query
// We use a base query and append conditions dynamically
$query = "SELECT p.*, c.category_name 
          FROM properties p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE (p.item_name LIKE :search OR p.property_code LIKE :search)";

// If a status filter is active, add it to the WHERE clause
if ($status_filter !== '') {
    $query .= " AND p.status = :status";
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);

// Bind parameters
$params = ['search' => "%$search%"];
if ($status_filter !== '') {
    $params['status'] = $status_filter;
}

$stmt->execute($params);
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0 text-dark">Asset Inventory</h2>
            <p class="text-muted small">Manage and track all registered company properties.</p>
        </div>
        
        <div class="d-flex gap-3 align-items-center" style="max-width: 500px; width: 100%;">
            <form action="inventory.php" method="GET" class="flex-grow-1">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Search code or name..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php if($status_filter): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Search</button>
                </div>
            </form>

            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <a href="add_asset.php" class="btn btn-primary px-4 fw-bold shadow-sm text-nowrap">
                    <i class="bi bi-plus-lg"></i> Add Asset
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($status_filter): ?>
        <div class="alert alert-info border-0 shadow-sm d-flex justify-content-between align-items-center mb-4 py-2">
            <span><i class="bi bi-filter-circle-fill me-2"></i>Showing only <strong><?php echo htmlspecialchars($status_filter); ?></strong> assets</span>
            <a href="inventory.php" class="btn btn-sm btn-link text-decoration-none fw-bold">Clear Filter</a>
        </div>
    <?php endif; ?>

    <div class="inventory-table-card shadow-sm border-0 rounded-3 overflow-hidden bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="text-muted small text-uppercase">
                    <th class="ps-4 py-3">Code</th>
                    <th class="py-3">Item Details</th>
                    <th class="py-3">Category</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="text-end pe-4 py-3">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php if($stmt->rowCount() > 0): ?>
                    <?php while($row = $stmt->fetch()): ?>
                    <tr>
                        <td class="ps-4">
                            <span class="fw-bold text-primary"><?php echo $row['property_code']; ?></span>
                        </td>
                        
                        <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['item_name']); ?></div>
                            <small class="text-muted">Value: ₱<?php echo number_format($row['value'], 2); ?></small>
                        </td>
                        
                        <td>
                            <span class="badge bg-light text-dark border fw-normal">
                                <?php echo $row['category_name'] ?? 'Unassigned'; ?>
                            </span>
                        </td>
                        
                        <td class="text-center">
                            <?php 
                                $status = $row['status'];
                                $badgeClass = 'bg-secondary'; 
                                if($status == 'Available') $badgeClass = 'bg-success text-white';
                                elseif($status == 'In Use') $badgeClass = 'bg-primary text-white';
                                elseif($status == 'Maintenance' || $status == 'In Repair') $badgeClass = 'bg-warning text-dark';
                                elseif($status == 'Disposed') $badgeClass = 'bg-secondary text-white';
                            ?>
                            <span class="badge rounded-pill <?php echo $badgeClass; ?> px-3 py-2" style="min-width: 95px;">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        
                        <td class="text-end pe-4">
                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                <div class="btn-group gap-2">
                                    <a href="edit_asset.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-secondary border-0 rounded-circle p-2" 
                                       title="Edit Asset">
                                         <i class="bi bi-pencil fs-5"></i>
                                    </a>
                                    
                                    <a href="delete_asset.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger border-0 rounded-circle p-2" 
                                       title="Delete Asset"
                                       onclick="return confirm('Are you sure you want to permanently delete this asset?');">
                                         <i class="bi bi-trash fs-5"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-light text-muted fw-normal border">Read-Only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
                            <p class="text-muted mb-0">No assets found matching your criteria.</p>
                            <a href="inventory.php" class="btn btn-link btn-sm mt-2">View all assets</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>