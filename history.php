<?php include("db.php"); ?>
<!DOCTYPE html>
<html>
<head>
  <title>QR History</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="text-center mb-4">Generated QR History</h2>
  <a href="index.php" class="btn btn-outline-secondary mb-3">← Back</a>
  <div class="row">
    <?php
      $result = $conn->query("SELECT * FROM qr_history ORDER BY created_at DESC");
      while ($row = $result->fetch_assoc()) {
        echo "<div class='col-md-4 mb-4'>
                <div class='card p-3 shadow-sm'>
                  <img src='qrcodes/{$row['qr_filename']}' class='img-fluid'>
                  <div class='mt-2'>
                    <strong>{$row['name']}</strong><br>
                    <small>{$row['phone']} | {$row['email']}</small><br>
                    <small><i>{$row['created_at']}</i></small>
                    <a href='qrcodes/{$row['qr_filename']}' download class='btn btn-sm btn-outline-primary mt-2'>Download</a>
                  </div>
                </div>
              </div>";
      }
    ?>
  </div>
</div>

</body>
</html>
