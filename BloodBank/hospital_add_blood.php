<?php
require_once 'functions.php';
require_role('hospital');
$user = current_user();
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_group = strtoupper(trim($_POST['blood_group'] ?? ''));
    $units = intval($_POST['units'] ?? 1);
    $notes = trim($_POST['notes'] ?? '');

    $valid = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
    if (!in_array($blood_group, $valid)) $errors[] = 'Invalid blood group.';
    if ($units < 1) $errors[] = 'Units must be at least 1.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO blood_samples (hospital_id, blood_group, units, notes) VALUES (?,?,?,?)');
        $stmt->execute([$user['id'],$blood_group,$units,$notes]);
        $success = 'Blood info added successfully.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hospital Dashboard - BloodBank</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .dashboard-header {
      background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
      padding: 1.5rem 0;
      margin-bottom: 2rem;
      color: white;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .welcome-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    .blood-group-selector {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0.75rem;
      margin: 1rem 0;
    }
    .blood-group-option {
      position: relative;
      cursor: pointer;
    }
    .blood-group-option input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }
    .blood-group-option label {
      display: block;
      padding: 1rem;
      text-align: center;
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      font-weight: 600;
    }
    .blood-group-option input[type="radio"]:checked + label {
      background: #1d3557;
      color: white;
      border-color: #1d3557;
    }
    .inventory-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-top: 2rem;
    }
    .table {
      margin-bottom: 0;
    }
    .table thead th {
      background-color: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    .table tbody tr:last-child td {
      border-bottom: none;
    }
    .btn-action {
      padding: 0.5rem 1.25rem;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .btn-action i {
      margin-right: 0.5rem;
    }
    .form-control, .form-select {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #dee2e6;
    }
    .form-control:focus, .form-select:focus {
      border-color: #1d3557;
      box-shadow: 0 0 0 0.25rem rgba(29, 53, 87, 0.15);
    }
    .nav-tabs .nav-link {
      border: none;
      color: #6c757d;
      font-weight: 500;
      padding: 0.75rem 1.25rem;
      border-radius: 8px 8px 0 0;
    }
    .nav-tabs .nav-link.active {
      background: white;
      color: #1d3557;
      border-bottom: 3px solid #1d3557;
    }
    .status-badge {
      padding: 0.35em 0.65em;
      font-size: 0.75em;
      font-weight: 600;
      border-radius: 50px;
    }
  </style>
</head>
<body style="background-color: #f8f9fa; font-family: 'Poppins', sans-serif;">
  <!-- Header -->
  <div class="dashboard-header">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="h4 mb-0"><i class="bi bi-hospital me-2"></i>Hospital Dashboard</h2>
          <p class="mb-0 small opacity-75">Manage your blood inventory and requests</p>
        </div>
        <div>
          <a href="available_samples.php" class="btn btn-outline-light btn-sm me-2">
            <i class="bi bi-droplet"></i> View All Samples
          </a>
          <a href="hospital_view_requests.php" class="btn btn-outline-light btn-sm me-2">
            <i class="bi bi-bell"></i> View Requests
          </a>
          <a href="logout.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <!-- Welcome Card -->
    <div class="welcome-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="mb-1">Welcome back, <?=htmlspecialchars($user['name'])?></h4>
          <p class="text-muted mb-0">Add new blood samples to your inventory</p>
        </div>
        <div class="text-end">
          <div class="text-primary fw-bold"><?=date('l, F j, Y')?></div>
          <div class="small text-muted"><?=date('h:i A')?></div>
        </div>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i> <?=$success?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      
      <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?php foreach($errors as $e): ?>
            <div><?=htmlspecialchars($e)?></div>
          <?php endforeach; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form method="post" class="mt-4">
        <h5 class="mb-3"><i class="bi bi-plus-circle me-2"></i>Add New Blood Sample</h5>
        
        <div class="row g-4">
          <div class="col-md-4">
            <label class="form-label fw-medium">Blood Group <span class="text-danger">*</span></label>
            <div class="blood-group-selector">
              <?php 
              $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
              foreach($bloodGroups as $group): 
                $isChecked = ($_POST['blood_group'] ?? '') === $group ? 'checked' : '';
              ?>
                <div class="blood-group-option">
                  <input type="radio" id="bg_<?=$group?>" name="blood_group" value="<?=$group?>" required <?=$isChecked?>>
                  <label for="bg_<?=$group?>"><?=$group?></label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="form-text">Select the blood group</div>
          </div>
          
          <div class="col-md-3">
            <label for="units" class="form-label fw-medium">Units <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-transparent"><i class="bi bi-droplet"></i></span>
              <input type="number" class="form-control" id="units" name="units" value="<?=htmlspecialchars($_POST['units'] ?? '1')?>" min="1" required>
            </div>
            <div class="form-text">Number of units available</div>
          </div>
          
          <div class="col-md-5">
            <label for="notes" class="form-label fw-medium">Notes</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent"><i class="bi bi-card-text"></i></span>
              <input type="text" class="form-control" id="notes" name="notes" placeholder="e.g., Urgent, Cross-matched, Expires soon" value="<?=htmlspecialchars($_POST['notes'] ?? '')?>">
            </div>
            <div class="form-text">Add any additional information</div>
          </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
          <button type="submit" class="btn btn-primary btn-action">
            <i class="bi bi-plus-lg"></i> Add to Inventory
          </button>
        </div>
      </form>
    </div>

    <!-- Inventory Table -->
    <div class="inventory-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Your Current Inventory</h5>
        <div class="small text-muted">Last updated: <?=date('M j, Y H:i')?></div>
      </div>
      
      <?php
      $stmt = $pdo->prepare('SELECT * FROM blood_samples WHERE hospital_id = ? ORDER BY added_at DESC');
      $stmt->execute([$user['id']]);
      $samples = $stmt->fetchAll();
      ?>
      
      <?php if (!$samples): ?>
        <div class="text-center py-5">
          <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
          <p class="mt-3 text-muted">No blood samples added yet. Add your first sample above.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Blood Group</th>
                <th>Units</th>
                <th>Notes</th>
                <th>Added On</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($samples as $s): 
                $addedDate = new DateTime($s['added_at']);
                $now = new DateTime();
                $interval = $now->diff($addedDate);
                $hoursOld = $interval->h + ($interval->days * 24);
                
                // Determine status based on how old the entry is
                if ($hoursOld < 24) {
                  $statusClass = 'bg-success';
                  $statusText = 'New';
                } elseif ($hoursOld < 72) {
                  $statusClass = 'bg-primary';
                  $statusText = 'Recent';
                } else {
                  $statusClass = 'bg-secondary';
                  $statusText = 'Older';
                }
              ?>
                <tr>
                  <td class="fw-bold"><?=htmlspecialchars($s['blood_group'])?></td>
                  <td>
                    <span class="badge bg-primary rounded-pill" style="font-size: 0.9em;">
                      <?=htmlspecialchars($s['units'])?> units
                    </span>
                  </td>
                  <td><?=htmlspecialchars($s['notes'] ?: '—')?></td>
                  <td>
                    <div class="d-flex align-items-center">
                      <i class="bi bi-calendar3 me-2 text-muted"></i>
                      <span class="small"><?=date('M j, Y', strtotime($s['added_at']))?></span>
                    </div>
                    <div class="text-muted small"><?=date('h:i A', strtotime($s['added_at']))?></div>
                  </td>
                  <td>
                    <span class="badge <?=$statusClass?> status-badge"><?=$statusText?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      
      <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <div class="text-muted small">
          <i class="bi bi-info-circle me-1"></i> Total <?=count($samples)?> entries
        </div>
        <a href="hospital_view_requests.php" class="btn btn-outline-primary btn-action">
          <i class="bi bi-bell"></i> View All Requests
        </a>
      </div>
    </div>
    
    <footer class="text-center text-muted small mt-5 mb-4">
      <p class="mb-1">© <?=date('Y')?> BloodBank Management System</p>
      <p class="mb-0">Logged in as: <span class="text-primary"><?=htmlspecialchars($user['name'])?> (Hospital)</span></p>
    </footer>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Auto-focus the first input field
    document.addEventListener('DOMContentLoaded', function() {
      const firstInput = document.querySelector('input[type="text"], input[type="number"], select');
      if (firstInput) firstInput.focus();
      
      // Add animation to table rows
      const rows = document.querySelectorAll('tbody tr');
      rows.forEach((row, index) => {
        row.style.animation = `fadeIn 0.3s ease-out ${index * 0.05}s forwards`;
        row.style.opacity = '0';
      });
    });
    
    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>
