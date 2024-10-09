<?php
session_start();
include "config/db_connect.php";
$id = $_SESSION['id'];
$no_survey = 1;
$sql = "SELECT * FROM survey_list WHERE client_id = ? AND is_running = 0 OR survey_id IN (SELECT survey_id FROM survey_purchase WHERE buyer_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $id);
$stmt->execute();
$result = $stmt->get_result();

if (mysqli_num_rows($result) > 0) {
  $no_survey = 0;
}
$surveys = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
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
      justify-content: flex-start;
      padding: 0;
      margin: 0;
      background: transparent;
    }

    .info-card {
      display: flex;
      flex-direction: column;
      height: 400px; /* Fixed height for all cards */
      transition: transform 0.3s, box-shadow 0.3s;
      flex: 0 1 calc(33.33% - 20px);
      max-width: calc(33.33% - 20px);
      background: transparent;
      border: none;
      box-shadow: none;
      outline: none;
      margin: 0;
      padding: 0;
    }

    .info-card:hover {
      transform: translateY(-5px);
    }

    .card {
      display: flex;
      flex-direction: column;
      height: 100%;
      background-color: #FBCEB1;
      border-radius: 10px;
      border: none;
      box-shadow: none;
      overflow: hidden;
      margin: 0;
      padding: 0;
    }

    .card-content {
      flex-grow: 1;
      padding: 15px;
      overflow-y: auto; /* Allow scrolling for overflow content */
    }

    .card-title {
      margin: 0;
      padding: 0;
      font-size: 1.2em;
      font-weight: bold;
    }

    .card-actions {
      padding: 15px;
      background-color: rgba(255, 255, 255, 0.1);
      border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .market-price-input {
      flex: 1;
      padding: 10px;
      background-color: white;
      color: black;
      border-radius: 5px;
      outline: none;
      transition: border-color 0.3s;
    }

    .market-price-input:focus {
      border-color: #FF885B;
      box-shadow: 0 0 5px rgba(255, 136, 91, 0.5);
    }

    .list-btn {
      background-color: #D2691E;
      color: white;
      border: none;
      border-radius: 5px;
      padding: 10px 15px;
      transition: background-color 0.3s;
    }

    .list-btn:hover {
      background-color: #FF7F4D;
    }

    @media (max-width: 768px) {
      .info-card {
        flex: 0 1 calc(50% - 20px);
        max-width: calc(50% - 20px);
      }
    }

    @media (max-width: 480px) {
      .info-card {
        flex: 0 1 100%;
        max-width: 100%;
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
      <?php if (!$no_survey): ?>
        <div class="survey-container">
          <?php foreach ($surveys as $survey): ?>
            <div class="info-card">
              <div class="card">
                <div class="card-content">
                  <h5 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h5>
                  <p><strong>Collected/Required (Entries): </strong> <?php echo htmlspecialchars($survey['collected_entries']) . "/" . htmlspecialchars($survey['required_entries']); ?></p>

                  <?php if ($survey['is_paid'] == 0): ?>
                    <p><strong>Per Query Cost: </strong> <?php echo htmlspecialchars($survey['cost_per_row']) . " Taka"; ?></p>
                    <p><strong>Survey Cost: </strong> <?php echo htmlspecialchars($survey['collected_entries']) * htmlspecialchars($survey['cost_per_row']) . " Taka"; ?></p>
                  <?php else: ?>
                    <?php if (check_if_users_own_survey($survey['survey_id'])): ?>
                      <?php if (check_listing_marketplace($survey['survey_id'])): ?>
                        <p><strong>Listed on Marketplace</strong></p>
                      <?php endif; ?>
                      <?php if (!is_subbed($survey['survey_id'])): ?>
                        <p><strong>Not Subscribed</strong></p>
                      <?php endif; ?>
                      <?php if (is_subscription_expiring_soon($survey['survey_id'])): ?>
                        <p><strong>Subscription Expiring Soon</strong></p>
                      <?php endif; ?>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
                <div class="card-actions">
                  <?php if ($survey['is_paid'] == 0): ?>
                    <a href="purchase_survey.php?survey_id=<?php echo $survey['survey_id']; ?>&market_price=<?php echo $survey['collected_entries'] * $survey['cost_per_row']; ?>" class="btn btn-primary" style="background-color: #FF885B; color:black; width: 100%;">Pay for Survey</a>
                  <?php else: ?>
                    <?php if (check_if_users_own_survey($survey['survey_id'])): ?>
                      <?php if (check_listing_marketplace($survey['survey_id'])): ?>
                        <form action="remove_from_marketplace.php" method="POST" style="margin-bottom: 10px;">
                          <input type="hidden" name="survey_id" value="<?php echo htmlspecialchars($survey['survey_id']); ?>">
                          <button type="submit" class="btn btn-danger btn-sm" style="width: 100%;">Remove from Marketplace</button>
                        </form>
                      <?php endif; ?>
                      <?php if (!is_subbed($survey['survey_id'])): ?>
                        <form action="subscription.php" method="GET" style="margin-bottom: 10px;">
                          <input type="hidden" name="survey_id" value="<?php echo htmlspecialchars($survey['survey_id']); ?>">
                          <button type="submit" class="btn btn-primary" style="width: 100%;">Subscribe</button>
                        </form>
                      <?php endif; ?>
                      <?php if (is_subscription_expiring_soon($survey['survey_id'])): ?>
                        <form action="renew_subscription.php" method="POST" style="margin-bottom: 10px;">
                          <input type="hidden" name="survey_id" value="<?php echo htmlspecialchars($survey['survey_id']); ?>">
                          <button type="submit" class="btn btn-warning" style="width: 100%;">Renew Subscription</button>
                        </form>
                      <?php endif; ?>

                      <form action="listing_on_marketplace.php" method="GET" style="display: flex; align-items: center; gap: 5px; width: 100%; margin-bottom: 10px;">
                        <input type="number" name="market_price" placeholder="Enter Value" class="market-price-input">
                        <input type="hidden" name="survey_id" value="<?php echo htmlspecialchars($survey['survey_id']); ?>">
                        <button type="submit" class="btn list-btn">List</button>
                      </form>
                    <?php endif; ?>
                    <form action="show_survey_data.php" method="GET">
                      <input type="hidden" name="survey_id" value="<?php echo htmlspecialchars($survey['survey_id']); ?>">
                      <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">Show Survey Data</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <h2>No surveys to show!!</h2>
        <a href="marketplace.php" class="btn btn-secondary">View Marketplace Listings</a>
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