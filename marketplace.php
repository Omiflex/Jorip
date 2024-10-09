<?php session_start() ?>
<?php include 'config/db_connect.php' ?>
<?php
$current_user_id = $_SESSION['id'];
$error = "";

// SQL query to fetch completed and public surveys, excluding those owned by the current user
$sql = "SELECT
    survey_list.survey_id,
    survey_list.title,
    survey_list.collected_entries,
    survey_list.market_price,
    users.company_name AS owner,
    COUNT(survey_questions.id) AS question_count
FROM
    survey_list
    JOIN users ON survey_list.client_id = users.id
    JOIN survey_questions ON survey_list.survey_id = survey_questions.survey_id
    JOIN survey_purchase ON survey_list.survey_id = survey_purchase.survey_id
WHERE
    survey_list.is_running = 0
    AND survey_list.is_private = 0
    AND survey_list.client_id != ? -- Exclude client's own surveys
    AND survey_list.survey_id NOT IN (
    SELECT survey_purchase.survey_id
    FROM survey_purchase
    WHERE survey_purchase.buyer_id = ?
)
GROUP BY
    survey_list.survey_id,
    survey_list.title,
    survey_list.collected_entries,
    users.company_name;";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $current_user_id, $current_user_id);  // Bind the current user's ID
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    $surveys = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $error = "No surveys for Sale!!!";
}
?>

<head>
    <?php include "component/head.php"; ?>
    <title>Survey Marketplace</title>
    <style>
        .survey-container {
            display: flex;
            flex-wrap: wrap; /* Allows wrapping of cards to the next line */
            gap: 20px; /* Space between cards */
            justify-content: space-between; /* Evenly space out the cards */
        }

        .card {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 12px; /* Updated border radius */
            background-color: #FBCEB1; /* Light orange background for survey cards */
            width: 48%; /* Make each card take up about 50% of the row, with space between */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            padding: 15px;
            text-align: center; /* Center align text in card body */
        }

        .card-title {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .btn-success {
            background-color: #D2691E; /* Orange button color */
            border-color: #D2691E;
            color: white;
            display: block;
            margin: 0 auto; /* Center align button */
        }

        .error-card {
            background-color: #FFCCB6;
            margin-top: 20px;
            border: none;
            border-radius: 12px;
        }

        .error-card .card-body {
            text-align: center;
        }

        /* Media query to handle small screens */
        @media (max-width: 768px) {
            .card {
                width: 100%; /* On smaller screens, make each card take the full width */
            }
        }
    </style>
</head>

<body>
    <?php include "component/header.php"; ?>
    <?php include "component/side-nav-bar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Survey Marketplace</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Marketplace</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <?php if ($error == ""): ?>
                <div class="survey-container">
                    <?php foreach ($surveys as $survey): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class='card-title'><?php echo htmlspecialchars($survey['title']) ?></h5>
                                <p><strong>Owner:</strong> <?php echo htmlspecialchars($survey['owner']) ?></p>
                                <p><strong>Data Columns:</strong> <?php echo htmlspecialchars($survey['question_count']) ?></p>
                                <p><strong>Entries Collected:</strong> <?php echo htmlspecialchars($survey['collected_entries']) ?></p>
                                <p><strong>Price:</strong> <?php echo htmlspecialchars($survey['market_price']) . " Taka" ?></p>
                                <a href="purchase_survey.php?survey_id=<?php echo $survey['survey_id']; ?>&market_price=<?php echo $survey['market_price']; ?>" class="btn btn-success">Buy Survey Data</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card error-card">
                    <div class="card-body">
                        <h4>No Surveys Available!</h4>
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>
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
