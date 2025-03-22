<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $prop_id = $data['prop_id'];
    $req_type = $data['req'];
    $date = $data['date'] ?? date('Y-m-d');

    $stmt = $db->prepare("INSERT INTO buyers (name, email, phone, property_id, request_type, request_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Guest User', 'guest@example.com', '9999999999', $prop_id, $req_type, $date]);
    echo json_encode(['success' => true, 'message' => 'Request saved!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Bad request']);
}
?>