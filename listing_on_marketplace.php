<?php
include "config/db_connect.php";

$survey_id = $_GET['survey_id'];
if (isset($_GET['market_price']) && $_GET['market_price'] !== '') {
  $market_price = $_GET['market_price'];
  $sql = "UPDATE survey_list SET market_price = ?, is_private = 0 WHERE survey_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $market_price, $survey_id);
  $stmt->execute();
} else {
  $sql = "UPDATE survey_list SET is_private = 0 WHERE survey_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $survey_id);
  $stmt->execute();
}
$sql = "UPDATE survey_subscriptions 
          SET is_active = 0, end_date = NOW() 
          WHERE id = (SELECT MAX(id) FROM survey_subscriptions WHERE survey_id = ? AND is_active = 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $survey_id);
$stmt->execute();

header("Location: client_previous_surveys.php");
