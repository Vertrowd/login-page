<?php
// dashboard.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database configuration
$host = 'localhost';
$dbname = 'user_auth';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user details including last_login
    $stmt = $pdo->prepare("SELECT id, username, email, created_at, last_login FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($user['username']); ?></title>
    <style>
        /* Your existing CSS styles remain the same */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-section h1 {
            color: #333;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: #666;
        }

        .logout-btn {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .user-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .user-info h3 {
            color: white;
        }

        .info-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .info-label {
            font-weight: bold;
        }

        .info-value {
            text-align: right;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .quick-actions {
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 12px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .action-btn:hover {
            background: #5a6fd8;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .dashboard-content {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 👋</h1>
                <p>Here's your personal dashboard</p>
            </div>
            <a href="?logout=1" class="logout-btn">Logout</a>
        </div>

        <div class="dashboard-content">
            <!-- User Profile Card -->
            <div class="card user-info">
                <div class="profile-pic">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h3>Profile Information</h3>
                <div class="info-item">
                    <span class="info-label">Username:</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">User ID:</span>
                    <span class="info-value">#<?php echo $user['id']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member since:</span>
                    <span class="info-value">
                        <?php 
                            if ($user['created_at']) {
                                $created = new DateTime($user['created_at']);
                                echo $created->format('M j, Y');
                            } else {
                                echo 'Unknown';
                            }
                        ?>
                    </span>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card">
                <h3>Your Statistics</h3>
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-number">1</span>
                        <span class="stat-label">Account</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">0</span>
                        <span class="stat-label">Projects</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Profile Complete</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <button class="action-btn" onclick="alert('Profile editing coming soon!')">Edit Profile</button>
                    <button class="action-btn" onclick="alert('Settings coming soon!')">Settings</button>
                    <button class="action-btn" onclick="alert('Help center coming soon!')">Help Center</button>
                </div>
            </div>
        </div>

        <!-- Recent Activity Card -->
        <div class="card">
            <h3>Recent Activity</h3>
            <div style="padding: 10px 0;">
                <div class="activity-item">
                    <span>✅</span>
                    <span>Account created on 
                        <?php 
                            if ($user['created_at']) {
                                $created = new DateTime($user['created_at']);
                                echo $created->format('M j, Y \a\t g:i A');
                            } else {
                                echo 'Unknown date';
                            }
                        ?>
                    </span>
                </div>
                <div class="activity-item">
                    <span>🕒</span>
                    <span>Last login: 
                        <?php 
                            if ($user['last_login']) {
                                $lastLogin = new DateTime($user['last_login']);
                                echo $lastLogin->format('M j, Y \a\t g:i A');
                            } else {
                                echo 'First login!';
                            }
                        ?>
                    </span>
                </div>
                <div class="activity-item">
                    <span>🔐</span>
                    <span>Current session started: <?php echo date('M j, Y \a\t g:i A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add functionality to action buttons
        document.querySelectorAll('.action-btn').forEach(button => {
            button.addEventListener('click', function() {
                // You can add specific functionality for each button here
                console.log('Button clicked:', this.textContent);
            });
        });
    </script>
</body>
</html>