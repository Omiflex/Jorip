<?php
session_start();
include "config/db_connect.php";

// Validate and sanitize the survey_id parameter
$survey_id = 13;

// Fetch survey information
$survey_query = "SELECT * FROM survey_list WHERE survey_id = ?";
$stmt = $conn->prepare($survey_query);
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$survey_result = $stmt->get_result();
$survey_info = $survey_result->fetch_assoc();

if (!$survey_info) {
  die("No survey found with the provided ID.");
}

// Fetch survey questions
$questions_query = "SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY id ASC";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);

// Fetch survey responses
$responses_query = "SELECT * FROM survey_responses WHERE survey_id = ?";
$stmt = $conn->prepare($responses_query);
$stmt->bind_param("i", $survey_id);
$stmt->execute();
$responses_result = $stmt->get_result();
$responses = $responses_result->fetch_all(MYSQLI_ASSOC);

// Calculate basic statistics
$total_responses = count($responses);
$response_rate = ($survey_info['required_entries'] > 0) ? ($survey_info['collected_entries'] / $survey_info['required_entries'] * 100) : 0;

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include "component/head.php"; ?>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
      line-height: 1.5;
      color: #1a1a1a;
      margin: 0;
      padding: 20px;
      background-color: #f9f9f9;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      background-color: #ffffff;
      padding: 20px;
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    }

    h1,
    h2 {
      color: #2c3e50;
    }

    .dataset-metadata {
      background-color: #fff5e6;
      /* Light background color */
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 4px;
      border: 1px solid #FF885B;
      /* Orange border */
    }

    .dataset-metadata h2 {
      color: #D2691E;
      /* Matching color for the title */
    }

    .table-container {
      overflow-x: auto;
      margin-top: 20px;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th,
    td {
      text-align: left;
      padding: 12px;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background-color: #f1f3f5;
      font-weight: 600;
      color: #495057;
    }

    tr:hover {
      background-color: #f8f9fa;
    }

    .scrollable {
      max-height: 400px;
      overflow-y: auto;
    }

    .scrollable::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    .scrollable::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }

    .scrollable::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    /* Added styles for the canvas */
    .canvas-container {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      margin-top: 30px;
    }

    .csv-download-btn button {
      background-color: #007bff;
      /* Blue background color */
      color: white;
      /* White text color */
      border: none;
      /* Remove default border */
      border-radius: 4px;
      /* Rounded corners */
      padding: 10px 15px;
      /* Padding for the button */
      cursor: pointer;
      /* Pointer cursor on hover */
      transition: background-color 0.3s;
      /* Smooth transition for hover effect */
    }

    .csv-download-btn button:hover {
      background-color: #0056b3;
      /* Darker blue on hover */
    }


    canvas {
      max-width: 400px;
      /* Adjust maximum width */
      max-height: 300px;
      /* Adjust maximum height */
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
      <div class="container">
        <?php if ($survey_info): ?>
          <h1 style="color: #D2691E;"><?php echo htmlspecialchars($survey_info['title']); ?> Dataset</h1>

          <div class="dataset-metadata">
            <h2>About this dataset</h2>
            <p><strong>Description:</strong> This dataset contains responses to the "<?php echo htmlspecialchars($survey_info['title']); ?>" survey.</p>
            <p><strong>Number of Responses:</strong> <?php echo $total_responses; ?></p>
            <p><strong>Response Rate:</strong> <?php echo number_format($response_rate, 2); ?>%</p>
            <p><strong>Number of Questions:</strong> <?php echo count($questions); ?></p>
          </div>
          <!-- Download CSV Button -->
          <div class="csv-download-btn">
            <form method="GET" action="download_csv.php">
              <input type="hidden" name="survey_id" value="<?php echo htmlspecialchars($survey_id); ?>">
              <button type="submit">Download CSV</button>
            </form>
          </div>

          <h2 style="color: #D2691E;">Data Preview</h2>
          <?php if ($questions && $responses): ?>
            <div class="table-container">
              <div class="scrollable">
                <table>
                  <thead>
                    <tr>
                      <?php foreach ($questions as $question): ?>
                        <th><?php echo htmlspecialchars($question['question_text']); ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($responses as $response): ?>
                      <?php
                      $response_data = json_decode($response['response_data'], true);
                      ?>
                      <tr>
                        <?php foreach ($questions as $question): ?>
                          <td>
                            <?php
                            $question_id = $question['id'];
                            if (isset($response_data[$question_id])) {
                              echo htmlspecialchars($response_data[$question_id]);
                            } else {
                              echo "N/A";
                            }
                            ?>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php else: ?>
            <p>No questions or responses found for this survey.</p>
          <?php endif; ?>

        <?php else: ?>
          <p>No survey found with the provided ID.</p>
        <?php endif; ?>
      </div>

      <h2 style="color: #D2691E;">Survey Response Overview</h2>

      <div class="canvas-container">
        <canvas id="surveyChart"></canvas>
        <canvas id="bubbleChart"></canvas>
        <canvas id="pieChart" width="300" height="300"></canvas> <!-- Set canvas width and height -->
      </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // Bar Chart Initialization
      const ctx = document.getElementById('surveyChart').getContext('2d');

      // Prepare labels (questions) and data (responses)
      const labels = <?php echo json_encode(array_map(function ($question) {
                        return $question['question_text'];
                      }, $questions)); ?>;

      const responsesData = <?php echo json_encode(array_map(function ($response) use ($questions) {
                              $response_data = json_decode($response['response_data'], true);
                              return array_map(function ($question) use ($response_data) {
                                $question_id = $question['id'];
                                return isset($response_data[$question_id]) ? $response_data[$question_id] : null;
                              }, $questions);
                            }, $responses)); ?>;

      // Generate random colors for each dataset
      const getRandomColor = () => `hsl(${Math.random() * 360}, 100%, 75%)`;

      const datasets = labels.map((label, i) => ({
        label: `Response for ${label}`,
        data: responsesData.map(response => response[i] || 0),
        backgroundColor: getRandomColor(),
        borderColor: 'rgba(0, 0, 0, 0.1)',
        borderWidth: 1
      }));

      // Create the bar chart
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: datasets
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      // Bubble Chart Initialization
      // Bubble Chart Initialization
      const bubbleCtx = document.getElementById('bubbleChart').getContext('2d');
      new Chart(bubbleCtx, {
        type: 'bubble',
        data: {
          datasets: [{
            label: 'Sample Dataset',
            data: [{
              x: 20,
              y: 30,
              r: 15
            }, {
              x: 30,
              y: 20,
              r: 10
            }]
          }]
        },
        options: {
          scales: {
            x: {
              title: {
                display: true,
                text: 'X Axis Title'
              }
            },
            y: {
              title: {
                display: true,
                text: 'Y Axis Title'
              }
            }
          }
        }
      });



      // Pie Chart Initialization
      const pieCtx = document.getElementById('pieChart').getContext('2d');

      // Prepare example data for the pie chart
      const pieData = {
        labels: ['Option 1', 'Option 2', 'Option 3'], // Replace with dynamic labels if needed
        datasets: [{
          data: [300, 50, 100], // Replace with dynamic data if needed
          backgroundColor: [
            getRandomColor(), // Generates random color for each section
            getRandomColor(),
            getRandomColor()
          ],
          borderColor: 'rgba(255, 255, 255, 0.3)',
          borderWidth: 2
        }]
      };

      // Create the pie chart
      new Chart(pieCtx, {
        type: 'pie',
        data: pieData,
        options: {
          responsive: true
        }
      });
    </script>
  </main>
  <?php include "component/footer.php"; ?>
</body>

</html>