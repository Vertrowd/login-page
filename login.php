<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'user_auth';
$username = 'root';
$password = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $action = $_POST['action'] ?? '';
        
        if ($action === 'signup') {
            handleSignup($pdo);
        } elseif ($action === 'login') {
            handleLogin($pdo);
        } else {
            throw new Exception('Invalid action.');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

function handleSignup($pdo) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        throw new Exception('All fields are required.');
    }

    if ($password !== $confirm_password) {
        throw new Exception('Passwords do not match.');
    }

    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format.');
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('Username or email already exists.');
    }

    // Create user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$username, $email, $hashed_password])) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! You can now login.',
            'redirect' => '?login=1'
        ]);
    } else {
        throw new Exception('Registration failed. Please try again.');
    }
}

function handleLogin($pdo) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        throw new Exception('Please enter both username and password.');
    }

    // Check user credentials
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Update last login timestamp
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => 'dashboard.php'
        ]);
    } else {
        throw new Exception('Invalid username or password.');
    }
}
// Check if user is logged in for dashboard
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === 'dashboard.php') {
    // This would be in a separate dashboard.php file, but for demo, we'll handle it here
    displayDashboard();
    exit;
}

function displayDashboard() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f4f4f4; }
            .dashboard { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
            .welcome { margin-bottom: 20px; text-align: center; }
            .logout { display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; }
            .logout:hover { background: #c82333; }
        </style>
    </head>
    <body>
        <div class="dashboard">
            <div class="welcome">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p>You're now logged in to your account.</p>
            </div>
            <a href="?logout=1" class="logout">Logout</a>
        </div>
    </body>
    </html>
    <?php
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?');
    exit;
}

// Determine which form to show
$showLogin = isset($_GET['login']) || !isset($_GET['signup']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showLogin ? 'Login' : 'Sign Up'; ?></title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
        z-index: -1;
    }

    .container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 50px 40px;
        border-radius: 20px;
        box-shadow: 
            0 20px 40px rgba(76, 175, 80, 0.3),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        width: 100%;
        max-width: 480px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4CAF50, #81C784, #4CAF50);
    }

    .tabs {
        display: flex;
        margin-bottom: 35px;
        border-bottom: 2px solid #e8f5e8;
        position: relative;
    }

    .tab {
        flex: 1;
        padding: 18px;
        text-align: center;
        background: none;
        border: none;
        font-size: 16px;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .tab.active {
        color: #2E7D32;
    }

    .tab::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 50%;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #4CAF50, #81C784);
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .tab.active::after {
        width: 80%;
    }

    h2 {
        text-align: center;
        margin-bottom: 35px;
        color: #2E7D32;
        font-weight: 700;
        font-size: 28px;
        position: relative;
    }

    h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, #4CAF50, #81C784);
        border-radius: 2px;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #455a64;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #e0f2e0;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: #f9fffa;
        color: #2E7D32;
        font-weight: 500;
    }

    input:focus {
        outline: none;
        border-color: #4CAF50;
        background: white;
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        transform: translateY(-2px);
    }

    input::placeholder {
        color: #a5d6a7;
    }

    button {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 50%, #4CAF50 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(76, 175, 80, 0.4);
    }

    button:hover::before {
        left: 100%;
    }

    button:active {
        transform: translateY(-1px);
    }

    button:disabled {
        background: #c8e6c9;
        transform: none;
        cursor: not-allowed;
        box-shadow: none;
    }

    .message {
        margin: 25px 0;
        padding: 18px;
        border-radius: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .message.success {
        background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
        color: #2E7D32;
        border-color: #a5d6a7;
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.1);
    }

    .message.error {
        background: linear-gradient(135deg, #ffebee, #ffcdd2);
        color: #c62828;
        border-color: #ef9a9a;
        box-shadow: 0 5px 15px rgba(198, 40, 40, 0.1);
    }

    .message.hidden {
        display: none;
    }

    .form-container {
        display: none;
        animation: fadeIn 0.5s ease;
    }

    .form-container.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .switch-text {
        text-align: center;
        margin-top: 25px;
        color: #78909c;
        font-size: 14px;
    }

    .switch-text a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: 700;
        position: relative;
        transition: all 0.3s ease;
    }

    .switch-text a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: #4CAF50;
        transition: width 0.3s ease;
    }

    .switch-text a:hover {
        color: #2E7D32;
    }

    .switch-text a:hover::after {
        width: 100%;
    }

    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        pointer-events: none;
        z-index: -1;
    }

    .shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    .shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .shape:nth-child(2) {
        width: 120px;
        height: 120px;
        top: 60%;
        right: 10%;
        animation-delay: 2s;
    }

    .shape:nth-child(3) {
        width: 60px;
        height: 60px;
        bottom: 20%;
        left: 20%;
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <button class="tab <?php echo $showLogin ? 'active' : ''; ?>" onclick="showForm('login')">Login</button>
            <button class="tab <?php echo !$showLogin ? 'active' : ''; ?>" onclick="showForm('signup')">Sign Up</button>
        </div>

        <!-- Login Form -->
        <div id="loginForm" class="form-container <?php echo $showLogin ? 'active' : ''; ?>">
            <h2>Welcome Back</h2>
            <form id="loginFormElement" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="loginUsername">Username:</label>
                    <input type="text" id="loginUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password:</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <button type="submit" id="loginBtn">Login</button>
            </form>
            <div class="switch-text">
                Don't have an account? <a href="?signup=1">Sign up here</a>
            </div>
        </div>

        <!-- Signup Form -->
        <div id="signupForm" class="form-container <?php echo !$showLogin ? 'active' : ''; ?>">
            <h2>Create Account</h2>
            <form id="signupFormElement" method="POST">
                <input type="hidden" name="action" value="signup">
                <div class="form-group">
                    <label for="signupUsername">Username:</label>
                    <input type="text" id="signupUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="signupEmail">Email:</label>
                    <input type="email" id="signupEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="signupPassword">Password:</label>
                    <input type="password" id="signupPassword" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required minlength="8">
                </div>
                <button type="submit" id="signupBtn">Sign Up</button>
            </form>
            <div class="switch-text">
                Already have an account? <a href="?login=1">Login here</a>
            </div>
        </div>

        <div id="messageDiv" class="message hidden"></div>
    </div>
    <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
</div>

    <script>
        function showForm(formType) {
            // Update tabs
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.form-container').forEach(form => form.classList.remove('active'));
            
            document.querySelector(`[onclick="showForm('${formType}')"]`).classList.add('active');
            document.getElementById(formType + 'Form').classList.add('active');
            
            // Clear messages
            hideMessage();
        }

        // Login form handling
        document.getElementById('loginFormElement').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'loginBtn');
        });

        // Signup form handling
        document.getElementById('signupFormElement').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                showMessage('Passwords do not match!', 'error');
                return;
            }
            
            if (password.length < 8) {
                showMessage('Password must be at least 8 characters long!', 'error');
                return;
            }
            
            submitForm(this, 'signupBtn');
        });

        function submitForm(form, btnId) {
            const formData = new FormData(form);
            const submitBtn = document.getElementById(btnId);
            
            submitBtn.disabled = true;
            submitBtn.textContent = btnId === 'loginBtn' ? 'Logging in...' : 'Creating Account...';

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => {
                            if (data.redirect === 'dashboard.php') {
                                window.location.href = data.redirect;
                            } else {
                                window.location.href = window.location.pathname + data.redirect;
                            }
                        }, 1500);
                    }
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = btnId === 'loginBtn' ? 'Login' : 'Sign Up';
            });
        }

        function showMessage(message, type) {
            const messageDiv = document.getElementById('messageDiv');
            messageDiv.textContent = message;
            messageDiv.className = `message ${type}`;
            messageDiv.classList.remove('hidden');
            
            if (type === 'success') {
                setTimeout(hideMessage, 5000);
            }
        }

        function hideMessage() {
            document.getElementById('messageDiv').classList.add('hidden');
        }
    </script>
</body>
</html>