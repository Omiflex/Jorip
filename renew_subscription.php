<?php
session_start();
// Database connection
include "config/db_connect.php";

// Check if survey_id is set
if (isset($_POST['survey_id'])) {
    $survey_id = $_POST['survey_id'];
    
    // Fetch the end_date of the existing subscription
    $sql = "SELECT end_date FROM survey_subscriptions WHERE survey_id = ? AND is_active = 1 ORDER BY end_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $survey_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_end_date = new DateTime($row['end_date']);
        
        // Set the new start_date to be the same as last subscription's end_date
        $new_start_date = $last_end_date;
        // Add 30 days for the new end_date
        $new_end_date = clone $new_start_date;
        $new_end_date->modify('+30 days');

        // Insert a new row for the renewed subscription
        $sql_insert = "INSERT INTO survey_subscriptions (survey_id, start_date, end_date, subscription_fee, is_active)
                       VALUES (?, ?, ?, ?, 1)"; // Assuming subscription_fee is fixed; adjust as necessary
        $stmt_insert = $conn->prepare($sql_insert);
        $subscription_fee = 500.00; // Set your subscription fee here
        $stmt_insert->bind_param("issi", $survey_id, $new_start_date->format('Y-m-d H:i:s'), $new_end_date->format('Y-m-d H:i:s'), $subscription_fee);

        if ($stmt_insert->execute()) {
          header("Location: client_previous_surveys.php");
        }
    }
    $stmt->close();
    $stmt_insert->close();
}

$conn->close();
?>
