<?php
session_start();
include "config/db_connect.php";
include "functions.php"; // Include the functions file

if (isset($_GET['survey_id'])) {
    $survey_id = $_GET['survey_id'];
    $questions = getSurveyQuestions($survey_id); // Call your function to get questions

    // Return questions as JSON
    echo json_encode(['questions' => $questions]);
}
?>