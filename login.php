<?php
// users/login.php - Full login and registration system
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if config file exists
if (!file_exists('../config/database.php')) {
    die('Config file not found. Please create config/database.php');
}

require_once '../config/database.php';

// Create users table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) UNIQUE NOT NULL,
        Email VARCHAR(100) UNIQUE NOT NULL,
        Password VARCHAR(255) NOT NULL,
        FullName VARCHAR(100) NOT NULL,
        Role ENUM('ADMIN', 'MANAGER', 'CASHIER', 'PHARMACIST') DEFAULT 'CASHIER',
        Phone VARCHAR(20),
        Address TEXT,
        Status ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') DEFAULT 'ACTIVE',
        LastLogin DATETIME,
        CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CreatedBy VARCHAR(50) DEFAULT 'SYSTEM'
    )");
} catch(Exception $e) {
    // Table might already exist
}

$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login'; // login or register

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($username && $password) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE Username = ? AND Status = 'ACTIVE'");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['Password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['UserID'];
                    $_SESSION['username'] = $user['Username'];
                    $_SESSION['full_name'] = $user['FullName'];
                    $_SESSION['role'] = $user['Role'];
                    $_SESSION['email'] = $user['Email'];
                    
                    // Update last login
                    $update_stmt = $pdo->prepare("UPDATE users SET LastLogin = NOW() WHERE UserID = ?");
                    $update_stmt->execute([$user['UserID']]);
                    
                    header('Location: ../dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid username or password';
                }
            } catch (Exception $e) {
                $error = 'Login failed. Please try again.';
            }
        } else {
            $error = 'Please enter both username and password';
        }
    } 
    elseif ($_POST['action'] === 'register') {
        $username = $_POST['reg_username'] ?? '';
        $email = $_POST['reg_email'] ?? '';
        $password = $_POST['reg_password'] ?? '';
        $confirm_password = $_POST['reg_confirm_password'] ?? '';
        $full_name = $_POST['reg_full_name'] ?? '';
        $phone = $_POST['reg_phone'] ?? '';
        $role = $_POST['reg_role'] ?? 'CASHIER';
        
        if ($username && $email && $password && $full_name) {
            if ($password === $confirm_password) {
                try {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (Username, Email, Password, FullName, Phone, Role) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $role]);
                    
                    $success = 'Registration successful! You can now login.';
                    $mode = 'login';
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $error = 'Username or email already exists';
                    } else {
                        $error = 'Registration failed: ' . $e->getMessage();
                    }
                }
            } else {
                $error = 'Passwords do not match';
            }
        } else {
            $error = 'Please fill all required fields';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pharmacy Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            margin-bottom: 15px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            width: 100%;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .alert {
            border-radius: 10px;
        }

        .test-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-pills"></i>
            </div>
            <h2 class="mb-2"><?php echo $mode === 'register' ? 'Create Account' : 'Welcome Back'; ?></h2>
            <p class="text-muted"><?php echo $mode === 'register' ? 'Sign up for pharmacy account' : 'Sign in to your pharmacy account'; ?></p>
        </div>

        <!-- Mode Toggle Buttons -->
        <div class="row mb-3">
            <div class="col-6">
                <a href="?mode=login" class="btn <?php echo $mode === 'login' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
            <div class="col-6">
                <a href="?mode=register" class="btn <?php echo $mode === 'register' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <?php if ($mode === 'login'): ?>
        <!-- Login Form -->
        <form method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-user"></i> Username
                </label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <?php else: ?>
        <!-- Registration Form -->
        <form method="POST">
            <input type="hidden" name="action" value="register">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Full Name *
                    </label>
                    <input type="text" name="reg_full_name" class="form-control" placeholder="Enter full name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="fas fa-user-circle"></i> Username *
                    </label>
                    <input type="text" name="reg_username" class="form-control" placeholder="Choose username" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-envelope"></i> Email *
                </label>
                <input type="email" name="reg_email" class="form-control" placeholder="Enter email address" required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-phone"></i> Phone (Optional)
                </label>
                <input type="tel" name="reg_phone" class="form-control" placeholder="Enter phone number">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-user-tag"></i> Role
                </label>
                <select name="reg_role" class="form-control">
                    <option value="CASHIER">Cashier</option>
                    <option value="PHARMACIST">Pharmacist</option>
                    <option value="MANAGER">Manager</option>
                    <option value="ADMIN">Admin</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password *
                    </label>
                    <input type="password" name="reg_password" class="form-control" placeholder="Create password" required minlength="6">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Confirm Password *
                    </label>
                    <input type="password" name="reg_confirm_password" class="form-control" placeholder="Confirm password" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="fas fa-shield-alt"></i> Secure pharmacy management system
            </small>
        </div>
        
        <?php if ($mode === 'login'): ?>
        <div class="text-center mt-2">
            <small class="text-muted">
                Don't have an account? <a href="?mode=register" class="text-primary">Register here</a>
            </small>
        </div>
        <?php else: ?>
        <div class="text-center mt-2">
            <small class="text-muted">
                Already have an account? <a href="?mode=login" class="text-primary">Login here</a>
            </small>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>