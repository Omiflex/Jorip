<?php
include "config/db_connect.php";

function sanitizeInput($data)
{
  return htmlspecialchars(strip_tags(trim($data)));
}

function checkIfLoggedIn()
{
  global $conn;
  if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql = "SELECT id FROM users WHERE id = '$id' limit 1";

    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
      header("Location: pages-login.php");
      exit();
    } else {
      return;
    }
  } elseif (basename($_SERVER['PHP_SELF']) == 'pages-login.php') {
    return;
  } elseif (basename($_SERVER['PHP_SELF']) == 'pages-register.php') {
    return;
  } else {
    header("Location: pages-login.php");
    exit();
  }
}


function loginUser($username, $password)
{
  global $conn;

  // Sanitize user input
  $username = sanitizeInput($username);
  $password = sanitizeInput($password);

  // Fetch the user from the database based on the username
  $sql = "SELECT * FROM users WHERE BINARY username = '$username'";
  $result = mysqli_query($conn, $sql);

  // If a user is found
  if ($result && mysqli_num_rows($result) > 0) {
    $res = mysqli_fetch_assoc($result);
    $stored_hashed_password = $res['password'];

    if (password_verify($password, $stored_hashed_password)) {

      if ($res['role'] == 2) {
        $_SESSION['first_name'] = $res['first_name'];
        $_SESSION['last_name'] = $res['last_name'];
      } else {
        $_SESSION['company_name'] = $res['company_name'];
      }

      // Set common session variables
      $_SESSION['id'] = $res['id'];
      $_SESSION['email'] = $res['email'];
      $_SESSION['fullname'] = $res['first_name'] . " " . $res['last_name'];
      $_SESSION['phone'] = $res['phone'];
      $_SESSION['role'] = $res['role'];

      // Redirect to the index page
      header("Location: index.php");
      exit();
    } else {
      echo "Invalid password.";
    }
  } else {
    echo "User not found.";
  }
}

