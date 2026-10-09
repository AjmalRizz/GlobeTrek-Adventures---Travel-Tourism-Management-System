<?php
// audit_logs.php - Admin Security Event Log Viewer
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce admin role
require_role(3);

$user_name = $_SESSION['full_name'];
$logs = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT al.*, u.full_name, u.email FROM audit_logs al 
                             LEFT JOIN users u ON al.user_id = u.id 
                             ORDER BY al.created_at DESC LIMIT 50");
        $logs = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Admin Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">System Audit Logs</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="sidebar d-flex flex-column gap-2">
                <div class="text-center pb-3 border-bottom mb-3">
                    <i class="bi bi-shield-lock-fill text-accent" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Admin)</h5>
                    <span class="badge bg-light text-accent border mt-1">Administrator</span>
                </div>
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                <a class="nav-link" href="../staff/bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                <a class="nav-link" href="staff_management.php"><i class="bi bi-people"></i>Staff Accounts</a>
                <a class="nav-link active" href="audit_logs.php"><i class="bi bi-shield-check"></i>Audit Logs</a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h4 class="fw-bold text-dark mb-2">System Audit Trail</h4>
                <p class="text-muted small mb-4">Lists the last 50 system administration events, user authentications, and package updates for security inspections.</p>

                <?php if (!empty($logs)): ?>
                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table align-middle">
                            <thead class="bg-light sticky-top" style="z-index: 5;">
                                <tr>
                                    <th class="py-3 px-3">Timestamp</th>
                                    <th class="py-3">User Ref</th>
                                    <th class="py-3">Action Type</th>
                                    <th class="py-3">Event Details</th>
                                    <th class="py-3 text-end px-3">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="border-bottom small" style="font-size:0.8rem;">
                                        <td class="py-3 px-3 text-muted text-nowrap"><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td class="py-3 text-nowrap">
                                            <?php if ($log['user_id']): ?>
                                                <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($log['full_name']); ?></span>
                                                <span class="text-muted" style="font-size:0.7rem;">ID: <?php echo $log['user_id']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted italic">Guest / System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <?php 
                                            $act = $log['action'];
                                            $badge_class = 'bg-secondary';
                                            if (strpos($act, 'SUCCESS') !== false || strpos($act, 'CREATED') !== false || strpos($act, 'RECEIVED') !== false) {
                                                $badge_class = 'bg-success-subtle text-success border border-success';
                                            } elseif (strpos($act, 'FAILED') !== false || strpos($act, 'DELETED') !== false || strpos($act, 'CANCELLED') !== false) {
                                                $badge_class = 'bg-danger-subtle text-danger border border-danger';
                                            } elseif (strpos($act, 'TOGGLED') !== false || strpos($act, 'UPDATED') !== false || strpos($act, 'STATUS') !== false) {
                                                $badge_class = 'bg-warning-subtle text-warning border border-warning';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?> rounded-pill px-2 py-1" style="font-size:0.65rem;">
                                                <?php echo htmlspecialchars($act); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-muted"><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td class="py-3 text-end text-muted px-3 font-monospace" style="font-size:0.75rem;"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4 small mb-0">No audit trail records found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
