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

// Sanitize GET parameters
$page = trim($_GET['page'] ?? '');
$date = trim($_GET['date'] ?? '');
$month = trim($_GET['month'] ?? '');
$week = trim($_GET['week'] ?? '');

// Define the base SQL query
$sql = "SELECT * FROM items WHERE user_id = ?";
$filters = [];
$param_types = "i";
$params = [$_SESSION["id"]];

// Add conditions based on filters
if (!empty($date)) {
    $sql .= " AND DATE(created_at) = ?";
    $filters[] = $date;
    $param_types .= "s";
} elseif (!empty($month)) {
    $sql .= " AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $filters[] = date('m', strtotime($month));
    $filters[] = date('Y', strtotime($month));
    $param_types .= "ii";
} elseif (!empty($week)) {
    $sql .= " AND WEEK(created_at, 1) = WEEK(?, 1) AND YEAR(created_at) = ?";
    $filters[] = $week . '-1'; // Convert week to a date
    $filters[] = date('Y', strtotime($week . '-1'));
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

// Prepare data for the chart grouped by product type
$chartData = [];
$chartLabels = [];
$chartTotals = [];

$highestSalesDay = null;
$lowestSalesDay = null;
$highestSalesAmount = 0;
$lowestSalesAmount = PHP_INT_MAX; // or a high number like 999999999 if preferred

$chartSql = "SELECT Product_Type, SUM(price * Quantity_Sold) AS total_sales 
             FROM items 
             WHERE user_id = ? 
             GROUP BY Product_Type";

if ($chartStmt = mysqli_prepare($conn, $chartSql)) {
    mysqli_stmt_bind_param($chartStmt, "i", $_SESSION["id"]);
    if (mysqli_stmt_execute($chartStmt)) {
        $chartResult = mysqli_stmt_get_result($chartStmt);

        // Group sales by product type for the chart
        while ($row = mysqli_fetch_array($chartResult)) {
            $chartLabels[] = $row['Product_Type'];
            $chartTotals[] = $row['total_sales'];
        }
    }
}

// Prepare data for sales per day of the current month
$dailySalesLabels = [];
$dailySalesTotals = [];

if (!empty($_GET['chart_month'])) {
    $currentYear = date('Y', strtotime($_GET['chart_month']));
    $currentMonth = date('m', strtotime($_GET['chart_month']));
} else {
    $currentYear = date('Y');
    $currentMonth = date('m');
}


$dailySalesSql = "SELECT DATE(created_at) AS sale_date, SUM(price * Quantity_Sold) AS total_sales 
                  FROM items 
                  WHERE user_id = ? AND YEAR(created_at) = ? AND MONTH(created_at) = ? 
                  GROUP BY sale_date 
                  ORDER BY sale_date ASC";

if ($dailySalesStmt = mysqli_prepare($conn, $dailySalesSql)) {
    mysqli_stmt_bind_param($dailySalesStmt, "iii", $_SESSION["id"], $currentYear, $currentMonth);
    if (mysqli_stmt_execute($dailySalesStmt)) {
        $dailySalesResult = mysqli_stmt_get_result($dailySalesStmt);
        while ($row = mysqli_fetch_array($dailySalesResult)) {
            $dailySalesLabels[] = $row['sale_date'];
            $dailySalesTotals[] = $row['total_sales'];
        }
    }
}

// Query to calculate the highest and lowest sales days
$salesInsightsSql = "SELECT DATE(created_at) AS sale_date, SUM(price * Quantity_Sold) AS total_sales 
                      FROM items 
                      WHERE user_id = ? 
                      GROUP BY sale_date 
                      ORDER BY total_sales DESC";

if ($insightsStmt = mysqli_prepare($conn, $salesInsightsSql)) {
    mysqli_stmt_bind_param($insightsStmt, "i", $_SESSION["id"]);
    if (mysqli_stmt_execute($insightsStmt)) {
        $insightsResult = mysqli_stmt_get_result($insightsStmt);

        // Process the results to find the highest and lowest sales days
        while ($row = mysqli_fetch_array($insightsResult)) {
            $saleDate = $row['sale_date'];
            $totalSales = $row['total_sales'];

            // Set the highest sales day (first row in descending order)
            if ($highestSalesDay === null) {
                $highestSalesDay = $saleDate;
                $highestSalesAmount = $totalSales;
            }

            // Always update the lowest sales day
            if ($totalSales < $lowestSalesAmount) {
                $lowestSalesDay = $saleDate;
                $lowestSalesAmount = $totalSales;
            }
        }
    }
}

// Handle cases where no sales data is available
if ($highestSalesDay === null) {
    $highestSalesDay = 'N/A';
    $highestSalesAmount = 0;
}
if ($lowestSalesDay === null) {
    $lowestSalesDay = 'N/A';
    $lowestSalesAmount = 0;
}

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Modify the SQL query to include LIMIT and OFFSET
$sql .= " LIMIT ? OFFSET ?";
$param_types .= "ii";
$filters[] = $limit;
$filters[] = $offset;

// Execute the query
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, $param_types, ...array_merge($params, $filters));
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
    } else {
        echo '<div class="alert alert-danger">ERROR: Could not execute query: ' . mysqli_error($conn) . '</div>';
    }
}

