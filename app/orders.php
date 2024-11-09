<?php
// orders.php
session_start();
require_once 'cache_bust.php';

// Enable error reporting (optional for debugging)
// Uncomment the lines below during development to display errors
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve database credentials from POST
    if (isset($_POST['db_host'])) {
        // Coming from index.php
        $db_host = $_POST['db_host'];
        $db_username = $_POST['db_username'];
        $db_password = $_POST['db_password'];
        $db_database = $_POST['db_database'];

        // Save credentials and username in session for reuse
        $_SESSION['db_credentials'] = [
            'host'     => $db_host,
            'username' => $db_username,
            'password' => $db_password,
            'database' => $db_database,
        ];
        $username = $_POST['username'];
        $_SESSION['username'] = $username;
    } else {
        // Invalid POST request
        die('Invalid request.');
    }

    // Redirect to self using GET method
    header('Location: orders.php');
    exit();
} else {
    // Coming from GET request
    // Retrieve credentials and username from session
    if (!isset($_SESSION['db_credentials'], $_SESSION['username'])) {
        // Redirect to index.php
        header('Location: index.php');
        exit();
    }

    $db_credentials = $_SESSION['db_credentials'];
    $db_host        = $db_credentials['host'];
    $db_username    = $db_credentials['username'];
    $db_password    = $db_credentials['password'];
    $db_database    = $db_credentials['database'];
    $username       = $_SESSION['username'];
}

// Fetch sorting preference from GET or session
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : (isset($_SESSION['sort_by']) ? $_SESSION['sort_by'] : 'days_left');
$valid_sort_columns = ['customer_name', 'order_number', 'days_left'];
if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'days_left';
}
$_SESSION['sort_by'] = $sort_by;

// Connect to the database
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch orders where isVisible = 1
$query  = "SELECT order_number, customer_name, order_dueDate, isDone, isCanceled, OI_critical FROM order_management WHERE isVisible = 1";
$result = $mysqli->query($query);
if (!$result) {
    die("Query failed: " . $mysqli->error);
}

// Fetch all orders into an array and calculate days_left
$orders = [];
$currentDate = new DateTime();
while ($row = $result->fetch_assoc()) {
    // Check if order_dueDate is valid
    if (!empty($row['order_dueDate'])) {
        // Parse the date with the format 'dd-mm-yyyy'
        $dueDate = DateTime::createFromFormat('d-m-Y', $row['order_dueDate']);
        if ($dueDate) {
            $interval = $currentDate->diff($dueDate);
            // Calculate the difference in days, including negative values
            $daysLeft = (int)$interval->format('%r%a');
        } else {
            // Handle invalid date format
            $daysLeft = null;
        }
    } else {
        // Handle missing dates
        $daysLeft = null;
    }
    $row['days_left'] = $daysLeft;
    $orders[] = $row;
}

// Close the database connection
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css?v=<?php echo $cssVersion; ?>">
</head>
<body>
<div class="container">
    <h2><i class="greet">Welcome</i> <?php echo htmlspecialchars($username); ?></h2>
    <h3>Orderlista</h3>
    <div id="order-list"></div>
</div>

<!-- Bottom Navbar -->
<nav class="bottom-nav">
    <div class="bottom-nav-content">
        <button id="connection-btn" class="nav-button">
            <!-- Database Icon -->
            <img src="assets/svg/database.svg" type="image/svg+xml">
            <p>Databas</p>
            <div class="selector"></div>
        </button>
        <button id="settings-btn" class="nav-button">
            <!-- Settings Icon -->
            <img src="assets/svg/settings.svg" type="image/svg+xml">
            <p>Inställningar</p>
            <div class="selector"></div>
        </button>
        <button id="sort-btn" class="nav-button">
            <!-- Sort Icon -->
            <img src="assets/svg/sort.svg" type="image/svg+xml">
            <p>Sortera</p>
            <div class="selector"></div>
        </button>
        <button id="refresh-btn" class="nav-button">
            <!-- Refresh Icon -->
            <img src="assets/svg/refresh.svg" type="image/svg+xml">
            <p>Uppdatera</p>
        </button>
    </div>
