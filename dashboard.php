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
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
        min-height: 100vh;
        padding: 30px 20px;
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 80%, rgba(129, 199, 132, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(76, 175, 80, 0.2) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(46, 125, 50, 0.2) 0%, transparent 50%);
        z-index: -1;
    }

    .dashboard-container {
        max-width: 1300px;
        margin: 0 auto;
    }

    .header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 25px 35px;
        border-radius: 20px;
        box-shadow: 
            0 15px 35px rgba(76, 175, 80, 0.2),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        margin-bottom: 35px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4CAF50, #81C784, #4CAF50);
    }

    .welcome-section h1 {
        color: #2E7D32;
        margin-bottom: 8px;
        font-weight: 700;
        font-size: 32px;
        background: linear-gradient(135deg, #2E7D32, #4CAF50);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .welcome-section p {
        color: #78909c;
        font-size: 16px;
        font-weight: 500;
    }

    .logout-btn {
        padding: 14px 28px;
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 14px;
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        position: relative;
        overflow: hidden;
    }

    .logout-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .logout-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
    }

    .logout-btn:hover::before {
        left: 100%;
    }

    .dashboard-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
        margin-bottom: 35px;
    }

    .card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 30px;
        border-radius: 20px;
        box-shadow: 
            0 10px 30px rgba(76, 175, 80, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4CAF50, #81C784, #4CAF50);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(76, 175, 80, 0.25);
    }

    .card h3 {
        color: #2E7D32;
        margin-bottom: 20px;
        font-size: 1.4em;
        font-weight: 700;
        position: relative;
        display: inline-block;
    }

    .card h3::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 40px;
        height: 3px;
        background: linear-gradient(90deg, #4CAF50, #81C784);
        border-radius: 2px;
    }

    .user-info {
        background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
        color: white;
        position: relative;
    }

    .user-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
        background-size: cover;
    }

    .user-info h3 {
        color: white;
    }

    .user-info h3::after {
        background: rgba(255, 255, 255, 0.5);
    }

    .info-item {
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        position: relative;
        z-index: 1;
    }

    .info-label {
        font-weight: 600;
        opacity: 0.9;
    }

    .info-value {
        text-align: right;
        font-weight: 500;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .stat-item {
        text-align: center;
        padding: 20px 15px;
        background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
        border-radius: 12px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .stat-item:hover {
        transform: translateY(-3px);
        border-color: #4CAF50;
        box-shadow: 0 8px 20px rgba(76, 175, 80, 0.2);
    }

    .stat-number {
        font-size: 2.2em;
        font-weight: 800;
        display: block;
        background: linear-gradient(135deg, #2E7D32, #4CAF50);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        font-size: 0.85em;
        color: #78909c;
        font-weight: 600;
        margin-top: 5px;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 14px 22px;
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        position: relative;
        overflow: hidden;
        flex: 1;
        min-width: 120px;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }

    .action-btn:hover::before {
        left: 100%;
    }

    .profile-pic {
        width: 90px;
        height: 90px;
        background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5em;
        font-weight: 800;
        margin: 0 auto 20px;
        border: 3px solid rgba(255,255,255,0.3);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }

    .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid #e8f5e8;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #f9fffa;
        border-radius: 8px;
        padding: 12px 15px;
        margin: 0 -15px;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
            padding: 20px 25px;
        }
        
        .dashboard-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .welcome-section h1 {
            font-size: 26px;
        }
    }

    .floating-particles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
    }

    .particle {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: floatParticle 8s ease-in-out infinite;
    }

    @keyframes floatParticle {
        0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); }
        33% { transform: translateY(-30px) translateX(20px) rotate(120deg); }
        66% { transform: translateY(15px) translateX(-15px) rotate(240deg); }
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
    <div class="floating-particles" id="particles"></div>

    <script>
        const particlesContainer = document.getElementById('particles');
    for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.width = Math.random() * 20 + 10 + 'px';
        particle.style.height = particle.style.width;
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 5 + 's';
        particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
        particlesContainer.appendChild(particle);
    }
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