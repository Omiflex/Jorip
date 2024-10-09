<?php
session_start();
include "config/db_connect.php";

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
  header('Location: pages-login.php');
  exit();
}

// Fetch user role from session
$user_role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include "component/head.php"; ?>
  <style>
    .info-card {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
      /* Ensure full height */
    }

    .info-card .card-body {
      flex-grow: 1;
      /* Allow the card body to grow */
    }

    .card {
      background-color: #FBCEB1;
      border: 1px solid #ddd;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      transition: transform 0.2s, box-shadow 0.2s;
      display: flex;
      /* Flex for internal layout */
      flex-direction: column;
      /* Keep contents stacked */
      justify-content: space-between;
      /* Distribute space */
      height: 100%;
      /* Set fixed height for uniformity */
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

    .col-xxl-4,
    .col-md-4,
    .col-xxl-6,
    .col-md-6 {
      display: flex;
      flex: 1;
      /* Allow columns to grow equally */
      margin-bottom: 20px;
      /* Add some margin for spacing */
    }

    /* New styles for card layout */
    .row {
      display: flex;
      flex-wrap: wrap;
      /* Allow wrapping */
      justify-content: space-between;
      /* Ensure spacing between cards */
    }

    .btn {
      background-color: #D2691E;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 10px 15px;
      text-align: center;
      transition: background-color 0.3s;
      width: 100%;
      max-width: 200px;
    }

    .btn:hover {
      background-color: #A0522D;
    }

    .card-icon {
      background-color: whitesmoke;
      color: var(--contrast-color);
      font-size: 2rem;
      padding: 15px;
      border-radius: 50%;
      margin-bottom: 15px;
    }

    .card-body {
      display: flex;
      /* Use flexbox for layout */
      flex-direction: column;
      /* Stack children vertically */
      justify-content: space-between;
      /* Space between elements */
      height: 100%;
      /* Make it take full height */
    }

    .text-center {
      margin-top: auto;
      /* Push the button to the bottom */
    }
  </style>


</head>

<body>
  <?php include "component/header.php"; ?>
  <?php include "component/side-nav-bar.php"; ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <?php if ($user_role == 3): // for client 
        ?>
          <div class="col-xxl-4 col-md-4">
            <div class="card info-card">
              <div class="card-body">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-plus-circle"></i>
                </div>
                <h5 class="card-title">Create a Survey</h5>
                <p>Start creating a new survey and collect valuable data.</p>
                <div class="text-center mt-auto">
                  <a href="survey_creation_form.php" class="btn">Create Survey</a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xxl-4 col-md-4">
            <div class="card info-card">
              <div class="card-body">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-bar-chart-line-fill"></i>
                </div>
                <h5 class="card-title">Survey Status</h5>
                <p>Track the progress of your ongoing surveys.</p>
                <div class="text-center mt-auto">
                  <a href="client_survey_status.php" class="btn">View Status</a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xxl-4 col-md-4">
            <div class="card info-card">
              <div class="card-body">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-file-earmark-text"></i>
                </div>
                <h5 class="card-title">My Surveys</h5>
                <p>Track the progress of your ongoing surveys.</p>
                <div class="text-center mt-auto">
                  <a href="client_previous_surveys.php" class="btn">View Surveys</a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xxl-4 col-md-4">
            <div class="card info-card">
              <div class="card-body">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-shop"></i>
                </div>
                <h5 class="card-title">Marketplace</h5>
                <p>Track the progress of your ongoing surveys.</p>
                <div class="text-center mt-auto">
                  <a href="marketplace.php" class="btn">Visit</a>
                </div>
              </div>
            </div>
          </div>


        <?php endif; ?>

        <?php if ($user_role == 2): // for surveyors 
        ?>
          <div class="col-xxl-6 col-md-8">
            <div class="card info-card">
              <div class="card-body">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-bar-chart-line-fill"></i>
                </div>
                <h5 class="card-title">Active Surveys</h5>
                <p>Participate in active surveys and share your insights.</p>
                <div class="text-center mt-auto">
                  <a href="surveyor_active_surveys.php" class="btn">View Surveys</a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xxl-6 col-md-6">
            <div class="card info-card">
              <div class="card-body">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-file-earmark-text"></i>
                </div>
                <h5 class="card-title">New Surveys</h5>
                <p>Explore new surveys and contribute valuable data.</p>
                <div class="text-center mt-auto">
                  <a href="surveyor_new_surveys.php" class="btn">View New Surveys</a>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </section>

  </main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

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

</body>

</html>