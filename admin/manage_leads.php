<?php
// admin/manage_leads.php
require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/csrf_token.php';

// Process instant status transition changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $lead_id = (int)$_POST['lead_id'];
        $new_status = $_POST['status'];
        $source_table = $_POST['source_table'] ?? 'lead'; // Determine which table to update
        
        // Safely assign table name based on the source
        $table_name = ($source_table === 'test_drive') ? 'test_drives' : 'leads';
        
        $stmt = $pdo->prepare("UPDATE $table_name SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $new_status, 'id' => $lead_id]);
        
        echo "<script>window.location.href='manage_leads.php?msg=status_updated';</script>";
        exit();
    }
}

// Fetch all structural leads AND test drives using a UNION query
// We map td.full_name to first_name, and pass an empty string for last_name
$query = "
    SELECT 
        'lead' AS source_table,
        l.id, 
        l.vehicle_id, 
        l.first_name, 
        l.last_name, 
        l.phone, 
        l.email, 
        l.lead_type, 
        l.message, 
        l.trade_in_details, 
        NULL AS preferred_date, 
        NULL AS preferred_time, 
        l.status, 
        l.created_at,
        v.name AS car_name, 
        v.brand AS car_brand, 
        v.year AS car_year 
    FROM leads l 
    LEFT JOIN vehicles v ON l.vehicle_id = v.id 

    UNION ALL 

    SELECT 
        'test_drive' AS source_table,
        td.id, 
        td.vehicle_id, 
        td.full_name AS first_name, /* Maps the full_name column from the test_drives table */
        '' AS last_name,            /* Blank string to satisfy the UNION structure */
        td.phone, 
        td.email, 
        'Test Drive Booking' AS lead_type, 
        NULL AS message, 
        NULL AS trade_in_details, 
        td.preferred_date, 
        td.preferred_time, 
        td.status, 
        td.created_at,
        v.name AS car_name, 
        v.brand AS car_brand, 
        v.year AS car_year 
    FROM test_drives td 
    LEFT JOIN vehicles v ON td.vehicle_id = v.id 

    ORDER BY created_at DESC
";
$leads = $pdo->query($query)->fetchAll();
?>

<style>
/* Page-Specific Responsive Styles */
.lead-context-cell {
    padding: 14px;
    max-width: 350px;
    font-size: 13px;
    color: #334155;
}

.status-form {
    display: flex;
    gap: 6px;
    align-items: center;
}

/* Mobile Adjustments */
@media (max-width: 768px) {
    .lead-context-cell {
        max-width: 100%;
        /* Allows text to flow better on small screens */
        min-width: 250px;
        /* Ensures the column doesn't get completely crushed */
    }

    .status-form {
        flex-direction: column;
        align-items: stretch;
        /* Stacks the select and button */
    }

    .status-form select,
    .status-form button {
        width: 100%;
        /* Full width for easier tapping */
        padding: 8px !important;
        /* Slightly larger touch targets */
    }
}
</style>

<h3>Customer CRM Lead Pipeline</h3>

<?php if (($vehicles['msg'] ?? $_GET['msg'] ?? '') === 'status_updated'): ?>
<div
    style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size:14px; box-sizing: border-box;">
    Lead pipeline routing state successfully updated.
</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Customer & Contact</th>
                <th>Classification / Intent</th>
                <th>Context Specifics & Message Logs</th>
                <th>Submitted</th>
                <th>Pipeline Status Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($leads)): ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b;">No consumer interactions or inbound leads
                    registered.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($leads as $lead): ?>
            <tr style="vertical-align: top; border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 14px;">
                    <strong><?php echo sanitize(trim($lead['first_name'] . ' ' . $lead['last_name'])); ?></strong><br>
                    <span style="font-size: 13px; color: #475569;"><?php echo sanitize($lead['phone']); ?></span><br>
                    <span style="font-size: 12px; color: #2563eb;"><?php echo sanitize($lead['email']); ?></span>
                </td>
                <td style="padding: 14px;">
                    <span
                        style="font-weight: 600; font-size: 13px; color: #0f172a; <?php echo $lead['source_table'] === 'test_drive' ? 'color: #047857;' : ''; ?>">
                        <?php echo sanitize($lead['lead_type']); ?>
                    </span>
                    <?php if ($lead['vehicle_id']): ?>
                    <br><span style="font-size: 12px; color: #64748b;">Target:
                        <?php echo sanitize($lead['car_year'] . ' ' . $lead['car_brand'] . ' ' . $lead['car_name']); ?></span>
                    <?php endif; ?>
                </td>
                <td class="lead-context-cell">
                    <?php if (!empty($lead['message'])): ?>
                    <strong>Inquiry:</strong> "<?php echo sanitize($lead['message']); ?>"
                    <?php endif; ?>

                    <?php if ($lead['source_table'] === 'test_drive'): ?>
                    <div
                        style="background: #ecfdf5; padding: 8px; border-radius: 4px; border-left: 3px solid #10b981; margin-top: 5px;">
                        <strong>Requested Schedule:</strong><br>
                        Date: <?php echo sanitize(date('M d, Y', strtotime($lead['preferred_date']))); ?><br>
                        Time: <?php echo sanitize(date('g:i A', strtotime($lead['preferred_time']))); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($lead['lead_type'] === 'Trade-In Valuation' && !empty($lead['trade_in_details'])): ?>
                    <div
                        style="background: #f1f5f9; padding: 8px; border-radius: 4px; margin-top: 5px; border-left: 3px solid #64748b;">
                        <strong>Trade Asset Spec:</strong> <?php echo sanitize($lead['trade_in_details']); ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="padding: 14px; font-size: 12px; color: #64748b;">
                    <?php echo date('M d, Y', strtotime($lead['created_at'])); ?><br>
                    <?php echo date('g:i A', strtotime($lead['created_at'])); ?>
                </td>
                <td style="padding: 14px;">
                    <form action="" method="POST" class="status-form">
                        <?php csrf_input_field(); ?>
                        <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                        <input type="hidden" name="source_table" value="<?php echo $lead['source_table']; ?>">

                        <select name="status"
                            style="padding: 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 12px; box-sizing: border-box;">
                            <?php 
                            // Dynamically load the correct ENUM options based on the source table
                            $status_options = ($lead['source_table'] === 'test_drive') 
                                ? ['Pending', 'Confirmed', 'Completed', 'Cancelled'] 
                                : ['New', 'In Progress', 'Contacted', 'Closed'];

                            foreach ($status_options as $st): 
                            ?>
                            <option value="<?php echo $st; ?>" <?php echo $lead['status'] === $st ? 'selected' : ''; ?>>
                                <?php echo $st; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" name="update_status"
                            style="background: #0f172a; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; box-sizing: border-box;">Route</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>