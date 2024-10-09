<?php 
session_start();
include "config/db_connect.php";

if (isset($_GET['survey_id'])) {  //parameters from client_previous_surveys.php
	$survey_id = $_GET['survey_id'];
	$market_price = $_GET['market_price'];
}

$buyer_id = $_SESSION['id'];

$sql = "SELECT is_paid FROM survey_list WHERE survey_id = ?"; //to check if the payment we are making for paying for the survey or buying from marketplace
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $survey_id);  // Bind the correct variable ($survey_id)
$stmt->execute();
$result = $stmt->get_result();  // Use get_result() for prepared statements
$row = $result->fetch_assoc();  // Fetch the result
$stmt->close();

if ($row['is_paid'] == 0) {  //paying for the survey
	$sql = "INSERT INTO `survey_purchase` (`survey_id`, `buyer_id`, `website_commission`) VALUES (?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("iii", $survey_id, $buyer_id, $market_price);
	$stmt->execute();

	$sql = "UPDATE survey_list SEt is_paid = 1 WHERE survey_id = ?"; //updating that the survey is paid
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $survey_id);
	$stmt->execute();
  header("Location: payment.php?survey_id=$survey_id");
  exit(); 
} else {  //paying for buying from marketplace
	$seller_profit = $market_price * 0.7;
	$website_commission = $market_price * 0.3;
	$sql = "INSERT INTO `survey_purchase` (`survey_id`, `buyer_id`, `seller_profit`, `website_commission`) VALUES (?,?,?,?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("iiii", $survey_id, $buyer_id, $seller_profit, $website_commission);
	$stmt->execute();
  header("Location: payment.php?survey_id=$survey_id");
  exit(); 
}
