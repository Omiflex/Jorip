<?php
session_start();
include "config/db_connect.php";
$role = $_SESSION['role'];
$id = $_SESSION['id'];

$error = "";
$got_result = false;

$sql = "SELECT t1.survey_id, t1.title, t1.required_entries, t1.collected_entries, t1.created_at, t1.surveyor_earnings, t2.total_entries, t2.total_earnings
FROM (
        SELECT
            survey_id, title, required_entries, collected_entries, created_at, surveyor_earnings
        FROM survey_list
        WHERE
            survey_id IN (
                SELECT survey_id
                FROM survey_responses
                WHERE
                    surveyor_id = ?
                GROUP BY
                    survey_id
            )
            AND is_running = 1
    ) AS t1
    LEFT JOIN (
        SELECT
            survey_id, total_entries, total_earnings
        FROM surveyor_payouts
    ) AS t2 ON t1.survey_id = t2.survey_id;";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if (mysqli_num_rows($result) > 0) {
    $got_result = true;
    $surveys = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Your have not participated in any surveys";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "component/head.php"; ?>
    <style>
        .survey-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background-color: #FBCEB1;
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

        .button-container {
            display: flex;
            justify-content: center;
            /* Centers the button horizontally */
            margin: 10px 0;
            /* Optional: adds some vertical spacing */
        }
    </style>
</head>

<body>
    <?php include "component/header.php"; ?>
    <?php include "component/side-nav-bar.php"; ?>

    <main id="main" class="main">
        <?php if ($got_result): ?>
            <h1 style="text-align: center; margin-top: 20px;">Surveys Entered by You</h1>
            <div class="survey-container">
                <?php foreach ($surveys as $survey): ?>
                    <div class="card" data-aos="zoom-in" data-aos-delay="100">
                        <h5 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h5>
                        <p><strong>Collected/Required (Entries):</strong> <?php echo htmlspecialchars($survey['collected_entries']) . "/" . htmlspecialchars($survey['required_entries']); ?></p>
                        <p><strong>Entries by You:</strong> <?php echo htmlspecialchars($survey['total_entries']); ?></p>
                        <p><strong>Your Earning:</strong> <?php echo htmlspecialchars($survey['total_earnings']) . " Taka"; ?></p>
                        <p><strong>Per Query:</strong> <?php echo htmlspecialchars($survey['surveyor_earnings']) . " Taka"; ?></p>

                        <!-- Centering button inside a container -->
                        <div class="button-container">
                            <a href="survey_response_form.php?survey_id=<?php echo htmlspecialchars($survey['survey_id']); ?>" class="btn-answer">Enter Data</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <h2 style="text-align: center; margin-top: 20px;"><?php echo htmlspecialchars($error); ?></h2>
        <?php endif; ?>
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