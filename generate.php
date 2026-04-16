<?php
session_start();
require "db.php";
require "phpqrcode/qrlib.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$qrFolder = "qr_images/";
$message = "";

if (!file_exists($qrFolder)) {
    mkdir($qrFolder, 0777, true);
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("SELECT filename FROM qrcodes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && file_exists($result['filename'])) {
        unlink($result['filename']);
    }
    $stmt = $conn->prepare("DELETE FROM qrcodes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    header("Location: generate.php");
    exit();
}

// Handle Edit
if (isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $phone = $_POST['edit_phone'];
    $email = $_POST['edit_email'];
    $additional = $_POST['edit_additional'];

    $qrData = "Name: $name\nPhone: $phone\nEmail: $email\nAdditional: $additional";
    $filename = $qrFolder . "qr_" . time() . ".png";
    QRcode::png($qrData, $filename, QR_ECLEVEL_L, 5);

    $stmt = $conn->prepare("SELECT filename FROM qrcodes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();
    if ($old && file_exists($old['filename'])) {
        unlink($old['filename']);
    }

    $stmt = $conn->prepare("UPDATE qrcodes SET data=?, filename=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssii", $qrData, $filename, $edit_id, $user_id);
    $stmt->execute();
    header("Location: generate.php");
    exit();
}

// Handle QR Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $additional = $_POST['additional'];

    $qrData = "Name: $name\nPhone: $phone\nEmail: $email\nAdditional: $additional";
    $filename = $qrFolder . "qr_" . time() . ".png";
    QRcode::png($qrData, $filename, QR_ECLEVEL_L, 5);

    $stmt = $conn->prepare("INSERT INTO qrcodes (user_id, data, filename) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $qrData, $filename);
    $stmt->execute();
    $message = "QR Code generated successfully!";
}

// Fetch QR Codes
$stmt = $conn->prepare("SELECT * FROM qrcodes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>QuickQR | Generate QR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #00c6ff, #0072ff);
            color: #fff;
        }
        .card {
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 24px rgba(0,0,0,0.5);
        }
        .fade-in {
            animation: fadeInUp 0.8s ease-in-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome to QuickQR, <?= htmlspecialchars($user_name) ?>!</h2>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="text" name="name" class="form-control" placeholder="Name" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="phone" class="form-control" placeholder="Phone" required>
        </div>
        <div class="col-md-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="additional" class="form-control" placeholder="Additional Data">
        </div>
        <div class="col-md-12 text-end">
            <button name="generate" class="btn btn-warning px-4">Generate QR</button>
        </div>
    </form>

    <div class="mb-3 text-end">
        <button class="btn btn-light" onclick="toggleHistory()">History</button>
    </div>

    <div class="row" id="qrHistory">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4 fade-in">
                <div class="card p-3 text-center">
                    <img src="<?= htmlspecialchars($row['filename']) ?>" class="img-fluid mb-3">
                    <pre style="white-space: pre-wrap; color: #000; background: #f8f9fa; padding: 10px; border-radius: 5px;"><?= htmlspecialchars($row['data']) ?></pre>
                    <small class="text-muted">Generated: <?= $row['created_at'] ?></small>
                    <div class="mt-2">
                        <a href="<?= htmlspecialchars($row['filename']) ?>" download class="btn btn-sm btn-success">Download</a>
                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content text-dark">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit QR Code</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                $lines = explode("\n", $row['data']);
                                $vals = [];
                                foreach ($lines as $line) {
                                    [$key, $value] = explode(":", $line, 2);
                                    $vals[strtolower(trim($key))] = trim($value);
                                }
                                ?>
                                <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                                <input type="text" name="edit_name" class="form-control mb-2" value="<?= htmlspecialchars($vals['name'] ?? '') ?>" required>
                                <input type="text" name="edit_phone" class="form-control mb-2" value="<?= htmlspecialchars($vals['phone'] ?? '') ?>" required>
                                <input type="email" name="edit_email" class="form-control mb-2" value="<?= htmlspecialchars($vals['email'] ?? '') ?>" required>
                                <input type="text" name="edit_additional" class="form-control" value="<?= htmlspecialchars($vals['additional'] ?? '') ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function toggleHistory() {
    const section = document.getElementById("qrHistory");
    section.style.display = (section.style.display === "none") ? "flex" : "none";
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
