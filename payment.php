<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $paymentMethodId = $data['paymentMethodId'];
    $amount = $data['amount']; // In paise
    $propId = $data['prop_id'];

    try {
        // Create Payment Intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'inr',
            'payment_method' => $paymentMethodId,
            'confirmation_method' => 'manual',
            'confirm' => true
        ]);

        if ($paymentIntent->status === 'succeeded') {
            // Record buyer in database
            $stmt = $db->prepare("INSERT INTO buyers (name, email, phone, property_id, request_type, request_date) VALUES (?, ?, ?, ?, 'buy', CURDATE())");
            $stmt->execute(['Guest Buyer', 'guest@propeasy.com', '9999999999', $propId]);
            echo json_encode(['success' => true, 'message' => 'Payment completed!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment failed.']);
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>