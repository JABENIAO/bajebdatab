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

// Default join type
$join_type = "INNER";  // Default to INNER JOIN

// Check if a button was clicked to change the join type
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['inner_join'])) {
        $join_type = "INNER";
    } elseif (isset($_POST['left_join'])) {
        $join_type = "LEFT";
    } elseif (isset($_POST['right_join'])) {
        $join_type = "RIGHT";
    }
}

// SQL query based on selected join type
switch ($join_type) {
    case "INNER":
        $sql = "SELECT p.id, u.username, vp.name AS product_name, p.purchase_date
                FROM purchases p
                INNER JOIN users u ON p.user_id = u.id
                INNER JOIN vape_products vp ON p.product_id = vp.id
                ORDER BY p.purchase_date DESC";
        break;
    case "LEFT":
        $sql = "SELECT p.id, u.username, vp.name AS product_name, p.purchase_date
                FROM purchases p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN vape_products vp ON p.product_id = vp.id
                ORDER BY p.purchase_date DESC";
        break;
    case "RIGHT":
        $sql = "SELECT p.id, u.username, vp.name AS product_name, p.purchase_date
                FROM purchases p
                RIGHT JOIN users u ON p.user_id = u.id
                RIGHT JOIN vape_products vp ON p.product_id = vp.id
                ORDER BY p.purchase_date DESC";
        break;
    default:
        $sql = "SELECT p.id, u.username, vp.name AS product_name, p.purchase_date
                FROM purchases p
                INNER JOIN users u ON p.user_id = u.id
                INNER JOIN vape_products vp ON p.product_id = vp.id
                ORDER BY p.purchase_date DESC";
        break;
}

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute();
$purchases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vape Shop - Purchase History Log</title>
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
        .table th, .table td {
            text-align: center;
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
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="history_log.php">Purchase History Log</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Log Out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Join Type Buttons -->
<div class="container">
    <h2 class="text-center">Purchase History Log</h2>
    
    <!-- Buttons for Join Selection -->
    <div class="text-center">
        <form method="post">
            <button type="submit" name="inner_join" class="btn btn-primary">INNER JOIN</button>
            <button type="submit" name="left_join" class="btn btn-success">LEFT JOIN</button>
            <button type="submit" name="right_join" class="btn btn-danger">RIGHT JOIN</button>
        </form>
    </div>
    
    <br>

    <!-- Table Displaying Purchase History -->
    <?php if (empty($purchases)): ?>
        <div class="alert alert-danger">No purchase history found.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Product Name</th>
                    <th>Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($purchases as $index => $purchase): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($purchase['username']) ?: 'Unknown User'; ?></td>
                        <td><?php echo htmlspecialchars($purchase['product_name']) ?: 'Unknown Product'; ?></td>
                        <td><?php echo $purchase['purchase_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