function signupUser()
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;

    // Ensure the form was submitted with the proper button
    if (isset($_POST['submit'])) {

      // Sanitize input fields
      $role = sanitizeInput($_POST['role']);
      $firstName = isset($_POST['firstName']) ? sanitizeInput($_POST['firstName']) : null;
      $lastName = isset($_POST['lastName']) ? sanitizeInput($_POST['lastName']) : null;
      $companyName = isset($_POST['companyName']) ? sanitizeInput($_POST['companyName']) : null;
      $email = sanitizeInput($_POST['email']);
      $phone = sanitizeInput($_POST['phone']);
      $address = sanitizeInput($_POST['address']);
      $username = sanitizeInput($_POST['username']);
      $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_BCRYPT); // Hash the password

      // Handling image upload
      $profilePicture = $_FILES['profile_picture'];

      // Define the directory where the images will be stored
      $targetDir = "assets/img/";

      // Extract file extension
      $imageFileType = strtolower(pathinfo($profilePicture['name'], PATHINFO_EXTENSION));

      // Generate a unique file name to prevent overwriting existing files
      $imageFileName = uniqid() . '.' . $imageFileType;
      $targetFile = $targetDir . $imageFileName;

      // Check if the file is an actual image
      $check = getimagesize($profilePicture['tmp_name']);
      if ($check === false) {
        echo "File is not an image.";
        return;
      }

      // Check file size (5MB maximum)
      if ($profilePicture['size'] > 5000000) {
        echo "Sorry, your file is too large.";
        return;
      }

      // Allow only certain file formats (jpg, png, jpeg, gif)
      $allowedExtensions = array("jpg", "jpeg", "png", "gif");
      if (!in_array($imageFileType, $allowedExtensions)) {
        echo "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
        return;
      }

      // Move the uploaded file to the target directory
      if (!move_uploaded_file($profilePicture['tmp_name'], $targetFile)) {
        echo "Sorry, there was an error uploading your file.";
        return;
      }

      // Check if the username already exists
      $sql = "SELECT id FROM users WHERE username = '$username'";
      $result = mysqli_query($conn, $sql);

      if ($result->num_rows > 0) {
        // echo "Username already taken. Please choose another.";
        $result->close();
        return;
      }
      $result->close(); // Close the username check statement

      // Prepare the insert query based on role
      if ($role === 'surveyor') {
        $sql = "INSERT INTO users (role, first_name, last_name, email, phone, address, username, password, picture) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $roleValue = 2; // Surveyor role
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssss", $roleValue, $firstName, $lastName, $email, $phone, $address, $username, $password, $imageFileName);
        $stmt->execute();
        header("Location: pages-login.php");
        exit();
      } elseif ($role === 'client') {
        $sql = "INSERT INTO users (role, company_name, email, phone, address, username, password, picture) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $roleValue = 3; // Client role
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssss", $roleValue, $companyName, $email, $phone, $address, $username, $password, $imageFileName);
        $stmt->execute();
        header("Location: pages-login.php");
        exit();
      }
    }
  }
}
function getActiveSurveys($client_id) //for showing active survey in survey status
{
  global $conn;

  $sql = "SELECT * FROM survey_list WHERE client_id = ? AND is_running = 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'i', $client_id);
  mysqli_stmt_execute($stmt);
  $survey_result = mysqli_stmt_get_result($stmt);

  // Iterate over each survey and print it in its own card
  while ($survey = mysqli_fetch_assoc($survey_result)) {
    $survey_id = $survey['survey_id'];
    $title = htmlspecialchars($survey['title'], ENT_QUOTES, 'UTF-8');
    $required_entries = $survey['required_entries'];
    $collected_entries = $survey['collected_entries'];
    $cost_per_row = $survey['cost_per_row'];
    $created_at = $survey['created_at'];

    // Calculate the total bill
    $total_bill = $collected_entries * $cost_per_row;

    // Calculate time since the survey began
    $start_time = new DateTime($created_at);
    $current_time = new DateTime();
    $interval = $start_time->diff($current_time);
    $days_passed = $interval->days; // Get the number of days passed

    // Display the survey information in its own separate card
    echo "
        <div class='card' style='border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;'>
            <div class='card-body info-card'>
                <h3 class='card-title'>$title</h3>
                <p><strong>Required Entries:</strong> $required_entries</p>
                <p><strong>Collected Entries:</strong> $collected_entries</p>
                <p><strong>Cost per row:</strong> $cost_per_row</p>
                <p><strong>Total Bill:</strong> $total_bill Taka</p>
                <p><strong>Days Since Start:</strong> $days_passed days</p>
                
                <!-- Finish Survey Form -->
                <form method='POST' action='finish_survey.php'>
                    <input type='hidden' name='survey_id' value='$survey_id'>
                    <button type='submit' class='btn btn-info'>Finish Survey</button>
                </form>
            </div>
        </div>";
  }

  mysqli_stmt_close($stmt);
}

function sign_out()
{
  if (isset($_SESSION['id'])) {
    session_unset();
    session_destroy();
    header('Location: pages-login.php');
    exit();
  }
}



function getSurveyQuestions($survey_id) {
    global $conn; // Use the global database connection

    $sql = "SELECT question_text FROM survey_questions WHERE survey_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $survey_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row; // Add each question to the array
    }
    
    $stmt->close();
    return $questions; // Return the array of questions
}

function check_if_users_own_survey($survey_id){
  global $conn;
  
  $id = $_SESSION['id'];

  $sql = "SELECT client_id FROM survey_list WHERE survey_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  if($row['client_id'] == $id){
    return 1;
  }else{
    return 0;
  }
}

function check_listing_marketplace($survey_id){
  global $conn;
  $sql = "SELECT is_private FROM survey_list WHERE survey_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  
  if($row['is_private'] == 1){
    return 0;
  }else{
    return 1;
  }
}

function is_subbed($survey_id) {
  global $conn;
  $sql = "SELECT is_active FROM survey_subscriptions WHERE survey_id = ? AND is_active = 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      return 1; 
  } else {
      return 0; 
  }
}

function is_subscription_expiring_soon($survey_id) {
  global $conn;
  $sql = "SELECT end_date FROM survey_subscriptions WHERE survey_id = ? AND is_active = 1 ORDER BY id DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $survey_id);
  $stmt->execute();
  $result = $stmt->get_result();

  // Check if there is an active subscription
  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $end_date = new DateTime($row['end_date']);
      $current_date = new DateTime();

      // Calculate the difference in days
      $interval = $current_date->diff($end_date);
      $days_left = $interval->days;

      // Check if there is less than 1 day left
      return ($days_left < 1 && $end_date > $current_date);
  }

  return false; // No active subscription or not expiring soon
}