// Get the total number of records for pagination after applying filters
$total_sql = "SELECT COUNT(*) AS total FROM items WHERE user_id = ?";

// Add conditions based on filters
if (!empty($date)) {
    $total_sql .= " AND DATE(created_at) = ?";
} elseif (!empty($month)) {
    $total_sql .= " AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
} elseif (!empty($week)) {
    $total_sql .= " AND WEEK(created_at, 1) = WEEK(?, 1) AND YEAR(created_at) = ?";
}

$total_stmt = mysqli_prepare($conn, $total_sql);

// Bind parameters dynamically based on filters
if (!empty($date)) {
    mysqli_stmt_bind_param($total_stmt, "is", $_SESSION["id"], $date);
} elseif (!empty($month)) {
    $month_param = date('m', strtotime($month));
    $year_param = date('Y', strtotime($month));
    mysqli_stmt_bind_param($total_stmt, "iii", $_SESSION["id"], $month_param, $year_param);
} elseif (!empty($week)) {
    $week_param = $week . '-1'; // Convert week to a date
    $year_param = date('Y', strtotime($week . '-1'));
    mysqli_stmt_bind_param($total_stmt, "isi", $_SESSION["id"], $week_param, $year_param);
} else {
    mysqli_stmt_bind_param($total_stmt, "i", $_SESSION["id"]);
}

