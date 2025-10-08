<?php
// inventory_check.php

// ...existing code...

// Handle form submission for saving or submitting inventory reports
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    // Assuming you have a function to handle report saving and submission
    if ($action === 'save') {
        // Logic to save the report as draft (status = 'saved')
        saveReport($_POST['quantity'], 'saved');
    } elseif ($action === 'submit') {
        // Logic to submit the report (status = 'submitted')
        saveReport($_POST['quantity'], 'submitted');
    }
}

// Fetch saved and submitted reports from the database
$savedReports = getReportsByStatus('saved');
$submittedReports = getReportsByStatus('submitted');

// Assuming you have a function to fetch inventory items
$inventoryItems = getInventoryItems();
?>

<!-- Inventory Check Table -->
<table>
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Unit</th>
            <!-- Removed notes column -->
        </tr>
    </thead>
    <tbody>
        <?php foreach ($inventoryItems as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><input type="number" name="quantity[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>"></td>
            <td><?= htmlspecialchars($item['unit']) ?></td>
            <!-- Removed notes field -->
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Save and Submit Buttons -->
<button type="submit" name="action" value="save">Save</button>
<button type="submit" name="action" value="submit">Submit Inventory Report</button>

<!-- Display Saved Reports -->
<h3>Saved Reports</h3>
<ul>
<?php foreach ($savedReports as $report): ?>
    <li>
        Report #<?= $report['id'] ?> - <?= $report['created_at'] ?>
        <a href="view_report.php?id=<?= $report['id'] ?>">View</a>
        <!-- Optionally add Edit/Delete actions -->
    </li>
<?php endforeach; ?>
</ul>

<!-- Display Submitted Reports -->
<h3>Submitted Reports</h3>
<ul>
<?php foreach ($submittedReports as $report): ?>
    <li>
        Report #<?= $report['id'] ?> - <?= $report['submitted_at'] ?>
        <a href="view_report.php?id=<?= $report['id'] ?>">View</a>
    </li>
<?php endforeach; ?>
</ul>

<?php
// ...existing code...

function saveReport($quantities, $status) {
    // Logic to save the report to the database
    // This function should handle both saving as draft and submitting
}

function getReportsByStatus($status) {
    // Logic to fetch reports from the database by status
}

function getInventoryItems() {
    // Logic to fetch inventory items from the database
}
// ...existing code...