</nav>

<!-- Modals -->
<!-- Connection Settings Modal -->
<div id="connection-modal" class="modal hidden">
    <div id="modal-animation" class="modal-content">
        <h3>MySQL - Uppgifter</h3>
        <form id="connection-form">
            <!-- Display current credentials -->
            <div class="form-group">
                <label for="db_host">Databasvärd:</label>
                <input type="text" id="db_host" required value="<?php echo htmlspecialchars($db_host); ?>">
            </div>
            <div class="form-group">
                <label for="db_username">Användarnamn:</label>
                <input type="text" id="db_username" required value="<?php echo htmlspecialchars($db_username); ?>">
            </div>
            <div class="form-group">
                <label for="db_password">Lösenord:</label>
                <input type="password" id="db_password" required value="<?php echo htmlspecialchars($db_password); ?>">
            </div>
            <div class="form-group">
                <label for="db_database">Databasnamn:</label>
                <input type="text" id="db_database" required value="<?php echo htmlspecialchars($db_database); ?>">
            </div>
            <button type="submit" class="button">Spara</button>
            <button type="button" id="connection-cancel-btn" class="button cancel">Avbryt</button>
        </form>
    </div>
</div>

<!-- Settings Modal -->
<div id="settings-modal" class="modal hidden">
    <div id="modal-animation" class="modal-content">
        <div class="settings-top-section">
            <h3>Inställningar</h3>
            <button type="button" id="logout-btn" class="button logout">Logga ut</button>
        </div>
        
        <form id="settings-form">
            <div class="form-group">
                <label>
                    <input type="checkbox" id="auto_update">
                    Aktivera automatisk uppdatering
                </label>
            </div>
            <div class="form-group">
                <label for="update_interval">Uppdateringsintervall (sekunder):</label>
                <input type="number" id="update_interval" min="1">
            </div>

            <button type="submit" class="button">Spara</button>
            <button type="button" id="settings-cancel-btn" class="button cancel">Avbryt</button>
            
        </form>
    </div>
</div>

<!-- Sorting Modal -->
<div id="sorting-modal" class="modal hidden">
    <div id="modal-animation" class="modal-content">
        <h3>Sortera ordrar</h3>
        <form id="sorting-form">
            <div class="form-group">
                <label>
                    <input type="radio" name="sort_by" value="customer_name">
                    Kundnamn
                </label>
                <label>
                    <input type="radio" name="sort_by" value="order_number">
                    Produktionsnummer
                </label>
                <label>
                    <input type="radio" name="sort_by" value="days_left">
                    DTL
                </label>
            </div>
            <button type="submit" class="button">Sortera</button>
            <button type="button" id="sorting-cancel-btn" class="button cancel">Avbryt</button>
        </form>
    </div>
</div>

