<?php
require_once 'config.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $regno = trim($_POST['regno'] ?? '');

    if (!$name || !$email || !$password || !$confirm) $errors[] = 'Name, email and password required.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role,phone,address) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$name,$email,$hash,'hospital',$phone,$address]);
            $hid = $pdo->lastInsertId();
            $stmt2 = $pdo->prepare('INSERT INTO hospitals (user_id, registration_number) VALUES (?,?)');
            $stmt2->execute([$hid,$regno]);
            header('Location: login.php?msg=registered');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hospital Registration - BloodBank</title>
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
      background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
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
    .form-control, .form-select, .form-textarea {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #dee2e6;
      transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus, .form-textarea:focus {
      border-color: #1d3557;
      box-shadow: 0 0 0 0.25rem rgba(29, 53, 87, 0.15);
    }
    .btn-register {
      background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
      border: none;
      padding: 0.75rem 2rem;
      font-weight: 500;
      border-radius: 50px;
      transition: all 0.3s ease;
      width: 100%;
    }
    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(29, 53, 87, 0.3);
    }
    .input-group-text {
      background-color: transparent;
      border-right: none;
    }
    .form-control.is-invalid, .was-validated .form-control:invalid,
    .form-select.is-invalid, .was-validated .form-select:invalid,
    .form-textarea.is-invalid, .was-validated .form-textarea:invalid {
      border-color: #dc3545;
      padding-right: calc(1.5em + 0.75rem);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right calc(0.375em + 0.1875rem) center;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    .form-control.is-valid, .was-validated .form-control:valid,
    .form-select.is-valid, .was-validated .form-select:valid,
    .form-textarea.is-valid, .was-validated .form-textarea:valid {
      border-color: #198754;
      padding-right: calc(1.5em + 0.75rem);
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right calc(0.375em + 0.1875rem) center;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    .invalid-feedback {
      font-size: 0.875em;
      color: #dc3545;
    }
    .form-text {
      font-size: 0.75rem;
      color: #6c757d;
    }
    .password-strength {
      height: 4px;
      background-color: #e9ecef;
      border-radius: 2px;
      margin-top: 0.5rem;
      overflow: hidden;
    }
    .password-strength-bar {
      height: 100%;
      width: 0;
      transition: width 0.3s ease, background-color 0.3s ease;
    }
    .back-to-login {
      text-align: center;
      margin-top: 1.5rem;
    }
    .back-to-login a {
      color: #1d3557;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .back-to-login a:hover {
      color: #14213d;
      text-decoration: underline;
    }
    .form-textarea {
      min-height: 100px;
      resize: vertical;
    }
    .feature-list {
      list-style: none;
      padding-left: 0;
      margin: 1rem 0;
    }
    .feature-list li {
      margin-bottom: 0.5rem;
      padding-left: 1.75rem;
      position: relative;
    }
    .feature-list li:before {
      content: '✓';
      color: #2a9d8f;
      position: absolute;
      left: 0;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="auth-container">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
          <div class="auth-card">
            <div class="auth-header">
              <i class="bi bi-hospital"></i>
              <h2 class="h4 mb-1">Hospital Registration</h2>
              <p class="mb-0">Join our network of healthcare providers</p>
            </div>
            <div class="auth-body">
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="bi bi-exclamation-triangle-fill me-2"></i>
                  <?php foreach($errors as $e): ?>
                    <div><?=htmlspecialchars($e)?></div>
                  <?php endforeach; ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              
              <form method="post" class="needs-validation" novalidate>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="name" class="form-label">Hospital Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-building"></i></span>
                      <input type="text" class="form-control" id="name" name="name" required 
                             value="<?=htmlspecialchars($_POST['name'] ?? '')?>">
                      <div class="invalid-feedback">Please enter the hospital name.</div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                      <input type="email" class="form-control" id="email" name="email" required
                             value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
                      <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-lock"></i></span>
                      <input type="password" class="form-control" id="password" name="password" required
                             minlength="8" oninput="checkPasswordStrength(this.value)">
                      <button class="btn btn-outline-secondary bg-transparent" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                      </button>
                      <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                    </div>
                    <div class="password-strength mt-2">
                      <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="form-text">Use 8 or more characters with a mix of letters, numbers & symbols</div>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                      <input type="password" class="form-control" id="confirm" name="confirm" required>
                      <div class="invalid-feedback">Passwords must match.</div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                      <input type="tel" class="form-control" id="phone" name="phone" required
                             value="<?=htmlspecialchars($_POST['phone'] ?? '')?>">
                      <div class="invalid-feedback">Please enter a valid phone number.</div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="regno" class="form-label">Registration Number</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-card-checklist"></i></span>
                      <input type="text" class="form-control" id="regno" name="regno" 
                             value="<?=htmlspecialchars($_POST['regno'] ?? '')?>">
                    </div>
                    <div class="form-text">Optional registration/license number</div>
                  </div>
                  
                  <div class="col-12">
                    <label for="address" class="form-label">Full Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                      <textarea class="form-control form-textarea" id="address" name="address" required><?=htmlspecialchars($_POST['address'] ?? '')?></textarea>
                      <div class="invalid-feedback">Please enter the hospital address.</div>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <div class="alert alert-info">
                      <h6 class="alert-heading"><i class="bi bi-info-circle-fill me-2"></i>Why register as a hospital?</h6>
                      <ul class="feature-list mb-0">
                        <li>Manage blood inventory in real-time</li>
                        <li>Connect with potential donors and recipients</li>
                        <li>Receive blood requests directly</li>
                        <li>Access to our network of healthcare providers</li>
                      </ul>
                    </div>
                  </div>
                </div>
                
                <div class="d-grid mt-4">
                  <button type="submit" class="btn btn-register text-white">
                    <i class="bi bi-hospital me-2"></i> Register Hospital
                  </button>
                </div>
                
                <div class="back-to-login">
                  Already have an account? <a href="login.php">Sign in here</a>
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
    // Form validation
    (function () {
      'use strict'
      
      // Fetch the form we want to apply custom Bootstrap validation styles to
      const form = document.querySelector('.needs-validation')
      const password = document.getElementById('password')
      const confirm = document.getElementById('confirm')
      
      // Toggle password visibility
      document.getElementById('togglePassword').addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
      });
      
      // Password match validation
      function validatePassword() {
        if (password.value !== confirm.value) {
          confirm.setCustomValidity("Passwords don't match");
          confirm.classList.add('is-invalid');
        } else {
          confirm.setCustomValidity('');
          confirm.classList.remove('is-invalid');
        }
      }
      
      // Password strength indicator
      function checkPasswordStrength(password) {
        const strengthBar = document.getElementById('passwordStrengthBar');
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength += 1;
        
        // Contains numbers
        if (/\d/.test(password)) strength += 1;
        
        // Contains letters
        if (/[a-zA-Z]/.test(password)) strength += 1;
        
        // Contains special characters
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Update strength bar
        const width = (strength / 4) * 100;
        strengthBar.style.width = width + '%';
        
        // Update color based on strength
        if (strength <= 1) {
          strengthBar.style.backgroundColor = '#dc3545'; // Red
        } else if (strength <= 2) {
          strengthBar.style.backgroundColor = '#fd7e14'; // Orange
        } else if (strength <= 3) {
          strengthBar.style.backgroundColor = '#ffc107'; // Yellow
        } else {
          strengthBar.style.backgroundColor = '#198754'; // Green
        }
      }
      
      password.addEventListener('input', function() {
        validatePassword();
        checkPasswordStrength(this.value);
      });
      
      confirm.addEventListener('input', validatePassword);
      
      // Form submission
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        
        form.classList.add('was-validated')
      }, false)
    })()
  </script>
</body>
</html>
