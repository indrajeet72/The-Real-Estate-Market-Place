<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO tenants (name, email, phone, property_id, applied_date) VALUES (?, ?, ?, ?, CURDATE())");
    $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'], $_POST['prop_id']]);
    header("Location: owner.php");
    exit;
}
?>