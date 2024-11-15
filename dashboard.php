<?php
// Start the session
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the database configuration file
require_once "config.php";

// Get the user's ID
$user_id = $_SESSION["id"];

// Fetch vape products (showing 10 products here)
$sql = "SELECT * FROM vape_products LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();

// Handle product purchase
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase'])) {
    $product_id = $_POST['product_id'];

    // Insert purchase into database
    $sql = "INSERT INTO purchases (user_id, product_id) VALUES (:user_id, :product_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Purchase successful!</div>';
    } else {
        echo '<div class="alert alert-danger">Something went wrong, please try again.</div>';
    }
}

// Fetch user history log
$sql_history = "SELECT * FROM purchases WHERE user_id = :user_id ORDER BY purchase_date DESC";
$stmt_history = $pdo->prepare($sql_history);
$stmt_history->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_history->execute();
$history = $stmt_history->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vape Shop - Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fa;
        }
        .navbar {
            background-color: #007bb5;
        }
        .navbar a {
            color: white;
        }
        .navbar a:hover {
            color: #00bcd4;
        }
        .container {
            margin-top: 50px;
        }
        .product-card {
            margin-bottom: 30px;
        }
        .product-img {
            height: 200px;
            object-fit: cover;
        }
        .purchase-btn {
            background-color: #007bb5;
            color: white;
        }
        .purchase-btn:hover {
            background-color: #00bcd4;
        }
        .alert-success {
            margin-top: 20px;
        }
        .alert-danger {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="#">Vape Shop</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="historylog.php">Purchase History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Log Out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Dashboard Content -->
<div class="container">
    <h2 class="text-center">Welcome to Vape Shop, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>

    <div class="row">
        <?php foreach ($products as $product) { ?>
            <div class="col-md-4">
                <div class="card product-card">
                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="card-img-top product-img">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                        <p class="card-text"><?php echo $product['description']; ?></p>
                        <p class="card-text"><strong>$<?php echo number_format($product['price'], 2); ?></strong></p>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="purchase" class="btn purchase-btn">Purchase</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <hr>

    <!-- Purchase History -->
    <h3>Your Purchase History</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Purchase Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $purchase) { 
                // Get the product name
                $sql_product = "SELECT name FROM vape_products WHERE id = :product_id";
                $stmt_product = $pdo->prepare($sql_product);
                $stmt_product->bindParam(':product_id', $purchase['product_id'], PDO::PARAM_INT);
                $stmt_product->execute();
                $product = $stmt_product->fetch();
            ?>
                <tr>
                    <td><?php echo $product['name']; ?></td>
                    <td><?php echo $purchase['purchase_date']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
