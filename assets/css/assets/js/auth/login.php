
<?php
session_start();
require_once '../config/database.php';

$error = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Semua field harus diisi.";
    } else {
        // Check user in database
        $query = "SELECT * FROM users WHERE email = '$email' AND active = 1";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header('Location: ../admin/dashboard.php');
                } elseif ($user['role'] == 'guide') {
                    header('Location: ../guide/dashboard.php');
                } else {
                    header('Location: ../user/dashboard.php');
                }
                exit();
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Email tidak ditemukan atau akun tidak aktif.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LombokHiking</title>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-form .form-group {
            margin-bottom: 20px;
        }
        
        .auth-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .auth-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .auth-form input.error {
            border-color: #f44336;
        }
        
        .error-message {
            color: #f44336;
            margin-top: 5px;
            font-size: 14px;
        }
        
        .auth-form button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            font-size: 16px;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .social-login {
            margin-top: 30px;
            text-align: center;
        }
        
        .social-login p {
            position: relative;
            margin-bottom: 20px;
        }
        
        .social-login p::before,
        .social-login p::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 35%;
            height: 1px;
            background-color: #e0e0e0;
        }
        
        .social-login p::before {
            left: 0;
        }
        
        .social-login p::after {
            right: 0;
        }
        
        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1px solid #e0e0e0;
            color: #666;
            font-size: 20px;
            transition: all 0.3s ease;
        }
        
        .social-button:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <?php include_once '../includes/simple-header.php'; ?>
    
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Login ke LombokHiking</h2>
                <p>Masukkan email dan password Anda untuk melanjutkan</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" action="login.php" id="loginForm" onsubmit="return validateForm('loginForm')">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                    <div id="email-error" class="error-message" style="display: none;">Email tidak valid.</div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div id="password-error" class="error-message" style="display: none;">Password harus diisi.</div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <label style="margin-bottom: 0;">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                    <a href="forgot-password.php" style="color: var(--primary-color); text-decoration: none;">Lupa password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="form-footer">
                Belum punya akun? <a href="register.php">Daftar sekarang</a>
            </div>
            
            <div class="social-login">
                <p>Atau masuk dengan</p>
                <div class="social-buttons">
                    <a href="#" class="social-button"><i class="fab fa-google"></i></a>
                    <a href="#" class="social-button"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-button"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
