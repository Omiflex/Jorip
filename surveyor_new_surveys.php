<?php
session_start();
include "config/db_connect.php";

// Fetch the surveyor ID from session
$id = $_SESSION['id'];

// SQL to retrieve surveys
$sql = "SELECT 
    sl.survey_id, 
    sl.title, 
    sl.surveyor_earnings, 
    sl.created_at, 
    sl.collected_entries, 
    sl.required_entries, 
    COUNT(sr.id) AS question_count 
FROM 
    survey_list AS sl
LEFT JOIN 
    survey_questions AS sr ON sl.survey_id = sr.survey_id
WHERE 
    sl.survey_id NOT IN (
        SELECT survey_id FROM surveyor_payouts WHERE surveyor_id = ?
    )
    AND sl.is_running = 1
GROUP BY 
    sl.survey_id, 
    sl.is_running, 
    sl.title, 
    sl.surveyor_earnings, 
    sl.created_at, 
    sl.collected_entries, 
    sl.required_entries;";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$surveys = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include "component/head.php"; ?>
  <style>
    /* Modal Background */
    .modal {
      display: none;
      /* Hidden by default */
      position: fixed;
      /* Stay in place */
      z-index: 1050;
      /* Sit on top */
      left: 0;
      top: 0;
      width: 100%;
      /* Full width */
      height: 100%;
      /* Full height */
      overflow: auto;
      /* Enable scroll if needed */
      background-color: rgba(0, 0, 0, 0.6);
      /* Black with opacity */
      transition: opacity 0.3s ease;
    }

    /* Modal Content */
    .modal-content {
      background-color: #fff5e6;
      /* Light orange background */
      margin: 10% auto;
      /* 10% from the top and centered */
      padding: 30px;
      border-radius: 12px;
      width: 80%;
      /* Adjust as needed */
      max-width: 700px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
      position: relative;
      animation: fadeIn 0.3s ease-out;
    }

    /* Close Button */
    .close {
      position: absolute;
      top: 15px;
      right: 20px;
      color: #444;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      transition: color 0.3s;
    }

    .close:hover,
    .close:focus {
      color: #000;
      text-decoration: none;
    }

    /* Animation */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Modal Header */
    .modal-content h2 {
      color: var(--heading-color);
      margin-bottom: 20px;
      text-align: center;
    }

    /* Modal Questions */
    #questionsContainer p {
      background-color: #ffe6cc;
      /* Slightly darker light orange */
      padding: 12px;
      margin: 10px 0;
      border-radius: 6px;
      font-size: 16px;
      line-height: 1.5;
    }

    /* Button Styling */
    .btn-answer {
      background-color: whitesmoke;
      color: var(--contrast-color);
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
      margin: 5px;
      width: 100%;
      max-width: 200px;
    }

    .btn-answer:hover {
      background-color: var(--heading-color);
      color: #ffffff;
    }

    /* Survey Container */
    .survey-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      padding: 20px;
    }

    /* Card Styling */
    /* Card Styling */
    .card {
      background-color: #FBCEB1;
      /* Medium orange background */
      border: 1px solid #ddd;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      text-align: center;
      width: 100%;
      max-width: 300px;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .card-title {
      font-size: 1.5rem;
      color: var(--heading-color);
      margin-bottom: 15px;
    }

    .card p {
      font-size: 1rem;
      color: var(--default-color);
      margin: 10px 0;
      padding: 0 10px;
    }


    /* Responsive Adjustments */
    @media (max-width: 576px) {
      .modal-content {
        width: 90%;
        margin: 20% auto;
      }

      .btn-answer {
        max-width: 100%;
      }
    }
  </style>
</head>

<body>
  <?php include "component/header.php"; ?>
  <?php include "component/side-nav-bar.php"; ?>

  <main id="main" class="main">
    <?php if (count($surveys) > 0): ?>
      <h1 style="text-align: center; margin-top: 20px;">NEW SURVEYS!!!</h1>
      <div class="survey-container">
        <?php foreach ($surveys as $survey): ?>
          <div class="card" data-aos="zoom-in" data-aos-delay="100">
            <h5 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h5>
            <p><strong>Collected/Required (Entries):</strong> <?php echo htmlspecialchars($survey['collected_entries']) . "/" . htmlspecialchars($survey['required_entries']) ?></p>
            <p><strong>Per Query:</strong> <?php echo htmlspecialchars($survey['surveyor_earnings']) . " Taka" ?></p>
            <p><strong>Ques no:</strong> <?php echo htmlspecialchars($survey['question_count']) ?></p>
            <div class="d-flex flex-column align-items-center">
              <button class="btn-answer" onclick="openModal(<?php echo htmlspecialchars($survey['survey_id']); ?>)">View Questions</button>
              <a href="survey_response_form.php?survey_id=<?php echo htmlspecialchars($survey['survey_id']); ?>" class="btn-answer">Participate</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <h3 style="text-align: center; margin-top: 20px;">No new Surveys</h3>
    <?php endif; ?>
  </main>

  <?php include "component/footer.php"; ?>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Modal Structure -->
  <div id="questionsModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Survey Questions</h2>
      <div id="questionsContainer"></div>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    function openModal(surveyId) {
      // Fetch questions for the selected survey using AJAX
      fetch(`get_questions.php?survey_id=${surveyId}`)
        .then(response => response.json())
        .then(data => {
          let questionsContainer = document.getElementById("questionsContainer");
          questionsContainer.innerHTML = ''; // Clear previous questions

          if (data.questions && data.questions.length > 0) {
            data.questions.forEach(question => {
              let p = document.createElement("p");
              p.textContent = question.question_text; // Display question text
              questionsContainer.appendChild(p);
            });
          } else {
            questionsContainer.innerHTML = '<p>No questions available for this survey.</p>';
          }

          // Display the modal with fade-in effect
          let modal = document.getElementById("questionsModal");
          modal.style.display = "block";
          setTimeout(() => {
            modal.style.opacity = "1";
          }, 10); // Allow time for the display to apply before opacity transition
        })
        .catch(error => console.error('Error fetching questions:', error));
    }

    function closeModal() {
      let modal = document.getElementById("questionsModal");
      modal.style.opacity = "0";
      setTimeout(() => {
        modal.style.display = "none";
      }, 300); // Match the CSS transition duration
    }

    // Close the modal when clicking outside of it
    window.onclick = function(event) {
      const modal = document.getElementById("questionsModal");
      if (event.target === modal) {
        closeModal();
      }
    }
  </script>
</body>

</html>