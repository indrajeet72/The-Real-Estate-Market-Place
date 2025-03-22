<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO leases (tenant_id, property_id, start_date, end_date, rent_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['tenant_id'], $_POST['prop_id'], $_POST['start'], $_POST['end'], $_POST['rent']]);
    header("Location: owner.php");
    exit;
}
?>