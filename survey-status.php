<?php
session_start();
include "config/db_connect.php";

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
        }

        .info-card .card-body {
            flex-grow: 1;
        }

        .card {
            height: 100%;
        }

        .col-xxl-5,
        .col-md-6 {
            display: flex;
            align-items: stretch;
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

                <?php if ($user_role == 3):
                ?>
                    <?php getActiveSurveys($_SESSION['id']) ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <?php include "component/footer.php"; ?>
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