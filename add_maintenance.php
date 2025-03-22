<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO maintenance (tenant_id, property_id, issue, request_date) VALUES (?, ?, ?, CURDATE())");
    $stmt->execute([$_POST['tenant_id'], $_POST['prop_id'], $_POST['issue']]);
    header("Location: owner.php");
    exit;
}
?>