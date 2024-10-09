<?php
session_start();
include "config/db_connect.php";

// Check if survey_id is provided
if (!isset($_GET['survey_id']) || !is_numeric($_GET['survey_id'])) {
    die("Invalid or missing Survey ID.");
}

$survey_id = (int)$_GET['survey_id'];

// Fetch survey information (including the survey title)
$survey_query = "SELECT title FROM survey_list WHERE survey_id = ?";
$stmt = $conn->prepare($survey_query);
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$survey_result = $stmt->get_result();
$survey_info = $survey_result->fetch_assoc();

if (!$survey_info) {
    die("Survey not found.");
}

// Use the survey title for the file name (sanitize the title for use in a file name)
$survey_title = preg_replace('/[^a-zA-Z0-9_-]/', '_', $survey_info['title']); // Replace spaces and special characters with underscores

// Fetch survey questions
$questions_query = "SELECT id, question_text FROM survey_questions WHERE survey_id = ? ORDER BY id ASC";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$questions_result = $stmt->get_result();

if ($questions_result->num_rows == 0) {
    die("No questions found for the specified survey.");
}

$questions = [];
while ($row = $questions_result->fetch_assoc()) {
    $questions[$row['id']] = $row['question_text'];
}

// Fetch survey responses
$responses_query = "SELECT response_data FROM survey_responses WHERE survey_id = ?";
$stmt = $conn->prepare($responses_query);
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$responses_result = $stmt->get_result();

if ($responses_result->num_rows == 0) {
    die("No responses found for the specified survey.");
}

// Prepare CSV file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $survey_title . '_responses.csv"'); // Set filename to survey title

// Open output stream
$output = fopen('php://output', 'w');

// Write header row with question texts
fputcsv($output, $questions);

// Write each response as a row in the CSV
while ($response_row = $responses_result->fetch_assoc()) {
    $response_data = json_decode($response_row['response_data'], true);
    
    // Initialize an array to hold the row data for the current response
    $response_row_data = [];

    // Populate the row with answers in the order of questions
    foreach ($questions as $question_id => $question_text) {
        // Check if the response contains an answer for the current question
        if (isset($response_data[$question_id])) {
            $response_row_data[] = $response_data[$question_id];
        } else {
            $response_row_data[] = "N/A"; // Mark unanswered questions as "N/A"
        }
    }

    // Write the response data row to the CSV file
    fputcsv($output, $response_row_data);
}

// Close output stream
fclose($output);

// Close database connection
$stmt->close();
$conn->close();
exit;