mysqli_stmt_execute($total_stmt);
$total_result = mysqli_stmt_get_result($total_stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Calculate total sales and percentages
$total_sales = array_sum($chartTotals);

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']); // Sanitize the ID to prevent SQL injection

    // Prepare the delete query
    $delete_sql = "DELETE FROM items WHERE id = ? AND user_id = ?";
    if ($delete_stmt = mysqli_prepare($conn, $delete_sql)) {
        mysqli_stmt_bind_param($delete_stmt, "ii", $delete_id, $_SESSION["id"]);

        // Execute the query
        if (mysqli_stmt_execute($delete_stmt)) {
            // Redirect to the same page to refresh the list
            header("location: read.php");
            exit;
        } else {
            echo '<div class="alert alert-danger">Error: Could not delete the item. Please try again later.</div>';
        }

        // Close the statement
        mysqli_stmt_close($delete_stmt);
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
    /* Button Styles */
    .btn-primary {
        background-color: #F4A261 !important; /* Soft orange */
        border-color: #F4A261 !important;
    }

    .btn-primary:hover {
        background-color: #E76F51 !important; /* Muted orange-red */
        border-color: #E76F51 !important;
    }

    .btn-danger {
        background-color: #6D6875 !important; /* Muted gray-purple */
        border-color: #6D6875 !important;
    }

    .btn-danger:hover {
        background-color: #4A4E69 !important; /* Darker muted gray-purple */
        border-color: #4A4E69 !important;
    }

    .btn-success {
        background-color: #F4A261 !important; /* Soft orange */
        border-color: #F4A261 !important;
    }

    .btn-success:hover {
        background-color: #E76F51 !important; /* Muted orange-red */
        border-color: #E76F51 !important;
    }

    /* Table Header Styles */
    .table thead {
        background-color: #FFE8D6 !important; /* Light cream */
        color: #6D6875 !important; /* Muted gray-purple text */
    }

    /* Pagination Styles */
    .pagination .page-link {
        color: #F4A261 !important; /* Soft orange */
    }

    .pagination .page-item.active .page-link {
        background-color: #F4A261 !important; /* Soft orange */
        border-color: #F4A261 !important;
        color: white !important; /* Ensure text is visible */
    }

    /* Header Styles */
    .card-header {
        background-color: #F6D7B0 !important; /* Light beige-orange */
        color: #6D6875 !important; /* Muted gray-purple text */
    }

    /* Chart Colors */
    .chart-bar {
        background-color: rgba(244, 162, 97, 0.5); /* Soft orange */
        border-color: rgba(244, 162, 97, 1); /* Soft orange */
    }

    .chart-line {
        border-color: rgba(231, 111, 81, 1); /* Muted orange-red */
        background-color: rgba(231, 111, 81, 0.2); /* Muted orange-red */
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
            <form id="monthfilterform" method="GET" action="read.php" class="mb-4">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($_GET['month'] ?? ''); ?>">
            <input type="hidden" name="week" value="<?php echo htmlspecialchars($_GET['week'] ?? ''); ?>">
            <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 1); ?>">

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
                        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #ff6000 !important;">
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
    <?php if (isset($result) && mysqli_num_rows($result) > 0): // Only show pagination if the current query has results ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="read.php?page=<?php echo $i; ?>
                            <?php echo !empty(trim($date)) ? '&date=' . urlencode(trim($date)) : ''; ?>
                            <?php echo !empty(trim($month)) ? '&month=' . urlencode(trim($month)) : ''; ?>
                            <?php echo !empty(trim($week)) ? '&week=' . urlencode(trim($week)) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
    <hr class="my-4" style="border-top: 2px solid #343a40; width: 80%; margin: auto; border-radius: 5px; 
    background-color: #343a40; height: 2px;">
    <div class="row justify-content-center">
       <div class="col-md-10">
           <h4 class="text-center">Sales Summary</h4>
           <p class="text-center">Total sales for the selected period</p>
       </div>
    </div>
    <div class="row justify-content-center mt-4">
       <div class="col-md-10">
           <div class="text-center mb-4">
               <h5 class="fw-bold">
                   Total sales as of now: Php <?php echo number_format($total_sales, 2); ?>
               </h5>
           </div>
           <div class="text-center mb-4">
               <h5 class="fw-bold">Sales Insights for <?php echo date('F'); ?></h5>
               <p>Most-Selling Day: <strong><?php echo $highestSalesDay; ?></strong> | Php <strong><?php echo number_format($highestSalesAmount, 2); ?></strong></p>
               <p>Least-Selling Day: <strong><?php echo $lowestSalesDay; ?></strong> | Php <strong><?php echo number_format($lowestSalesAmount, 2); ?></strong></p>
           </div>
           <hr class="my-4" style="border-top: 2px solid #343a40; width: 80%; margin: auto; border-radius: 5px;
           background-color: #343a40; height: 2px;">
           <br>
           <form method="GET" action="read.php" class="d-flex align-items-center justify-content-end mb-2" style="position: relative; top: -20px;">
               <label for="chart_month" class="me-2">Select Month:</label>
               <input type="month" name="chart_month" id="chart_month" class="form-control form-control-sm w-auto" 
                      value="<?php echo isset($_GET['chart_month']) ? htmlspecialchars($_GET['chart_month']) : date('Y-m'); ?>" 
                      onchange="this.form.submit();" style="font-size: 0.85rem; padding: 2px 5px;">
           </form>
           <div class="d-flex flex-wrap justify-content-center gap-4">
               <div style="flex: 1; min-width: 300px;">
                   <canvas id="salesChart" height="200"></canvas>
               </div>
               <div style="flex: 1; min-width: 300px;">
                   <canvas id="dailySalesChart" height="200"></canvas>
               </div>
           </div>
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
    // Data for the first chart (updated to use orange)
    const chartLabels = <?php echo json_encode($chartLabels); ?>;
    const chartTotals = <?php echo json_encode($chartTotals); ?>;

    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Total Sales (by Product Type)',
                data: chartTotals,
                backgroundColor: 'rgba(255, 165, 0, 0.5)', // Orange
                borderColor: 'rgba(255, 165, 0, 1)', // Orange
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'All-time Sales by Product Type'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // NEW Chart for Daily Sales (updated to use green)
    const dailyLabels = <?php echo json_encode($dailySalesLabels); ?>;
    const dailyTotals = <?php echo json_encode($dailySalesTotals); ?>;

    const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
    const dailySalesChart = new Chart(dailyCtx, {
        type: 'line', // Line chart for daily trends
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Total Sales (Daily)',
                data: dailyTotals,
                fill: false,
                borderColor: 'rgba(0, 128, 0, 1)', // Green
                backgroundColor: 'rgba(0, 128, 0, 0.2)', // Green
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Daily Sales This Month'
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