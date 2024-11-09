<?php
// fetch_users.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
    exit();
}

$db_host = $data['host'];
$db_username = $data['username'];
$db_password = $data['password'];
$db_database = $data['database'];

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_database);

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit();
}

$query = "SELECT username FROM users";
$result = $mysqli->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $mysqli->error]);
    exit();
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row['username'];
}

echo json_encode(['success' => true, 'users' => $users]);

$mysqli->close();
?>
