<?php
// Enable error reporting (for debugging - remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Read raw POST body
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Optional: Log the full callback
file_put_contents("callback-log.txt", print_r($data, true), FILE_APPEND);

// Validate required fields
if (!isset($data['orderId']) || !isset($data['merchantReferenceId']) || !isset($data['status'])) {
    http_response_code(400);
    echo "Missing fields in callback.";
    exit;
}

// Extract relevant info
$orderId = $data['orderId'];
$referenceId = $data['merchantReferenceId'];
$status = strtolower($data['status']); // Should be "paid" or similar

// ✅ Connect to MySQL
$conn = new mysqli("localhost", "asti_dwc_admin", "Ast!Dwc@2025", "asti_dwc_admin");
if ($conn->connect_error) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}

// ✅ Update the order status
$stmt = $conn->prepare("UPDATE payments SET status = ? WHERE order_id = ? AND reference_id = ?");
$stmt->bind_param("sss", $status, $orderId, $referenceId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    http_response_code(200);
    echo "✅ Payment status updated.";
} else {
    http_response_code(404);
    echo "❌ Payment record not found.";
}

$stmt->close();
$conn->close();
?>
