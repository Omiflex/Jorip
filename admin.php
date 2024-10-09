<?php
// Database connection and PHP functions remain the same as before
// Assuming you have already established a database connection
$db = new mysqli('localhost', 'root', '', 'survey_bd');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Function to get surveyor list
function getSurveyorList($db) {
    $query = "SELECT id, first_name, last_name, email FROM users WHERE role = 2";
    $result = $db->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get completed surveys today
function getCompletedSurveysToday($db) {
    $query = "SELECT COUNT(*) as count FROM survey_responses WHERE DATE(submitted_at) = CURDATE()";
    $result = $db->query($query);
    return $result->fetch_assoc()['count'];
}

// Function to get total revenue
function getTotalRevenue($db) {
    $query = "SELECT SUM(amount) as total FROM survey_purchase";
    $result = $db->query($query);
    return $result->fetch_assoc()['total'];
}

// Function to get company list and bills
function getCompanyListAndBills($db) {
    $query = "SELECT u.id, u.company_name, SUM(sp.amount) as total_bill 
              FROM users u 
              LEFT JOIN survey_purchase sp ON u.id = sp.buyer_id 
              WHERE u.role = 3 
              GROUP BY u.id";
    $result = $db->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$surveyors = getSurveyorList($db);
$completedSurveysToday = getCompletedSurveysToday($db);
$totalRevenue = getTotalRevenue($db);
$companies = getCompanyListAndBills($db);

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        :root {
            --primary-color: #173B45; /* Your dark greenish shade */
            --secondary-color: #FF885B; /* Button and accent color */
            --background-color: #FFE5CF; /* Light background for the page */
            --text-color: #000000; /* Set text color to black */
            --card-background: #FBCEB1; /* Card background color */
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        header h1 {
            color: var(--secondary-color); /* Update the header text color */
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: var(--card-background);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card h2 {
            color: var(--primary-color);
            margin-top: 0;
        }

        .stat {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: var(--secondary-color); /* Table header background color */
            color: black; /* Table header text color */
        }

        td {
            color: var(--text-color); /* Table data text color */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Button Styling */
        .btn {
            background-color: var(--secondary-color);
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: darken(var(--secondary-color), 10%);
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
    </header>

    <div class="container">
        <div class="dashboard-grid">
            <div class="card">
                <h2>Completed Surveys Today</h2>
                <p class="stat"><?php echo $completedSurveysToday; ?></p>
            </div>

            <div class="card">
                <h2>Total Revenue</h2>
                <p class="stat">$<?php echo number_format($totalRevenue, 2); ?></p>
            </div>

            <div class="card">
                <h2>Surveyor List</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                    </tr>
                    <?php foreach ($surveyors as $surveyor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($surveyor['id']); ?></td>
                        <td><?php echo htmlspecialchars($surveyor['first_name'] . ' ' . $surveyor['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($surveyor['email']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="card">
                <h2>Company List and Bills</h2>
                <table>
                    <tr>
                        <th>Company ID</th>
                        <th>Company Name</th>
                        <th>Total Bill</th>
                    </tr>
                    <?php foreach ($companies as $company): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($company['id']); ?></td>
                        <td><?php echo htmlspecialchars($company['company_name']); ?></td>
                        <td>$<?php echo number_format($company['total_bill'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Optional: Add real-time updates or JavaScript functionality here
    </script>
</body>
</html>

