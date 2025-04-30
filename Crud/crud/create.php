<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

// Initialize form variables
$product_type = $product_name = $quantity_sold = $price = "";
$product_type_err = $product_name_err = $quantity_sold_err = $price_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty(trim($_POST["product_type"]))) {
        $product_type_err = "Please select a product type.";
    } else {
        $product_type = trim($_POST["product_type"]);
    }

    if (empty(trim($_POST["product_name"]))) {
        $product_name_err = "Please enter a product name.";
    } else {
        $product_name = trim($_POST["product_name"]);
    }

    if (empty(trim($_POST["quantity_sold"]))) {
        $quantity_sold_err = "Please enter quantity sold.";
    } elseif (!is_numeric(trim($_POST["quantity_sold"])) || trim($_POST["quantity_sold"]) < 0) {
        $quantity_sold_err = "Please enter a valid quantity.";
    } else {
        $quantity_sold = trim($_POST["quantity_sold"]);
    }

    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter price.";
    } elseif (!is_numeric(trim($_POST["price"])) || trim($_POST["price"]) <= 0) {
        $price_err = "Please enter a valid price.";
    } else {
        $price = trim($_POST["price"]);
    }

    // If no errors, insert into database
    if (empty($product_type_err) && empty($product_name_err) && empty($quantity_sold_err) && empty($price_err)) {
        $total_price = $price * $quantity_sold;

        $sql = "INSERT INTO items (Product_Type, Product_Name, Quantity_Sold, Price, Total_Price, user_id) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssiddi", $product_type, $product_name, $quantity_sold, $price, $total_price, $_SESSION["id"]);

            if (mysqli_stmt_execute($stmt)) {
                header("location: read.php");
                exit;
            } else {
                echo "Oops! Something went wrong. Try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D'Plato -- Create Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
    <style>
        /* Button Styles */
        .btn-primary {
            background-color: #ff6000 !important;
            border-color: #ff6000 !important;
        }

        .btn-primary:hover {
            background-color: #e69500 !important;
            border-color: #e69500 !important;
        }

        .btn-secondary {
            background-color: black !important;
            border-color: black !important;
        }

        .btn-secondary:hover {
            background-color: #333 !important;
            border-color: #333 !important;
        }

        /* Header Styles */
        .card-header {
            background-color: #ff6000 !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <?php
    // Include header
    require_once "../includes/header.php";
    ?>

    <div class="container mt-4 mb-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-white"><h4 class="mb-0">Create New Sales Report</h4></div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label>Product Type</label>
                                <select name="product_type" class="form-control <?php echo (!empty($product_type_err)) ? 'is-invalid' : ''; ?>">
                                    <option value="">-- Select Type --</option>
                                    <option value="Food" <?php if ($product_type == "Food") echo "selected"; ?>>Food</option>
                                    <option value="Drink" <?php if ($product_type == "Drink") echo "selected"; ?>>Drink</option>
                                </select>
                                <span class="invalid-feedback"><?php echo $product_type_err; ?></span>
                            </div>

                            <div class="form-group">
                                <label>Product Name</label>
                                <textarea name="product_name" class="form-control <?php echo (!empty($product_name_err)) ? 'is-invalid' : ''; ?>"><?php echo $product_name; ?></textarea>
                                <span class="invalid-feedback"><?php echo $product_name_err; ?></span>
                            </div>

                            <div class="form-group">
                                <label>Quantity Sold</label>
                                <input type="text" name="quantity_sold" class="form-control <?php echo (!empty($quantity_sold_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity_sold; ?>">
                                <span class="invalid-feedback"><?php echo $quantity_sold_err; ?></span>
                            </div>

                            <div class="form-group">
                                <label>Price</label>
                                <input type="text" name="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>">
                                <span class="invalid-feedback"><?php echo $price_err; ?></span>
                            </div>

                            <div class="form-group mt-3">
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="read.php" class="btn btn-secondary ml-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<br>
<br>
<br>
<br>
<br>
</body>
<?php
// Include footer
require_once "../includes/footer.php";
?>
</html>