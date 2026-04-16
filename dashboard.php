<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM qr_history WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your QR History | QuickQR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Welcome, <?= $_SESSION['user_name'] ?> 👋</h2>
    <a href="logout.php" class="btn btn-danger float-end">Logout</a>
    <h4 class="mt-4 mb-3">Your QR History</h4>

    <table class="table table-bordered">
        <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>QR Code</th>
            <th>Date</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['name'] ?></td>
                <td><?= $row['phone'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><img src="<?= $row['qr_filename'] ?>" width="80"></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
