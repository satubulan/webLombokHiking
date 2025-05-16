
<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi.";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok.";
    } elseif (strlen($password) < 8) {
        $error = "Password harus minimal 8 karakter.";
    } else {
        // Check if email already exists
        $check_query = "SELECT * FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows > 0) {
            $error = "Email sudah terdaftar.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insert_query = "INSERT INTO users (name, email, password, phone, role, active) 
                           VALUES ('$name', '$email', '$hashed_password', '$phone', 'user', 1)";
            
            if ($conn->query($insert_query) === TRUE) {
                $success = "Pendaftaran berhasil! Silahkan login.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LombokHiking</title>
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
        
        .success-message {
            color: #4caf50;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <?php include_once '../includes/simple-header.php'; ?>
    
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Daftar di LombokHiking</h2>
                <p>Buat akun untuk dapat memesan open trip pendakian</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                    <p style="margin-top: 10px;"><a href="login.php">Login sekarang</a></p>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <form class="auth-form" method="POST" action="register.php" id="registerForm" onsubmit="return validateForm('registerForm')">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required>
                        <div id="name-error" class="error-message" style="display: none;">Nama harus diisi.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                        <div id="email-error" class="error-message" style="display: none;">Email tidak valid.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" placeholder="Contoh: 081234567890">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <div id="password-error" class="error-message" style="display: none;">Password minimal 8 karakter.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <div id="confirm_password-error" class="error-message" style="display: none;">Password tidak cocok.</div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label>
                            <input type="checkbox" name="terms" required> Saya setuju dengan <a href="../terms.php">Syarat & Ketentuan</a> dan <a href="../privacy.php">Kebijakan Privasi</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </form>
            <?php endif; ?>
            
            <div class="form-footer">
                Sudah punya akun? <a href="login.php">Login sekarang</a>
            </div>
        </div>
    </div>
    
    <?php include_once '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Additional validation for registration form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            if (!form) return;
            
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Check password strength
            password.addEventListener('input', function() {
                const value = this.value;
                const errorElement = document.getElementById('password-error');
                
                if (value.length < 8) {
                    errorElement.style.display = 'block';
                    errorElement.textContent = 'Password minimal 8 karakter.';
                    this.classList.add('error');
                } else {
                    errorElement.style.display = 'none';
                    this.classList.remove('error');
                }
            });
            
            // Check password match
            confirmPassword.addEventListener('input', function() {
                const errorElement = document.getElementById('confirm_password-error');
                
                if (this.value !== password.value) {
                    errorElement.style.display = 'block';
                    this.classList.add('error');
                } else {
                    errorElement.style.display = 'none';
                    this.classList.remove('error');
                }
            });
        });
    </script>
</body>
</html>
