<?php
// order_details.php
session_start();
require_once 'cache_bust.php';

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

$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    die('Order number is missing.');
}

// Fetch order details, including isDone, freight_status, and freight information
$query = "SELECT order_number, customer_name, OI_number, OI_marking, OI_ref, OI_comment, OI_checksum, isDone, freight_status, x_freight, y_freight, z_freight, kg_freight, freight_type, isFreightDone FROM order_management WHERE order_number = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $order_number);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die('Order not found.');
}

// Fetch order items that match the OI_checksum
$order_items_query = "SELECT * FROM order_items WHERE for_checksum = ?";
$stmt_items = $mysqli->prepare($order_items_query);
$stmt_items->bind_param("s", $order['OI_checksum']);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$order_items = [];
while ($item = $result_items->fetch_assoc()) {
    $order_items[] = $item;
}

// Map freight_type values to display names
$freight_types = [
    0 => 'I/A',
    1 => 'Brev',
    2 => 'Paket',
    3 => 'Pall',
    4 => 'Halvpall',
    5 => 'Halvpall m. krage',
    6 => 'Helpall',
    7 => 'Helpall m. krage',
    8 => 'Annan',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orderdetaljer - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css?v=<?php echo $cssVersion; ?>">
</head>
<body id='last-card' >
<div class="container">
    <div class="top-nav">
        <button onclick="window.history.back();" class="button">Tillbaka</button>
        <h2>Orderdetaljer</h2>
    </div>
    
    <div class="card">
        <div class="card-content">
            <h3>Produktionsnummer: <?php echo htmlspecialchars($order['order_number']); ?></h3>
            <p><strong>Kundnamn:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Ordernummer:</strong> <?php echo htmlspecialchars($order['OI_number']); ?></p>
            <p><strong>Märkning:</strong> <?php echo htmlspecialchars($order['OI_marking']); ?></p>
            <p><strong>Referens:</strong> <?php echo htmlspecialchars($order['OI_ref']); ?></p>
            <p><strong>Kommentar:</strong> <?php echo htmlspecialchars($order['OI_comment']); ?></p>
            <p><strong>Index:</strong> <?php echo htmlspecialchars($order['OI_checksum']); ?></p>
        </div>
    </div>
    
    <div class="card">
        
    <?php if (!empty($order_items)) { ?>

        <table class="part-table">
            <caption><h3>Produktionslista</h3></caption>
            <thead>
                <tr>
                    <th scope="col">Artikelnummer</th>
                    <th scope="col">Antal</th>
                    <th scope="col">Tjocklek</th>
                    <th scope="col">Material</th>
                    <th scope="col">Process</th>
                    <th scope="col">Kommentar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item) { ?>
                    <tr>
                        <td scope="row" data-label="Artikelnummer"><h3><?php echo htmlspecialchars($item['drawingNumber']); ?></h3></td>
                        <td data-label="An">
                            <span>
                                <?php echo htmlspecialchars($item['quantity']); ?><i>st</i>
                            </span>
                        </td>
                        <td data-label="Tj">
                            <span>
                                <?php echo htmlspecialchars($item['thickness']); ?><i>mm</i>
                            </span>
                        </td>
                        <td data-label="Mt"><?php echo htmlspecialchars($item['material']); ?></td>
                        <td data-label="Pr"><?php echo htmlspecialchars($item['process']); ?></td>
                        <td data-label="Km"><?php echo htmlspecialchars($item['comment']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>Inga orderobjekt hittades för denna order.</p>
    <?php } ?>
    </div>
</div>

<!-- Bottom nav -->
<nav class="bottom-nav">
    <div class="bottom-nav-content">
        <!-- "Freight" button -->
        <button id="freight-btn" class="nav-button" <?php echo ($order['freight_status'] === 'Skickas') ? '' : 'disabled'; ?>>
            <!-- Freight Icon -->
            <img src="assets/svg/freight.svg" type="image/svg+xml">
            <p>Frakt</p>
            <div class="selector"></div>
        </button>
        <!-- "Mark as Done" button -->
        <button id="mark-done-btn" class="nav-button" <?php echo ($order['isDone'] == 1) ? 'disabled' : ''; ?>>
            <!-- Done Icon -->
            <img id="thumbs-up" src="assets/svg/done.svg" type="image/svg+xml">
            <p>Godkänn</p>
            <div class="selector"></div>
        </button>
    </div>
</nav>

<!-- Modals -->
<!-- Freight Modal -->
<div id="freight-modal" class="modal hidden">
    <div id="modal-animation" class="modal-content">
        <h3>Fraktinformation</h3>
        <form id="freight-form" autocomplete="off">
            <div class="form-group">
                <label for="x_freight">Längd (X):</label>
                <input class="quite" type="number" id="x_freight" name="x_freight" required>
            </div>
            <div class="form-group">
                <label for="y_freight">Bredd (Y):</label>
                <input class="quite" type="number" id="y_freight" name="y_freight" required>
            </div>
            <div class="form-group">
                <label for="z_freight">Höjd (Z):</label>
                <input class="quite" type="number" id="z_freight" name="z_freight" required>
            </div>
            <div class="form-group">
                <label for="kg_freight">Vikt (kg):</label>
                <input class="quite" type="number" step="0.01" id="kg_freight" name="kg_freight" required>
            </div>
            <div class="form-group">
                <label for="freight_type">Frakttyp:</label>
                <select class="quite" id="freight_type" name="freight_type" required>
                    <option value="">Välj frakttyp</option>
                    <?php foreach ($freight_types as $key => $value) { ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php } ?>
                </select>
            </div>
            <!-- Submit button will be shown or hidden based on isFreightDone -->
            <?php if ($order['isFreightDone'] < 1) { ?>
                <button type="submit" class="button">Spara</button>
            <?php } ?>
            <button type="button" id="freight-cancel-btn" class="button cancel">Stäng</button>
        </form>
    </div>
</div>
<!-- Mark as Done Modal -->
<div id="mark-done-modal" class="modal hidden">
    <div id="modal-animation" class="modal-content">
        <h3>Statusuppdatering</h3>
        <p>Är ordern helt klar?</p>
        <br>
        <button id="mark-done-confirm-btn" class="button">Ja</button>
        <button id="mark-done-cancel-btn" class="button cancel">Avbryt</button>
    </div>
</div>

<!-- JavaScript Code -->
<script>
    // Pass the order number and freight data from PHP to JavaScript
    var orderNumber = <?php echo json_encode($order_number); ?>;
    var freightData = <?php echo json_encode([
        'isFreightDone' => $order['isFreightDone'],
        'x_freight' => $order['x_freight'],
        'y_freight' => $order['y_freight'],
        'z_freight' => $order['z_freight'],
        'kg_freight' => $order['kg_freight'],
        'freight_type' => $order['freight_type']
    ]); ?>;

    document.addEventListener('DOMContentLoaded', function() {
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

        // Event listener for the "Mark as Done" button
        document.getElementById('mark-done-btn').addEventListener('click', function() {
            document.getElementById('mark-done-modal').classList.remove('hidden');
            document.querySelectorAll('#modal-animation')[1].classList.add('active');
            document.querySelectorAll('.selector')[1].classList.add('selector-active');
            disableOutsideClicks();
        });

        // Handle "Mark as Done" modal buttons
        document.getElementById('mark-done-confirm-btn').addEventListener('click', function() {
            // Send AJAX request to update isDone to 1
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_order.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        // Handle response
                        if (xhr.responseText.trim() === 'success') {
                            showUpdateSuccess('Orderstatus uppdaterades!');
                            
                            // Disable the button
                            document.getElementById('mark-done-btn').disabled = true;
                            // Close modal
                            document.getElementById('mark-done-modal').classList.add('hidden');
                            document.querySelectorAll('.selector')[1].classList.remove('selector-active');
                            document.querySelectorAll('#modal-animation')[1].classList.remove('active');
                            document.getElementById('thumbs-up').classList.add('wobble-hor-bottom');
                            enableOutsideClicks();
                        } else {
                            alert('Fel vid uppdatering av ordern: ' + xhr.responseText);
                            // Close modal
                            document.getElementById('mark-done-modal').classList.add('hidden');
                            document.querySelectorAll('.selector')[1].classList.remove('selector-active');
                            document.querySelectorAll('#modal-animation')[1].classList.remove('active');
                            enableOutsideClicks();
                        }
                    } else {
                        alert('Ett fel inträffade. Status: ' + xhr.status);
                        // Close modal
                        document.getElementById('mark-done-modal').classList.add('hidden');
                        document.querySelectorAll('.selector')[1].classList.remove('selector-active');
                        document.querySelectorAll('#modal-animation')[1].classList.remove('active');
                        enableOutsideClicks();
                    }
                }
            };
            xhr.send('order_number=' + encodeURIComponent(orderNumber) + '&action=mark_done');
        });

        document.getElementById('mark-done-cancel-btn').addEventListener('click', function() {
            document.getElementById('mark-done-modal').classList.add('hidden');
            document.querySelectorAll('#modal-animation')[1].classList.remove('active');
            document.querySelectorAll('.selector')[1].classList.remove('selector-active');
            enableOutsideClicks();
        });

        // Event listener for the "Freight" button
        document.getElementById('freight-btn').addEventListener('click', function() {
            // Open freight modal
            document.getElementById('freight-modal').classList.remove('hidden');
            document.querySelectorAll('#modal-animation')[0].classList.add('active');
            document.querySelectorAll('.selector')[0].classList.add('selector-active');
            disableOutsideClicks();

            // Set input values from freightData
            document.getElementById('x_freight').value = freightData.x_freight || '';
            document.getElementById('y_freight').value = freightData.y_freight || '';
            document.getElementById('z_freight').value = freightData.z_freight || '';
            document.getElementById('kg_freight').value = freightData.kg_freight || '';
            document.getElementById('freight_type').value = freightData.freight_type || '';

            // If isFreightDone == 1, make inputs read-only and hide submit button
            if (freightData.isFreightDone > 0) {
                document.getElementById('x_freight').readOnly = true;
                document.getElementById('y_freight').readOnly = true;
                document.getElementById('z_freight').readOnly = true;
                document.getElementById('kg_freight').readOnly = true;
                document.getElementById('freight_type').disabled = true;

                // Hide the submit button
                var submitButton = document.getElementById('freight-submit-btn');
                if (submitButton) {
                    submitButton.style.display = 'none';
                }
            } else {
                // Ensure inputs are editable
                document.getElementById('x_freight').readOnly = false;
                document.getElementById('y_freight').readOnly = false;
                document.getElementById('z_freight').readOnly = false;
                document.getElementById('kg_freight').readOnly = false;
                document.getElementById('freight_type').disabled = false;

                // Show the submit button
                var submitButton = document.getElementById('freight-submit-btn');
                if (submitButton) {
                    submitButton.style.display = 'block';
                }
            }
        });

        // Handle freight form submission
        var freightForm = document.getElementById('freight-form');
        freightForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Check if freight is already done
            if (freightData.isFreightDone == 1 || 2) {
                // Freight information already entered; do nothing
                return;
            }

            // Get values from the form
            var x_freight = document.getElementById('x_freight').value;
            var y_freight = document.getElementById('y_freight').value;
            var z_freight = document.getElementById('z_freight').value;
            var kg_freight = document.getElementById('kg_freight').value;
            var freight_type = document.getElementById('freight_type').value;

            // Validate that all values are provided
            if (x_freight && y_freight && z_freight && kg_freight && freight_type !== '') {
                // Send AJAX request to update freight details
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_order.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            // Handle response
                            if (xhr.responseText.trim() === 'success') {
                                showUpdateSuccess('Fraktinfo inskickat!');
                                // Update freightData and isFreightDone
                                freightData.isFreightDone = 1;
                                freightData.x_freight = x_freight;
                                freightData.y_freight = y_freight;
                                freightData.z_freight = z_freight;
                                freightData.kg_freight = kg_freight;
                                freightData.freight_type = freight_type;

                                // Disable inputs
                                document.getElementById('x_freight').readOnly = true;
                                document.getElementById('y_freight').readOnly = true;
                                document.getElementById('z_freight').readOnly = true;
                                document.getElementById('kg_freight').readOnly = true;
                                document.getElementById('freight_type').disabled = true;


                                // Hide the submit button
                                var submitButton = document.getElementById('freight-submit-btn');
                                if (submitButton) {
                                    submitButton.style.display = 'none';
                                }

                                // Close modal
                                document.getElementById('freight-modal').classList.add('hidden');
                                document.querySelectorAll('.selector')[0].classList.remove('selector-active');
                                document.querySelectorAll('#modal-animation')[0].classList.remove('active');
                                enableOutsideClicks();
                            } else {
                                alert('Fel vid uppdatering av fraktinformation: ' + xhr.responseText);
                                enableOutsideClicks();
                            }
                        } else {
                            alert('Ett fel inträffade. Status: ' + xhr.status);
                            enableOutsideClicks();
                        }
                    }
                };
                var params = 'order_number=' + encodeURIComponent(orderNumber) + '&action=update_freight' +
                    '&x_freight=' + encodeURIComponent(x_freight) +
                    '&y_freight=' + encodeURIComponent(y_freight) +
                    '&z_freight=' + encodeURIComponent(z_freight) +
                    '&kg_freight=' + encodeURIComponent(kg_freight) +
                    '&freight_type=' + encodeURIComponent(freight_type);
                xhr.send(params);
            } else {
                alert('Vänligen fyll i alla fält.');
                enableOutsideClicks();
            }
        });

        // Handle freight modal cancel button
        document.getElementById('freight-cancel-btn').addEventListener('click', function() {
            document.getElementById('freight-modal').classList.add('hidden');
            document.querySelectorAll('#modal-animation')[0].classList.remove('active');
            document.querySelectorAll('.selector')[0].classList.remove('selector-active');
            enableOutsideClicks();
        });

        // Table row expand/collapse functionality
        let selectedRow = null;
        const tableRows = document.querySelectorAll('tbody tr');
        const card = document.getElementById('last-card');

        // Store the default margin-bottom of the card
        const defaultMarginBottom = window.getComputedStyle(card).marginBottom;

        tableRows.forEach(function(row) {
            // Select the first <td> with scope="row" within the row
            const firstTd = row.querySelector('td[scope="row"]');
            // Get the collapsed height from the first <td>
            const collapsedHeight = firstTd ? firstTd.offsetHeight : row.offsetHeight;
            // Store the collapsed height
            row.collapsedHeight = collapsedHeight;
            // Set the initial max-height
            row.style.maxHeight = collapsedHeight + 'px';

            row.addEventListener('click', function() {
                if (row.classList.contains('selected')) {
                    // Collapse the row
                    row.classList.remove('selected');
                    row.style.maxHeight = row.collapsedHeight + 'px';
                    selectedRow = null;

                    // Reset the margin-bottom of #last-card to default
                    card.style.marginBottom = defaultMarginBottom;
                } else {
                    // Collapse previously selected row
                    if (selectedRow && selectedRow !== row) {
                        selectedRow.classList.remove('selected');
                        selectedRow.style.maxHeight = selectedRow.collapsedHeight + 'px';
                    }
                    // Expand the clicked row
                    row.classList.add('selected');
                    const contentHeight = row.scrollHeight;
                    row.style.maxHeight = contentHeight + 'px';
                    selectedRow = row;

                    // Calculate additional height added
                    const additionalHeight = contentHeight - row.collapsedHeight;

                    // Adjust margin-bottom of #last-card
                    card.style.marginBottom = 0;

                    // Adjust scroll position to ensure the row is fully visible
                    setTimeout(function() {
                        // Get the root font size to calculate 1rem in pixels
                        const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
                        const extraOffset = rootFontSize * 2.2; // 1rem in pixels

                        const rowRect = row.getBoundingClientRect();
                        const bottomNav = document.querySelector('.bottom-nav');
                        const bottomNavHeight = bottomNav ? bottomNav.offsetHeight : 0;
                        const viewportHeight = window.innerHeight;

                        // Calculate the position of the bottom of the row relative to the viewport
                        const rowBottom = rowRect.bottom;

                        // If the bottom of the row is below the viewport height minus the bottom nav height
                        if (rowBottom > viewportHeight - bottomNavHeight) {
                            // Calculate how much to scroll
                            const scrollAmount = rowBottom - (viewportHeight - bottomNavHeight) + extraOffset;

                            // Scroll the window down by the scrollAmount
                            window.scrollBy({ top: scrollAmount, behavior: 'smooth' });
                        }
                    }, 300); // Adjust the timeout duration if necessary
                }
            });
        });
    });
</script>

</body>
</html>
<?php
$stmt->close();
$stmt_items->close();
$mysqli->close();
?>
