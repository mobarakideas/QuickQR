<?php include("db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QuickQR - QR Code Generator</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="text-center mb-4">QuickQR - QR Code Generator</h2>
  <form action="generate.php" method="POST" class="card p-4 shadow-sm">
    <div class="row g-3">
      <div class="col-md-4">
        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
      </div>
      <div class="col-md-4">
        <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
      </div>
      <div class="col-md-4">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="col-12">
        <textarea name="data" rows="3" class="form-control" placeholder="Enter text, link or info to encode" required></textarea>
      </div>
    </div>
    <div class="mt-4 text-center">
      <button type="submit" class="btn btn-success">Generate QR</button>
      <a href="history.php" class="btn btn-outline-secondary">View History</a>
    </div>
  </form>
</div>

</body>
</html>
