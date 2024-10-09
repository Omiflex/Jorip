<?php
session_start();
include "config/db_connect.php";
$role = $_SESSION['role'];
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
        }

        .info-card .card-body {
            flex-grow: 1;
        }

        .card {
            height: 100%;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            background-color: #FBCEB1;
            transition: transform 0.3s, box-shadow 0.3s; /* Transition for transform and box-shadow */
        }

        .card:hover {
            transform: translateY(-5px); /* Move up slightly */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Increase shadow on hover */
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .card-icon {
            font-size: 2rem;
            color: #FBCEB1; /* Adjust color as needed */
        }

        .btn {
            background-color: #fff;
            color: #000; /* Text color */
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #e0a198; /* Darker shade for hover effect */
        }

        /* Additional styles for layout */
        .card-container {
            display: flex;
            justify-content: center; /* Center the cards */
            flex-wrap: wrap; /* Allow wrapping */
            gap: 20px; /* Space between cards */
            margin-top: 20px; /* Space from the top */
        }

        .col-xxl-4,
        .col-md-6 {
            flex: 0 0 auto; /* Allow auto-sizing of columns */
            width: 300px; /* Set a fixed width for each card */
        }

        @media (max-width: 768px) {
            .col-xxl-4,
            .col-md-6 {
                width: 100%; /* Full width on smaller screens */
            }
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
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <?php if ($role == 3): ?>
                    <div class="card-container">
                        <?php 
                        $activeSurveys = getActiveSurveys($_SESSION['id']) ?? []; // Initialize as empty array if null
                        foreach ($activeSurveys as $survey): 
                        ?>
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card">
                                <div class="card-body">
                                    <div class="card-icon d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h5>
                                    <p><?php echo htmlspecialchars($survey['description']); ?></p>
                                    <div class="text-center mt-auto">
                                        <a href="survey_details.php?id=<?php echo $survey['id']; ?>" class="btn">View Survey</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
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