<!-- JavaScript Code -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Load settings from localStorage
    var autoUpdateEnabled = localStorage.getItem('auto_update') === 'true';
    var updateInterval = localStorage.getItem('update_interval') || 60;
    var autoUpdateTimer;
    var sortBy = localStorage.getItem('sort_by') || 'days_left';

    // Fetch orders data passed from PHP
    var ordersData = <?php echo json_encode($orders, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    // Check if ordersData is valid
    if (!Array.isArray(ordersData)) {
        console.error('Invalid orders data:', ordersData);
        alert('Failed to load orders data.');
        return;
    }

    // Sorting function
    function sortOrders() {
        if (sortBy === 'customer_name') {
            ordersData.sort(function(a, b) {
                return a.customer_name.localeCompare(b.customer_name);
            });
        } else if (sortBy === 'order_number') {
            ordersData.sort(function(a, b) {
                return a.order_number.localeCompare(b.order_number);
            });
        } else if (sortBy === 'days_left') {
            ordersData.sort(function(a, b) {
                return (a.days_left !== null ? a.days_left : Infinity) - (b.days_left !== null ? b.days_left : Infinity);
            });
        }
    }

    // Render the sorted orders
    function renderOrders() {
        var orderList = document.getElementById('order-list');
        orderList.innerHTML = ''; // Clear existing content
        ordersData.forEach(function(order) {
            var daysLeftDisplay = order.days_left !== null ? order.days_left : 'N/A';
            var card = document.createElement('div');
            card.className = 'card';
            card.onclick = function () {
                window.location = 'order_details.php?order_number=' + encodeURIComponent(order.order_number);
            };

            // Set background color based on order status
            if (order.isCanceled == 1) {
                card.style.backgroundColor = 'lightcoral'; // light red
            } else if (order.isDone == 1) {
                card.style.backgroundColor = 'lightgreen';
            } else if (order.OI_critical == 1) {
                card.style.backgroundColor = '#FFD580'; // light orange
            }
            // Else, default background color

            var content = '<div class="card-content">' +
                '<div class="main-screen-cards">' +
                '<h4>' + order.order_number + '</h4>' +
                '<p>' + daysLeftDisplay + '</p>' +
                '</div>' +
                '<p>' + order.customer_name + '</p>' +
                '</div>';
            card.innerHTML = content;
            orderList.appendChild(card);
        });
    }

    // Initial sorting and rendering of orders
    sortOrders();
    renderOrders();

    // Auto-update functionality
    if (autoUpdateEnabled) {
        startAutoUpdate(updateInterval);
    }

    function startAutoUpdate(interval) {
        autoUpdateTimer = setInterval(function () {
            location.reload();
        }, interval * 1000);
    }

    function stopAutoUpdate() {
        clearInterval(autoUpdateTimer);
    }

    // Function to disable interactions outside the modal
    function disableOutsideClicks() {
        document.body.classList.add('no-click');
    }

    // Function to enable interactions outside the modal
    function enableOutsideClicks() {
        document.body.classList.remove('no-click');
    }

    // Add a CSS rule to prevent clicks when modal is open
    const style = document.createElement('style');
    style.innerHTML = `
        .no-click *:not(.modal):not(.modal *) {
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);

    // Function to show update success message
    function showUpdateSuccess(message) {
        const updateDiv = document.createElement('div');
        updateDiv.classList.add('update-success');
        updateDiv.textContent = message || 'Ändringar sparade!';
        document.body.appendChild(updateDiv);
        setTimeout(() => {
            updateDiv.style.top = '20px';
            updateDiv.style.opacity = '1';
        }, 100);
        setTimeout(() => {
            updateDiv.style.top = '-50px';
            updateDiv.style.opacity = '0';
        }, 2000);
        setTimeout(() => {
            document.body.removeChild(updateDiv);
        }, 2500);
    }

    // Event listeners for navbar buttons
    document.getElementById('connection-btn').addEventListener('click', function () {
        document.getElementById('connection-modal').classList.remove('hidden');
        document.querySelectorAll('#modal-animation')[0].classList.add('active');
        document.querySelectorAll('.selector')[0].classList.add('selector-active');
        disableOutsideClicks();
    });

    document.getElementById('settings-btn').addEventListener('click', function () {
        // Set the checkbox and interval value
        document.getElementById('auto_update').checked = autoUpdateEnabled;
        document.getElementById('update_interval').value = updateInterval;
        document.getElementById('settings-modal').classList.remove('hidden');
        document.querySelectorAll('#modal-animation')[1].classList.add('active');
        document.querySelectorAll('.selector')[1].classList.add('selector-active');
        disableOutsideClicks();
    });

    document.getElementById('sort-btn').addEventListener('click', function () {
        // Set the selected radio button
        var radios = document.getElementsByName('sort_by');
        for (var i = 0; i < radios.length; i++) {
            if (radios[i].value === sortBy) {
                radios[i].checked = true;
                break;
            }
        }
        document.getElementById('sorting-modal').classList.remove('hidden');
        document.querySelectorAll('#modal-animation')[2].classList.add('active');
        document.querySelectorAll('.selector')[2].classList.add('selector-active');
        disableOutsideClicks();
    });

    document.getElementById('refresh-btn').addEventListener('click', function () {
        location.reload();
    });

    // Handle settings form
    document.getElementById('settings-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var autoUpdate = document.getElementById('auto_update').checked;
        var interval = parseInt(document.getElementById('update_interval').value, 10) || 60;
        localStorage.setItem('auto_update', autoUpdate);
        localStorage.setItem('update_interval', interval);
        if (autoUpdate) {
            stopAutoUpdate();
            startAutoUpdate(interval);
        } else {
            stopAutoUpdate();
        }
        document.getElementById('settings-modal').classList.add('hidden');
        document.querySelectorAll('#modal-animation')[1].classList.remove('active');
        document.querySelectorAll('.selector')[1].classList.remove('selector-active');
        enableOutsideClicks();
        showUpdateSuccess();
    });

    document.getElementById('settings-cancel-btn').addEventListener('click', function () {
        document.getElementById('settings-modal').classList.add('hidden');
        document.querySelectorAll('#modal-animation')[1].classList.remove('active');
        document.querySelectorAll('.selector')[1].classList.remove('selector-active');
        enableOutsideClicks();
    });

    // Handle logout button
    document.getElementById('logout-btn').addEventListener('click', function () {
        // Clear user selection from localStorage
        localStorage.removeItem('selected_username');
        // Destroy PHP session
        fetch('logout.php', { method: 'POST' })
            .then(() => {
                // Redirect to index.php
                window.location.href = 'index.php';
            });
    });

    // Handle sorting form
    document.getElementById('sorting-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var selectedSort = document.querySelector('input[name="sort_by"]:checked').value;
        localStorage.setItem('sort_by', selectedSort);
        sortBy = selectedSort; // Update the sortBy variable
        sortOrders();
        renderOrders();
        document.getElementById('sorting-modal').classList.add('hidden');
        document.querySelectorAll('#modal-animation')[2].classList.remove('active');
        document.querySelectorAll('.selector')[2].classList.remove('selector-active');
        enableOutsideClicks();
        showUpdateSuccess();
    });

    document.getElementById('sorting-cancel-btn').addEventListener('click', function () {
        document.getElementById('sorting-modal').classList.add('hidden');
        document.querySelectorAll('#modal-animation')[2].classList.remove('active');
        document.querySelectorAll('.selector')[2].classList.remove('selector-active');
        enableOutsideClicks();
    });

    // Handle connection settings form
    document.getElementById('connection-form').addEventListener('submit', function (e) {
        e.preventDefault();
        // Update credentials in localStorage
        var dbCredentials = {
            host: document.getElementById('db_host').value,
            username: document.getElementById('db_username').value,
            password: document.getElementById('db_password').value,
            database: document.getElementById('db_database').value,
        };
        localStorage.setItem('db_credentials', JSON.stringify(dbCredentials));
        // Since we can't update the session from here, redirect to index.php
        alert('Database credentials updated. Please log in again.');
        window.location = 'index.php';
        enableOutsideClicks();
    });

    document.getElementById('connection-cancel-btn').addEventListener('click', function () {
        document.getElementById('connection-modal').classList.add('hidden');
        document.querySelectorAll('#modal-animation')[0].classList.remove('active');
        document.querySelectorAll('.selector')[0].classList.remove('selector-active');
        enableOutsideClicks();
    });

    function updateGreeting() {
        const now = new Date();
        const hours = now.getHours();
        let greeting;

        if (hours < 5) {
            greeting = 'God natt';
        } else if (hours < 10) {
            greeting = 'God morgon';
        } else if (hours < 12) {
            greeting = 'God förmiddag';
        } else if (hours < 14) {
            greeting = 'God middag';
        } else if (hours < 18) {
            greeting = 'God eftermiddag';
        } else {
            greeting = 'God kväll';
        }

        const greetingElement = document.querySelector('.greet');

        if (greetingElement) {
            greetingElement.textContent = `${greeting}`;
        }
    }

    updateGreeting();

});
</script>
</body>
</html>
