<?php
// index.php
session_start();
require_once 'cache_bust.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EPM</title>
    <!-- Meta viewport tag for responsiveness -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css?v=<?php echo $cssVersion; ?>">
</head>
<body>
<div id="loading-screen" style="display: none;">
    <!-- Loading spinner or message -->
    <div class="spinner"></div>
    <p>Laddar...</p>
</div>

<div class="container" id="user-selection-container" style="display: none;">
    <h2>Välj användare</h2>
    <!-- User Selection Form -->
    <form id="user-form" action="orders.php" method="post">
        <div class="form-group">
            <label for="username">Användare:</label>
            <select id="username" name="username" required>
                <!-- Options will be populated via JavaScript -->
            </select>
        </div>
        <button type="submit" class="button">Fortsätt</button>
    </form>
</div>

<!-- MySQL Credentials Modal -->
<div id="credentials-modal" class="modal hidden">
    <div class="modal-content">
        <h3>MySQL - Uppgifter</h3>
        <form id="credentials-form">
            <div class="form-group">
                <label for="db_host">Databasvärd</label>
                <input type="text" id="db_host" required value="localhost">
            </div>
            <div class="form-group">
                <label for="db_username">Användarnamn:</label>
                <input type="text" id="db_username" required>
            </div>
            <div class="form-group">
                <label for="db_password">Lösenord:</label>
                <input type="password" id="db_password" required>
            </div>
            <div class="form-group">
                <label for="db_database">Databasnamn:</label>
                <input type="text" id="db_database" required value="production_schema">
            </div>
            <button type="submit" class="button">Anslut</button>
        </form>
    </div>
</div>

<script>
// index.php JavaScript
document.addEventListener('DOMContentLoaded', async function () {
    const dbCredentials = JSON.parse(localStorage.getItem('db_credentials'));
    const selectedUsername = localStorage.getItem('selected_username');
    const loadingScreen = document.getElementById('loading-screen');
    const userSelectionContainer = document.getElementById('user-selection-container');

    async function initializeApp() {
        if (!dbCredentials) {
            // Show the credentials modal
            document.getElementById('credentials-modal').classList.remove('hidden');
            userSelectionContainer.style.display = 'none';
        } else if (selectedUsername) {
            // Show loading screen
            loadingScreen.style.display = 'flex';
            // Automatically log in with saved user
            await submitUserForm(selectedUsername);
        } else {
            // Fetch users and show user selection
            await fetchUsers();
        }
    }

    // Handle credentials form submission
    document.getElementById('credentials-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        // Get credentials from form inputs
        const dbCredentials = {
            host: document.getElementById('db_host').value,
            username: document.getElementById('db_username').value,
            password: document.getElementById('db_password').value,
            database: document.getElementById('db_database').value,
        };
        // Save credentials to localStorage
        localStorage.setItem('db_credentials', JSON.stringify(dbCredentials));
        // Hide the credentials modal
        document.getElementById('credentials-modal').classList.add('hidden');
        // Fetch users
        await fetchUsers();
    });

    // Fetch users from the server
    async function fetchUsers() {
        const dbCredentials = JSON.parse(localStorage.getItem('db_credentials'));
        try {
            const response = await fetch('fetch_users.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(dbCredentials)
            });
            const data = await response.json();
            if (data.success) {
                populateUserSelect(data.users);
                userSelectionContainer.style.display = 'block';
            } else {
                alert('Failed to fetch users: ' + data.message);
                // Clear credentials and show modal again
                localStorage.removeItem('db_credentials');
                document.getElementById('credentials-modal').classList.remove('hidden');
                userSelectionContainer.style.display = 'none';
            }
        } catch (error) {
            alert('Error fetching users: ' + error);
            // Clear credentials and show modal again
            localStorage.removeItem('db_credentials');
            document.getElementById('credentials-modal').classList.remove('hidden');
            userSelectionContainer.style.display = 'none';
        }
    }

    // Populate the user select dropdown
    function populateUserSelect(users) {
        const userSelect = document.getElementById('username');
        userSelect.innerHTML = '';
        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user;
            option.textContent = user;
            userSelect.appendChild(option);
        });
    }

    // Handle user form submission
    document.getElementById('user-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        // Save the selected username to localStorage
        const selectedUser = document.getElementById('username').value;
        localStorage.setItem('selected_username', selectedUser);

        // Show loading screen
        loadingScreen.style.display = 'flex';
        userSelectionContainer.style.display = 'none';

        // Submit the form
        await submitUserForm(selectedUser);
    });

    async function submitUserForm(selectedUser) {
        try {
            // Create a form and submit it
            const dbCredentials = JSON.parse(localStorage.getItem('db_credentials'));
            const formData = new FormData();
            // Append db credentials
            for (const key in dbCredentials) {
                formData.append('db_' + key, dbCredentials[key]);
            }
            // Append username
            formData.append('username', selectedUser);

            // Send data via fetch
            const response = await fetch('orders.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });

            if (response.redirected) {
                window.location.href = response.url;
            } else {
                // If not redirected, check for errors
                const text = await response.text();
                // Handle session errors or display the content
                document.open();
                document.write(text);
                document.close();
            }
        } catch (error) {
            alert('Error logging in: ' + error);
            loadingScreen.style.display = 'none';
            userSelectionContainer.style.display = 'block';
        }
    }

    await initializeApp();
});
</script>
</body>
</html>
