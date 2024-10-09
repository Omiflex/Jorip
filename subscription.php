<?php
session_start();
include "config/db_connect.php";

// Get the survey ID from the GET request
if (isset($_GET['survey_id'])){
  $survey_id = $_GET['survey_id'];
}

// Define the subscription fee and end date (30 days from now)
$end_date = date('Y-m-d H:i:s', strtotime('+30 days')); // Subscription valid for 30 days

// Prepare the insert query
$sql = "INSERT INTO survey_subscriptions (survey_id, end_date, is_active) 
        VALUES (?, ?, 1)"; // is_active set to 1 (active)

// Prepare statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $survey_id, $end_date);
$stmt->execute();

$sql ="UPDATE survey_list SET is_private = 1 WHERE survey_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $survey_id);
$stmt->execute();

$stmt->close();
$conn->close();
header("Location: client_previous_surveys.php");

?>
