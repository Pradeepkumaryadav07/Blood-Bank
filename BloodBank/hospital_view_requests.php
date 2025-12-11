<?php
require_once 'functions.php';
require_role('hospital');
$user = current_user();

$stmt = $pdo->prepare("
  SELECT r.*, s.blood_group, u.name as receiver_name, u.email as receiver_email, u.phone as receiver_phone
  FROM requests r
  JOIN blood_samples s ON r.sample_id = s.id
  JOIN users u ON r.receiver_id = u.id
  WHERE r.hospital_id = ?
  ORDER BY r.requested_at DESC
");
$stmt->execute([$user['id']]);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blood Requests - Hospital Dashboard</title>
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
    .request-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #1d3557;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .request-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }
    .request-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #eee;
    }
    .request-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #1d3557;
      margin: 0;
    }
    .request-meta {
      display: flex;
      gap: 1rem;
      margin-top: 0.5rem;
    }
    .request-meta-item {
      display: flex;
      align-items: center;
      color: #6c757d;
      font-size: 0.9rem;
    }
    .request-meta-item i {
      margin-right: 0.5rem;
      color: #457b9d;
    }
    .request-body {
      margin-bottom: 1.5rem;
    }
    .receiver-info {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .receiver-name {
      font-weight: 600;
      margin-bottom: 0.25rem;
      color: #1d3557;
    }
    .receiver-contact {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 0.5rem;
      font-size: 0.9rem;
    }
    .receiver-contact a {
      color: #6c757d;
      text-decoration: none;
      transition: color 0.2s;
    }
    .receiver-contact a:hover {
      color: #1d3557;
    }
    .status-badge {
      padding: 0.35em 0.75em;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: capitalize;
    }
    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }
    .status-approved {
      background-color: #d4edda;
      color: #155724;
    }
    .status-rejected {
      background-color: #f8d7da;
      color: #721c24;
    }
    .btn-action {
      padding: 0.5rem 1.25rem;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn-action i {
      font-size: 1rem;
    }
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .empty-state i {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 1.5rem;
    }
    .empty-state h4 {
      color: #6c757d;
      margin-bottom: 0.5rem;
    }
    .empty-state p {
      color: #adb5bd;
      max-width: 500px;
      margin: 0 auto 1.5rem;
    }
    .time-ago {
      color: #6c757d;
      font-size: 0.85rem;
      font-style: italic;
    }
  </style>
</head>
<body style="background-color: #f8f9fa; font-family: 'Poppins', sans-serif;">
  <!-- Header -->
  <div class="dashboard-header">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="h4 mb-0"><i class="bi bi-bell me-2"></i>Blood Requests</h2>
          <p class="mb-0 small opacity-75">Manage blood requests from recipients</p>
        </div>
        <div>
          <a href="hospital_add_blood.php" class="btn btn-outline-light btn-sm me-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Inventory
          </a>
          <a href="logout.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">
        <i class="bi bi-list-check me-2 text-primary"></i>
        Recent Requests
      </h4>
      <div class="text-muted small">
        Showing <?=count($rows)?> request<?=count($rows) !== 1 ? 's' : ''?>
      </div>
    </div>

    <?php if (!$rows): ?>
      <div class="empty-state">
        <i class="bi bi-inboxes"></i>
        <h4>No Requests Yet</h4>
        <p>You don't have any blood requests at the moment. Check back later or share your inventory to receive requests.</p>
        <a href="hospital_add_blood.php" class="btn btn-primary">
          <i class="bi bi-plus-circle me-1"></i> Add Blood Inventory
        </a>
      </div>
    <?php else: ?>
      <?php 
      // Function to format time difference
      function timeAgo($date) {
        $now = new DateTime();
        $diff = $now->diff(new DateTime($date));
        
        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
      }
      
      foreach($rows as $r): 
        // Determine status class
        $statusClass = '';
        if ($r['status'] === 'pending') $statusClass = 'status-pending';
        elseif ($r['status'] === 'approved') $statusClass = 'status-approved';
        else $statusClass = 'status-rejected';
        
        // Format requested time
        $requestedTime = new DateTime($r['requested_at']);
        $formattedTime = $requestedTime->format('M j, Y \a\t h:i A');
      ?>
        <div class="request-card">
          <div class="request-header">
            <div>
              <h3 class="request-title">
                Blood Group: <span class="text-danger"><?=htmlspecialchars($r['blood_group'])?></span>
              </h3>
              <div class="request-meta">
                <div class="request-meta-item">
                  <i class="bi bi-calendar3"></i>
                  <?=$formattedTime?>
                </div>
                <div class="request-meta-item">
                  <i class="bi bi-clock"></i>
                  <span class="time-ago"><?=timeAgo($r['requested_at'])?></span>
                </div>
                <span class="status-badge <?=$statusClass?>">
                  <i class="bi <?=$r['status'] === 'pending' ? 'bi-hourglass-split' : ($r['status'] === 'approved' ? 'bi-check-circle' : 'bi-x-circle')?>"></i>
                  <?=ucfirst(htmlspecialchars($r['status']))?>
                </span>
              </div>
            </div>
          </div>
          
          <div class="request-body">
            <div class="receiver-info">
              <div class="receiver-name">
                <i class="bi bi-person-fill me-2"></i>
                <?=htmlspecialchars($r['receiver_name'])?>
              </div>
              <div class="receiver-contact">
                <a href="mailto:<?=htmlspecialchars($r['receiver_email'])?>">
                  <i class="bi bi-envelope"></i> <?=htmlspecialchars($r['receiver_email'])?>
                </a>
                <a href="tel:<?=htmlspecialchars($r['receiver_phone'])?>">
                  <i class="bi bi-telephone"></i> <?=htmlspecialchars($r['receiver_phone'])?>
                </a>
              </div>
            </div>
            
            <?php if (!empty($r['notes'])): ?>
              <div class="alert alert-light">
                <i class="bi bi-chat-square-text me-2"></i>
                <strong>Note:</strong> <?=htmlspecialchars($r['notes'])?>
              </div>
            <?php endif; ?>
          </div>
          
          <div class="request-footer d-flex justify-content-end gap-2">
            <?php if ($r['status'] === 'pending'): ?>
              <form method="post" action="hospital_action_request.php" class="mb-0">
                <input type="hidden" name="request_id" value="<?=intval($r['id'])?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn btn-success btn-action">
                  <i class="bi bi-check-lg"></i> Approve
                </button>
              </form>
              <form method="post" action="hospital_action_request.php" class="mb-0">
                <input type="hidden" name="request_id" value="<?=intval($r['id'])?>">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn btn-outline-danger btn-action">
                  <i class="bi bi-x-lg"></i> Reject
                </button>
              </form>
            <?php else: ?>
              <div class="text-muted small d-flex align-items-center">
                <i class="bi bi-info-circle me-1"></i> This request has been <?=$r['status']?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    
    <footer class="text-center text-muted small mt-5 mb-4">
      <p class="mb-1">© <?=date('Y')?> BloodBank Management System</p>
      <p class="mb-0">Logged in as: <span class="text-primary"><?=htmlspecialchars($user['name'])?> (Hospital)</span></p>
    </footer>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Add animation to request cards
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.request-card');
      cards.forEach((card, index) => {
        card.style.animation = `fadeInUp 0.3s ease-out ${index * 0.05}s forwards`;
        card.style.opacity = '0';
      });
    });
    
    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>
