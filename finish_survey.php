<?php
session_start();
include "config/db_connect.php";
include "functions.php";

checkIfLoggedIn();

// Check if a survey_id was posted
if (isset($_POST['survey_id'])) {

  $survey_id = $_POST['survey_id'];
  $sql = "UPDATE survey_list
          SET is_running = 0, 
              market_price = collected_entries * cost_per_row * 0.4
          WHERE survey_id = ?;";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $survey_id);

  if ($stmt->execute()) {
    // Redirect back to the dashboard or some confirmation page
    header("Location: index.php");
  }

  mysqli_stmt_close($stmt);
}
mysqli_close($conn);