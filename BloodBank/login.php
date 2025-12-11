<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) $errors[] = 'Enter email and password.';
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'hospital') {
                header('Location: hospital_add_blood.php');
            } else {
                header('Location: available_samples.php');
            }
            exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - BloodBank Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .auth-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 2rem 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .auth-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .auth-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    }
    .auth-header {
      background: linear-gradient(135deg, #e63946 0%, #c1121f 100%);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    .auth-header i {
      font-size: 3rem;
      margin-bottom: 1rem;
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      width: 80px;
      height: 80px;
      border-radius: 50%;
      line-height: 80px;
      text-align: center;
    }
    .auth-body {
      padding: 2.5rem;
      background: white;
    }
    .form-control {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #dee2e6;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      border-color: #e63946;
      box-shadow: 0 0 0 0.25rem rgba(230, 57, 70, 0.15);
    }
    .btn-login {
      background: linear-gradient(135deg, #e63946 0%, #c1121f 100%);
      border: none;
      padding: 0.75rem 2rem;
      font-weight: 500;
      border-radius: 50px;
      transition: all 0.3s ease;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(230, 57, 70, 0.3);
    }
    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 1.5rem 0;
      color: #6c757d;
      font-size: 0.875rem;
    }
    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #dee2e6;
    }
    .divider::before {
      margin-right: 1rem;
    }
    .divider::after {
      margin-left: 1rem;
    }
    .register-links {
      text-align: center;
      margin-top: 1.5rem;
    }
    .register-links a {
      color: #e63946;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .register-links a:hover {
      color: #c1121f;
      text-decoration: underline;
    }
    .register-links span {
      color: #6c757d;
      margin: 0 0.5rem;
    }
  </style>
</head>
<body>
  <div class="auth-container">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
          <div class="auth-card">
            <div class="auth-header">
              <i class="bi bi-droplet-fill"></i>
              <h2 class="h4 mb-0">Welcome Back</h2>
              <p class="mb-0">Sign in to your BloodBank account</p>
            </div>
            <div class="auth-body">
              <?php if (!empty($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="bi bi-check-circle-fill me-2"></i> Registration successful. Please login.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="bi bi-exclamation-triangle-fill me-2"></i>
                  <?php foreach($errors as $e): ?>
                    <div><?=htmlspecialchars($e)?></div>
                  <?php endforeach; ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              
              <form method="post" class="mb-0">
                <div class="mb-3">
                  <label for="email" class="form-label">Email address</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required 
                           value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
                  </div>
                </div>
                
                <div class="mb-4">
                  <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Password</label>
                    <a href="#" class="text-decoration-none small text-muted">Forgot password?</a>
                  </div>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                    <button class="btn btn-outline-secondary bg-transparent" type="button" id="togglePassword">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>
                
                <div class="d-grid mb-3">
                  <button type="submit" class="btn btn-login text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                  </button>
                </div>
                
                <div class="divider">or continue with</div>
                
                <div class="register-links">
                  <span>Don't have an account?</span>
                  <a href="register_receiver.php">Register as Receiver</a>
                  <span>or</span>
                  <a href="register_hospital.php">Register as Hospital</a>
                </div>
              </form>
            </div>
          </div>
          
          <div class="text-center mt-4">
            <p class="text-muted small">© <?=date('Y')?> BloodBank. All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const password = document.getElementById('password');
      const icon = this.querySelector('i');
      
      if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    });
    
    // Focus the email field on page load
    document.addEventListener('DOMContentLoaded', function() {
      const emailField = document.getElementById('email');
      if (emailField) {
        emailField.focus();
      }
    });
  </script>
</body>
</html>
