<?php
// admin/index.php

require_once __DIR__ . '/includes/admin_header.php';

// 1. Calculate Aggregated Operational Data Metrics
$total_vehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$available_vehicles = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE availability_status = 'Available'")->fetchColumn();
$new_leads = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'New'")->fetchColumn();
$pending_test_drives = $pdo->query("SELECT COUNT(*) FROM test_drives WHERE status = 'Pending'")->fetchColumn();

// 2. Fetch Recent Customer Submissions Log Array
$stmtLeads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
$recent_leads = $stmtLeads->fetchAll();
?>

<h3>Core Analytics Summary</h3>
<div class="metrics-grid">
    <div class="metric-card">
        <h4>Total Car Inventory</h4>
        <div class="value"><?php echo $total_vehicles; ?></div>
    </div>
    <div class="metric-card">
        <h4>Active Available Inventory</h4>
        <div class="value" style="color: #16a34a;"><?php echo $available_vehicles; ?></div>
    </div>
    <div class="metric-card">
        <h4>Unresolved Fresh Leads</h4>
        <div class="value" style="color: #dc2626;"><?php echo $new_leads; ?></div>
    </div>
    <div class="metric-card">
        <h4>Pending Test Drives</h4>
        <div class="value" style="color: #ca8a04;"><?php echo $pending_test_drives; ?></div>
    </div>
</div>

<h3>Recent Actionable System Leads Pipeline</h3>
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Contact Classification Type</th>
                <th>Communication Detail</th>
                <th>Submission Date</th>
                <th>Lead Routing State</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_leads)): ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b;">No consumer leads pending triage.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($recent_leads as $lead): ?>
            <tr>
                <td><strong><?php echo sanitize($lead['first_name'] . ' ' . $lead['last_name']); ?></strong></td>
                <td><?php echo sanitize($lead['lead_type']); ?></td>
                <td><?php echo sanitize($lead['email']); ?> <br> <span
                        style="font-size:12px; color:#64748b;"><?php echo sanitize($lead['phone']); ?></span></td>
                <td><?php echo date('M d, Y - g:i A', strtotime($lead['created_at'])); ?></td>
                <td>
                    <span class="badge <?php echo ($lead['status'] === 'New') ? 'badge-new' : 'badge-pending'; ?>">
                        <?php echo sanitize($lead['status']); ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>