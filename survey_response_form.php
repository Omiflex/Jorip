<?php
ob_start();
session_start(); // Start the session

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'component/head.php';
include 'config/db_connect.php';
include 'component/header.php';

// Check if survey_id is set via POST or GET
if (isset($_POST['survey_id'])) {
  $survey_id = $_POST['survey_id'];
} elseif (isset($_GET['survey_id'])) {
  $survey_id = $_GET['survey_id'];
} else {
  die('Error: survey_id is not specified.');
}

// Ensure that the user is logged in
if (!isset($_SESSION['id'])) {
  die('Error: User not logged in.');
}

$id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the responses from the form
  if (isset($_POST['response']) && is_array($_POST['response'])) {
    $responses = $_POST['response'];
  } else {
    die('Error: Invalid form submission.');
  }

  // Convert responses to JSON format
  $responses_json = json_encode($responses);

  // Insert the responses into the database
  $sql = "INSERT INTO survey_responses (surveyor_id, survey_id, response_data) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    die('Prepare Error: ' . htmlspecialchars($conn->error));
  }
  $stmt->bind_param('iis', $id, $survey_id, $responses_json);
  if (!$stmt->execute()) {
    die('Execute Error: ' . htmlspecialchars($stmt->error));
  }
  $stmt->close();

  // Updating survey entries count
  $sql = "UPDATE survey_list SET collected_entries = collected_entries + 1 WHERE survey_id = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    die('Prepare Error: ' . htmlspecialchars($conn->error));
  }
  $stmt->bind_param('i', $survey_id);
  if (!$stmt->execute()) {
    die('Execute Error: ' . htmlspecialchars($stmt->error));
  }
  $stmt->close();

  // Fetch collected_entries, required_entries, and surveyor_earnings for the given survey_id
  $query = "SELECT collected_entries, required_entries, surveyor_earnings FROM survey_list WHERE survey_id = ?";
  $stmt = $conn->prepare($query);
  if ($stmt === false) {
    die('Prepare Error: ' . htmlspecialchars($conn->error));
  }
  $stmt->bind_param("i", $survey_id);
  if (!$stmt->execute()) {
    die('Execute Error: ' . htmlspecialchars($stmt->error));
  }
  $stmt->bind_result($collected_entries, $required_entries, $surveyor_earnings);
  if (!$stmt->fetch()) {
    die('Error fetching survey data.');
  }
  $stmt->close();

  // Creating and updating surveyor_payouts for each survey
  $sql = "SELECT * FROM surveyor_payouts WHERE survey_id = ? AND surveyor_id = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    die('Prepare Error: ' . htmlspecialchars($conn->error));
  }
  $stmt->bind_param("ii", $survey_id, $id);
  if (!$stmt->execute()) {
    die('Execute Error: ' . htmlspecialchars($stmt->error));
  }
  $surveyor_info = $stmt->get_result();

  if ($surveyor_info->num_rows === 0) {
    $sql = "INSERT INTO surveyor_payouts (survey_id, surveyor_id, total_entries, total_earnings) VALUES (?, ?, 1, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      die('Prepare Error: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("iid", $survey_id, $id, $surveyor_earnings); // 'd' for double
    if (!$stmt->execute()) {
      die('Execute Error: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();
  } else {
    $sql = "UPDATE surveyor_payouts SET total_entries = total_entries + 1, total_earnings = total_earnings + ? WHERE survey_id = ? AND surveyor_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
      die('Prepare Error: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("dii", $surveyor_earnings, $survey_id, $id); // 'd' for double
    if (!$stmt->execute()) {
      die('Execute Error: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();
  }

  // Check if collected entries match or exceed required entries
  if ($collected_entries >= $required_entries) {
    // Update the `is_running` status to 0 and calculate market_price
    $updateQuery = "UPDATE survey_list SET is_running = 0, market_price = collected_entries * cost WHERE survey_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    if ($updateStmt === false) {
      die('Prepare Error: ' . htmlspecialchars($conn->error));
    }
    $updateStmt->bind_param("i", $survey_id);
    if (!$updateStmt->execute()) {
      die('Execute Error: ' . htmlspecialchars($updateStmt->error));
    }
    $updateStmt->close();

    header("Location: index.php");
    exit; // Ensure the script stops after the redirect
  } else {
    header("Location: survey_response_form.php?survey_id=" . urlencode($survey_id));
    exit; // Ensure the script stops after the redirect
  }
}

// Fetch the `is_running` status
$sql = "SELECT is_running FROM survey_list WHERE survey_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
  die('Prepare Error: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $survey_id);
if (!$stmt->execute()) {
  die('Execute Error: ' . htmlspecialchars($stmt->error));
}
$stmt->bind_result($is_running);
if (!$stmt->fetch()) {
  die('Error fetching is_running status.');
}
$stmt->close();

if ($is_running == 1) {
  // Fetch survey questions
  $sql = "SELECT survey_list.title, survey_questions.question_text, survey_questions.question_type, survey_questions.id
          FROM survey_list
          INNER JOIN survey_questions ON survey_list.survey_id = survey_questions.survey_id
          WHERE survey_list.survey_id = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    die('Prepare Error: ' . htmlspecialchars($conn->error));
  }
  $stmt->bind_param('i', $survey_id);
  if (!$stmt->execute()) {
    die('Execute Error: ' . htmlspecialchars($stmt->error));
  }
  $result = $stmt->get_result();
  $questions = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  if (count($questions) > 0 && isset($questions[0]['title'])) {
    $survey_title = htmlspecialchars($questions[0]['title']);
  } else {
    $survey_title = "No questions found for this survey.";
  }
} else {
  header("Location: index.php");
  exit;
}
?>
<!-- Continue with your HTML output here -->
<!DOCTYPE html>
<html lang="en">

<head>

  <style>
    /* Card styles */
    .card {
      background-color: #fff5e6;
      /* Light orange background */
      border-radius: 8px;
      box-shadow: none;
      /* Removes default shadow */
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin: 20px;
      padding: 20px;
    }

    .card:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      /* Adds a shadow on hover */
    }

    .card-header {
      background-color: #FBCEB1;
      /* Matches the existing card color */
      padding: 15px;
      border-radius: 8px 8px 0 0;
    }

    .card-header h3 {
      color: #333;
      /* Adjust title color as needed */
      margin: 0;
    }

    .card-body {
      padding: 20px;
    }

    /* Form styles */
    .form-group {
      margin-bottom: 15px;
    }

    .form-label {
      font-weight: bold;
    }

    .form-control {
      padding: 10px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    /* Button styles */
    .btn-primary {
      background-color: #FF885B;
      /* Button background color */
      border: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #ff6a35;
      /* Darker shade on hover */
    }
  </style>
</head>


<!-- Start the form -->

<body>
  <div class="card info-card">
    <div class="card-header">
      <h3 class="card-title">Survey Form</h3>
    </div>
    <div class="card-body">
      <form action="" method="POST" class="php-email-form">
        <input type="hidden" name="survey_id" value="<?= $survey_id ?>">
        <h1><?= htmlspecialchars($survey_title) ?></h1>

        <?php foreach ($questions as $question): ?>
          <div class="form-group mb-3">
            <label class="form-label"><?= htmlspecialchars($question['question_text']) ?></label>
            <?php switch ($question['question_type']) {
              case 'String': ?>
                <input type="text" class="form-control" name="response[<?= $question['id'] ?>]" required />
                <?php break; ?>
              <?php
              case 'Float': ?>
                <input type="number" step="any" class="form-control" name="response[<?= $question['id'] ?>]" required />
                <?php break; ?>
              <?php
              case 'Boolean': ?>
                <div class="form-check">
                  <input type="radio" class="form-check-input" name="response[<?= $question['id'] ?>]" value="1"> Yes
                </div>
                <div class="form-check">
                  <input type="radio" class="form-check-input" name="response[<?= $question['id'] ?>]" value="0"> No
                </div>
                <?php break; ?>
              <?php
              default: ?>
                <input type="text" class="form-control" name="response[<?= $question['id'] ?>]" />
            <?php break;
            } ?>
          </div>
        <?php endforeach; ?>

        <div class="text-center">
          <button type="submit" class="btn">Submit Survey</button>
        </div>
      </form>
    </div>
  </div>
</body>

<?php ob_flush() ?>