<?php
session_start();
include "config/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the survey ID
    $survey_id = $_POST['survey_id'];

   $sql = "UPDATE survey_list SET is_private = 1 WHERE survey_id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $survey_id);

    if ($stmt->execute()) {
        header("Location: client_previous_surveys.php"); // Adjust as needed
    }
} else {
    echo "Invalid request.";
}
?>
