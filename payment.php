<?php
session_start(); // Make sure this is the first line in the file

// Add this right after session_start() to check if session is active
if (!isset($_SESSION['id'])) {
    header("Location: lol.php"); // Redirect to login if not logged in
    exit();
}

include "config/db_connect.php";

// Check if survey_id is passed via GET
if (isset($_GET['survey_id'])) {
    $survey_id = $_GET['survey_id'];
}

// Assuming buyer_id is stored in the session
$buyer_id = $_SESSION['id'];

// Query to get survey details
$sql = "SELECT sl.collected_entries,sl.cost_per_row, sl.market_price,sp.seller_profit FROM survey_purchase sp
				join survey_list sl
				ON sl.survey_id = sp.survey_id
				WHERE sp.survey_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc(); // Corrected method call

if ($row['seller_profit'] == 0){  //if zero means the payment is for payment of the survey
	$market_price = $row['collected_entries'] * $row['cost_per_row'];
}
else{  //payment for buuying from marketplace
	$market_price = $row['market_price'];
}


$post_data = array();
$post_data['store_id'] = "jorip66fee8ba69373";
$post_data['store_passwd'] = "jorip66fee8ba69373@ssl";
$post_data['total_amount'] = $market_price;
$post_data['currency'] = "BDT";
$post_data['tran_id'] = "SSLCZ_TEST_" . uniqid();
$post_data['success_url'] = "https://www.surveybd.com/projects/PROJECT/SurveyBD/success.php";
$post_data['fail_url'] = "https://www.surveybd.com/projects/PROJECT/SurveyBD/fail.php";
$post_data['cancel_url'] = "http://www.surveybd.com/projects/PROJECT/SurveyBD/cancel.php";
# $post_data['multi_card_name'] = "mastercard,visacard,amexcard";  # DISABLE TO DISPLAY ALL AVAILABLE

# EMI INFO
$post_data['emi_option'] = "1";
$post_data['emi_max_inst_option'] = "9";
$post_data['emi_selected_inst'] = "9";

# CUSTOMER INFORMATION
$post_data['cus_name'] = $_SESSION["company_name"];
$post_data['cus_email'] = $_SESSION["email"];
$post_data['cus_add1'] = "Dhaka";
$post_data['cus_add2'] = "Dhaka";
$post_data['cus_city'] = "Dhaka";
$post_data['cus_state'] = "Dhaka";
$post_data['cus_postcode'] = "1000";
$post_data['cus_country'] = "Bangladesh";
$post_data['cus_phone'] = $_SESSION["phone"];
$post_data['cus_fax'] = "01711111111";

# SHIPMENT INFORMATION
$post_data['ship_name'] = "testjoripquih";
$post_data['ship_add1'] = "Dhaka";
$post_data['ship_add2'] = "Dhaka";
$post_data['ship_city'] = "Dhaka";
$post_data['ship_state'] = "Dhaka";
$post_data['ship_postcode'] = "1000";
$post_data['ship_country'] = "Bangladesh";

# OPTIONAL PARAMETERS
$post_data['value_a'] = "ref001";
$post_data['value_b'] = "ref002";
$post_data['value_c'] = "ref003";
$post_data['value_d'] = "ref004";

# CART PARAMETERS
$post_data['cart'] = json_encode(array(
	array("product" => "DHK TO BRS AC A1", "amount" => "200.00"),
	array("product" => "DHK TO BRS AC A2", "amount" => "200.00"),
	array("product" => "DHK TO BRS AC A3", "amount" => "200.00"),
	array("product" => "DHK TO BRS AC A4", "amount" => "200.00")
));
$post_data['product_amount'] = "100";
$post_data['vat'] = "5";
$post_data['discount_amount'] = "5";
$post_data['convenience_fee'] = "3";

# REQUEST SEND TO SSLCOMMERZ
$direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $direct_api_url);
curl_setopt($handle, CURLOPT_TIMEOUT, 30);
curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($handle, CURLOPT_POST, 1);
curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC


$content = curl_exec($handle);

$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if ($code == 200 && !(curl_errno($handle))) {
	curl_close($handle);
	$sslcommerzResponse = $content;
} else {
	curl_close($handle);
	echo "FAILED TO CONNECT WITH SSLCOMMERZ API";
	exit;
}

# PARSE THE JSON RESPONSE
$sslcz = json_decode($sslcommerzResponse, true);

if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
	# THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
	# echo "<script>window.location.href = '". $sslcz['GatewayPageURL'] ."';</script>";
	echo "<meta http-equiv='refresh' content='0;url=" . $sslcz['GatewayPageURL'] . "'>";
	# header("Location: ". $sslcz['GatewayPageURL']);
	exit;
} else {
	echo "JSON Data parsing error!";
}
