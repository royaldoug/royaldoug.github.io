<?php
// update_order.php
session_start();

if (!isset($_SESSION['db_credentials'])) {
    die('Database credentials are missing.');
}

$db_credentials = $_SESSION['db_credentials'];
$db_host = $db_credentials['host'];
$db_username = $db_credentials['username'];
$db_password = $db_credentials['password'];
$db_database = $db_credentials['database'];

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$order_number = $_POST['order_number'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($order_number) || empty($action)) {
    die('Order number or action is missing.');
}

// Sanitize inputs
$order_number = $mysqli->real_escape_string($order_number);

if ($action == 'mark_done') {
    // Update isDone to 1
    $query = "UPDATE order_management SET isDone = 1 WHERE order_number = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $order_number);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error updating order: ' . $stmt->error;
    }
    $stmt->close();
} elseif ($action == 'update_freight') {
    // Collect and validate freight details
    $x_freight = $_POST['x_freight'] ?? '';
    $y_freight = $_POST['y_freight'] ?? '';
    $z_freight = $_POST['z_freight'] ?? '';
    $kg_freight = $_POST['kg_freight'] ?? '';
    $freight_type = $_POST['freight_type'] ?? '';

    if ($x_freight === '' || $y_freight === '' || $z_freight === '' || $kg_freight === '' || $freight_type === '') {
        die('All freight details are required.');
    }

    // Ensure numerical values are valid
    if (!is_numeric($x_freight) || !is_numeric($y_freight) || !is_numeric($z_freight) || !is_numeric($kg_freight) || !is_numeric($freight_type)) {
        die('Invalid input. Numerical values are required.');
    }

    // Prepare and execute the update query
    $query = "UPDATE order_management SET x_freight = ?, y_freight = ?, z_freight = ?, kg_freight = ?, freight_type = ?, isFreightDone = 1 WHERE order_number = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiiiis", $x_freight, $y_freight, $z_freight, $kg_freight, $freight_type, $order_number);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error updating freight information: ' . $stmt->error;
    }
    $stmt->close();
} else {
    echo 'Invalid action.';
}

$mysqli->close();
?>
