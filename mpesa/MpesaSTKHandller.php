<?php

// Get the raw POST data
$postData = file_get_contents("php://input");

// Decode the JSON data
$data = json_decode($postData, true);

// Log the raw POST data for debugging
file_put_contents('mpesa_callback.log', $postData . PHP_EOL, FILE_APPEND);

// Check if the data is valid
if ($data && isset($data['Body']['stkCallback'])) {
    $callbackData = $data['Body']['stkCallback'];

    // Extract necessary details
    $merchantRequestID = $callbackData['MerchantRequestID'];
    $checkoutRequestID = $callbackData['CheckoutRequestID'];
    $resultCode = $callbackData['ResultCode'];
    $resultDesc = $callbackData['ResultDesc'];

    // Handle the resultCode (0 indicates success)
    if ($resultCode == 0) {
        $amount = $callbackData['CallbackMetadata']['Item'][0]['Value'];
        $mpesaReceiptNumber = $callbackData['CallbackMetadata']['Item'][1]['Value'];
        $balance = $callbackData['CallbackMetadata']['Item'][2]['Value'];
        $transactionDate = $callbackData['CallbackMetadata']['Item'][3]['Value'];
        $phoneNumber = $callbackData['CallbackMetadata']['Item'][4]['Value'];

        // Insert data into your database
        $mysqli = new mysqli("localhost", "username", "password", "codecan_grocery");

        if ($mysqli->connect_errno) {
            file_put_contents('mpesa_callback.log', "Failed to connect to MySQL: " . $mysqli->connect_error . PHP_EOL, FILE_APPEND);
            exit();
        }

        $stmt = $mysqli->prepare("INSERT INTO mpesa_transactions (MerchantRequestID, CheckoutRequestID, ResultCode, ResultDesc, Amount, MpesaReceiptNumber, Balance, TransactionDate, PhoneNumber) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $merchantRequestID, $checkoutRequestID, $resultCode, $resultDesc, $amount, $mpesaReceiptNumber, $balance, $transactionDate, $phoneNumber);

        if ($stmt->execute()) {
            file_put_contents('mpesa_callback.log', "Transaction saved successfully." . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents('mpesa_callback.log', "Failed to save transaction: " . $stmt->error . PHP_EOL, FILE_APPEND);
        }

        $stmt->close();
        $mysqli->close();
    } else {
        file_put_contents('mpesa_callback.log', "Transaction failed: " . $resultDesc . PHP_EOL, FILE_APPEND);
    }
} else {
    file_put_contents('mpesa_callback.log', "Invalid data received." . PHP_EOL, FILE_APPEND);
}

?>
