<?php
require_once 'functions.php';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blood Bank Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="available_samples.php">
        <i class="bi bi-droplet-fill text-danger"></i> BloodBank
      </a>
      <div class="d-flex align-items-center">
        <?php if ($user): ?>
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-2"></i>
              <span class="d-none d-md-inline"><?=htmlspecialchars($user['name'])?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><span class="dropdown-item-text small text-muted">Logged in as <strong><?=htmlspecialchars($user['role'])?></strong></span></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="login.php" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
          </a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="flex-grow-1">
    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0">
          <i class="bi bi-droplet me-2 text-danger"></i>Available Blood Samples
        </h2>
        <?php if ($user && $user['role'] === 'hospital'): ?>
          <a href="hospital_add_blood.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Blood Sample
          </a>
        <?php endif; ?>
      </div>

  <?php
    $stmt = $pdo->query("SELECT s.*, u.name as hospital_name, u.address as hospital_address, u.phone as hospital_phone
      FROM blood_samples s
      JOIN users u ON s.hospital_id = u.id
      WHERE s.units > 0
      ORDER BY s.added_at DESC
    ");
    $samples = $stmt->fetchAll();
  ?>

      <?php if (!$samples): ?>
        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="bi bi-info-circle-fill me-2"></i>
          <div>No blood samples available currently. Please check back later.</div>
        </div>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <?php foreach($samples as $s): 
            $can_request = false;
            if ($user && $user['role'] === 'receiver') {
              $can_request = can_receive_from($user['blood_group'], $s['blood_group']);
            }
          ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                  <div class="card-body d-flex flex-column h-100">
                    <!-- Header Section -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <div>
                        <span class="badge bg-danger bg-opacity-10 text-danger mb-2 px-2 py-1 rounded-1">
                          <i class="bi bi-droplet-fill me-1"></i> <?=htmlspecialchars($s['blood_group']) ?>
                        </span>
                        <h5 class="card-title mb-1 fw-semibold"><?=htmlspecialchars($s['units'])?> Unit<?=$s['units'] > 1 ? 's' : ''?> Available</h5>
                        <div class="text-muted small d-flex align-items-center">
                          <i class="bi bi-hospital me-1"></i> 
                          <span class="text-truncate"><?=htmlspecialchars($s['hospital_name']) ?></span>
                        </div>
                      </div>
                      <div class="text-end">
                        <div class="text-muted small">
                          <i class="bi bi-calendar3 me-1"></i> 
                          <?=date('M d, Y', strtotime($s['added_at']))?>
                        </div>
                        <?php if ($user && $user['role'] === 'receiver'): ?>
                          <div class="mt-1 small fw-bold <?=$can_request ? 'text-success' : 'text-danger'?>">
                            <i class="bi <?=$can_request ? 'bi-check-circle-fill' : 'bi-x-circle-fill'?> me-1"></i>
                            <?=$can_request ? 'Compatible' : 'Not compatible'?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                    
                    <!-- Details Section -->
                    <div class="mb-3">
                      <div class="d-flex align-items-center text-muted small mb-2">
                        <i class="bi bi-geo-alt-fill me-2 flex-shrink-0"></i>
                        <span class="text-truncate"><?=htmlspecialchars($s['hospital_address']) ?></span>
                      </div>
                      <div class="d-flex align-items-center text-muted small mb-2">
                        <i class="bi bi-telephone-fill me-2 flex-shrink-0"></i>
                        <a href="tel:<?=htmlspecialchars($s['hospital_phone'])?>" class="text-decoration-none text-reset">
                          <?=htmlspecialchars($s['hospital_phone']) ?>
                        </a>
                      </div>
                      <?php if (!empty($s['notes'])): ?>
                        <div class="alert alert-light p-2 small mb-0 mt-2">
                          <i class="bi bi-info-circle me-1"></i> <?=htmlspecialchars($s['notes']) ?>
                        </div>
                      <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-auto pt-2">
                      <?php if (!$user): ?>
                        <a href="login.php" class="btn btn-primary w-100">
                          <i class="bi bi-box-arrow-in-right me-1"></i> Login to Request
                        </a>
                      <?php elseif ($user['role'] === 'hospital'): ?>
                        <button class="btn btn-outline-secondary w-100" disabled>
                          <i class="bi bi-hospital me-1"></i> Hospitals cannot request
                        </button>
                      <?php else: ?>
                        <?php if ($can_request): ?>
                          <form method="post" action="request_sample.php" class="w-100">
                            <input type="hidden" name="sample_id" value="<?=intval($s['id']) ?>">
                            <button type="submit" class="btn btn-primary w-100">
                              <i class="bi bi-heart-pulse me-1"></i> Request Sample
                            </button>
                          </form>
                        <?php else: ?>
                          <button class="btn btn-outline-danger w-100" disabled>
                            <i class="bi bi-x-circle me-1"></i> Incompatible
                          </button>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer mt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5><i class="bi bi-droplet-fill"></i> BloodBank System</h5>
          <p class="mb-0">Connecting donors with those in need. Every drop counts.</p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="mb-0">&copy; <?=date('Y')?> BloodBank. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
  </script>
</body>
</html>
