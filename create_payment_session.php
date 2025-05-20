<?php
header("Content-Type: application/json");

// Read incoming JSON POST
$data = json_decode(file_get_contents("php://input"), true);

// Extract & sanitize
$courseId = $data['course_id'] ?? 'COURSE001';
$courseName = trim($data['course_name'] ?? 'Online Course');
$rawAmount = $data['price'] ?? '0';
$currency = $data['currency'] ?? 'USD';

// Ensure valid float and minimum of 1.00
$price = number_format((float) $rawAmount, 2, '.', '');
if ($price < 1.00) {
    echo json_encode([
        "success" => false,
        "message" => "Amount must be at least 1.00 USD",
        "debug_amount" => $price
    ]);
    exit;
}

// Geidea LIVE credentials
$merchantPublicKey = "2258188d-9b2e-4bbf-817a-bd74a85e0c9c";
$apiPassword = "e8374eaa-a151-47a6-b967-99dc482ecaaf";

// Endpoint
$url = "https://api.geidea.ae/payment-intent/api/v2/direct/session";
$callbackUrl = "https://www.astidubai.ac.ae/payment-callback.php";
$returnUrl = "https://www.astidubai.ac.ae/";

// Timestamp and Reference
$timestamp = gmdate("Y-m-d\TH:i:s\Z");
$merchantReferenceId = strtoupper(bin2hex(random_bytes(6)));

// Signature
$signatureBase = $merchantPublicKey . $price . $currency . $merchantReferenceId . $timestamp;
$signature = base64_encode(hash_hmac('sha256', $signatureBase, $apiPassword, true));

// Payload
$requestData = [
    "amount" => $price,
    "currency" => $currency,
    "timestamp" => $timestamp,
    "merchantReferenceId" => $merchantReferenceId,
    "signature" => $signature,
    "paymentOperation" => "Pay",
    "callbackUrl" => $callbackUrl,
    "returnUrl" => $returnUrl,
    "language" => "en",
    "createCustomer" => true,
    "order" => [
        "items" => [[
            "merchantItemId" => $courseId,
            "name" => $courseName,
            "description" => "$courseName Enrollment",
            "categories" => "Course",
            "count" => 1,
            "price" => (float) $price,
            "sku" => "SKU-$courseId"
        ]]
    ],
    "appearance" => [
        "showEmail" => true,
        "showAddress" => false,
        "showPhone" => true,
        "receiptPage" => true
    ]
];

// cURL to Geidea
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Basic " . base64_encode("$merchantPublicKey:$apiPassword")
]);

$response = curl_exec($ch);
curl_close($ch);
$responseData = json_decode($response, true);

// Log response
file_put_contents("geidea-log.txt", print_r([
    "request" => $requestData,
    "response" => $responseData
], true));

// ✅ Return response to frontend
if (isset($responseData["session"]["id"]) && $responseData["responseCode"] === "000") {
    echo json_encode([
        "success" => true,
        "sessionId" => $responseData["session"]["id"]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $responseData["detailedResponseMessage"] ?? "Payment failed",
        "response" => $responseData
    ]);
}
?>