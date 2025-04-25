<?php
// Initialize the session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../auth/login.php");
    exit;
}

// Include database connection
require_once "../config/database.php";

// Define the base SQL query
$sql = "SELECT * FROM items WHERE user_id = ?";
$filters = [];
$param_types = "i";
$params = [$_SESSION["id"]];

// Add conditions based on filters
if (!empty($_GET['date'])) {
    $sql .= " AND DATE(created_at) = ?";
    $filters[] = $_GET['date'];
    $param_types .= "s";
} elseif (!empty($_GET['month'])) {
    $sql .= " AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $filters[] = date('m', strtotime($_GET['month']));
    $filters[] = date('Y', strtotime($_GET['month']));
    $param_types .= "ii";
} elseif (!empty($_GET['week'])) {
    $sql .= " AND WEEK(created_at, 1) = WEEK(?, 1) AND YEAR(created_at) = ?";
    $filters[] = $_GET['week'] . '-1'; // Convert week to a date
    $filters[] = date('Y', strtotime($_GET['week'] . '-1'));
    $param_types .= "si";
}

$sql .= " ORDER BY created_at DESC";

// Prepare the SQL statement
if ($stmt = mysqli_prepare($conn, $sql)) {
    // Bind parameters dynamically
    mysqli_stmt_bind_param($stmt, $param_types, ...array_merge($params, $filters));

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
    } else {
        echo '<div class="alert alert-danger">ERROR: Could not execute query: ' . mysqli_error($conn) . '</div>';
    }
}

// Prepare data for the chart
$chartData = [];
$chartLabels = [];
$chartTotals = [];

// Execute the query for the chart
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, $param_types, ...array_merge($params, $filters));
    if (mysqli_stmt_execute($stmt)) {
        $chartResult = mysqli_stmt_get_result($stmt);

        // Group sales by product type for the chart
        while ($row = mysqli_fetch_array($chartResult)) {
            $productType = $row['Product_Type'];
            $totalPrice = $row['price'] * $row['Quantity_Sold'];

            if (!isset($chartData[$productType])) {
                $chartData[$productType] = 0;
            }
            $chartData[$productType] += $totalPrice;
        }

        // Prepare labels and totals for the chart
        foreach ($chartData as $type => $total) {
            $chartLabels[] = $type;
            $chartTotals[] = $total;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard -- D'Plato</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .flex-grow-1 {
            flex: 1;
        }

        footer {
            margin-top: auto;
            padding: 10px 0;
            background-color: #343a40;
            color: white;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php
    // Include header
    require_once "../includes/header.php";
    ?>

    <div class="flex-grow-1">
        <div class="container mt-4">
            <!-- Filter Form -->
            <form method="GET" action="read.php" class="mb-4">
                <div class="row align-items-end">
                    <!-- Date Filter -->
                    <div class="col-md-4">
                        <label for="date" class="form-label">Filter by Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
                    </div>
                    <!-- Month Filter -->
                    <div class="col-md-4">
                        <label for="month" class="form-label">Filter by Month</label>
                        <input type="month" name="month" id="month" class="form-control" value="<?php echo isset($_GET['month']) ? $_GET['month'] : ''; ?>">
                    </div>
                    <!-- Week Filter -->
                    <div class="col-md-4">
                        <label for="week" class="form-label">Filter by Week</label>
                        <input type="week" name="week" id="week" class="form-control" value="<?php echo isset($_GET['week']) ? $_GET['week'] : ''; ?>">
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="read.php" class="btn btn-secondary btn-sm">Reset</a>
                    </div>
                    <a href="create.php" class="btn btn-success btn-sm">Add New Item</a>
                </div>
            </form>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Item List</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            if (isset($result) && mysqli_num_rows($result) > 0) {
                                echo '<div class="table-responsive">';
                                echo '<table class="table table-bordered table-striped">';
                                echo '<thead class="bg-light">';
                                echo '<tr>';
                                echo '<th>ID</th>';
                                echo '<th>Product Type</th>';
                                echo '<th>Product Name</th>';
                                echo '<th>Quantity Sold</th>';
                                echo '<th>Price</th>';
                                echo '<th>Total Price</th>';
                                echo '<th>Created</th>';
                                echo '<th>Action</th>';
                                echo '</tr>';
                                echo '</thead>';
                                echo '<tbody>';

                                $grand_total = 0;

                                while ($row = mysqli_fetch_array($result)) {
                                    echo '<tr>';
                                    echo '<td>' . $row['id'] . '</td>';
                                    echo '<td>' . htmlspecialchars($row['Product_Type']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['Product_Name']) . '</td>';
                                    echo '<td>' . number_format($row['Quantity_Sold']) . '</td>';
                                    echo '<td>' . number_format($row['price'], 2) . '</td>';

                                    $total_price = $row['price'] * $row['Quantity_Sold'];
                                    $grand_total += $total_price;

                                    echo '<td>' . number_format($total_price, 2) . '</td>';
                                    echo '<td>' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
                                    echo '<td>';
                                    echo '<a href="update.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm me-2">Edit</a>';
                                    echo '<a href="read.php?delete=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this item?\')">Delete</a>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                echo '<tr>';
                                echo '<td colspan="5" class="text-end"><strong>Total</strong></td>';
                                echo '<td><strong>' . number_format($grand_total, 2) . '</strong></td>';
                                echo '<td colspan="2"></td>';
                                echo '</tr>';
                                echo '</tbody>';
                                echo '</table>';
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-info">No items found. <a href="create.php" class="alert-link">Add an item</a>.</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-4">
    <div class="col-md-10">
        <canvas id="salesChart" width="400" height="200"></canvas>
    </div>
</div>
<br>
<br>
<br>

    <?php
    // Include header
    require_once "../includes/footer.php";
    ?>
    <script>
    // Get the chart data from PHP
    const chartLabels = <?php echo json_encode($chartLabels); ?>;
    const chartTotals = <?php echo json_encode($chartTotals); ?>;

    // Render the chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar', // Bar chart
        data: {
            labels: chartLabels, // Product types
            datasets: [{
                label: 'Total Sales (in currency)',
                data: chartTotals, // Total sales for each product type
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 99, 132, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Sales Summary by Product Type'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>