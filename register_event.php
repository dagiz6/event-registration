<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_name = $conn->real_escape_string($_POST['event_name']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Insert event into database
    $sql = "INSERT INTO events (user_id, event_name, name, email, phone) VALUES ('$user_id', '$event_name', '$name', '$email', '$phone')";
    if ($conn->query($sql) === TRUE) {
        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'dagimt369@gmail.com'; 
            $mail->Password = 'aigusrprgcitqkbn'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('dagimt369@gmail.com', 'Event Registration');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Event Registration Confirmation';
            $mail->Body = "Dear $name,<br><br>Thank you for registering for the event: $event_name.<br><br>Best regards,<br>Event Team";

            $mail->send();
            echo "Event registration successful! A confirmation email has been sent.";
        } catch (Exception $e) {
            echo "Event registration successful, but email sending failed: " . $mail->ErrorInfo;
        }
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch user's registered events
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM events WHERE user_id='$user_id'";
$result = $conn->query($sql);
$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: 20px;">
        <div class="container">
            <a class="navbar-brand" href="#">Event Registration System</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['user_name']; ?>!</span>
                <a class="btn btn-danger" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 30px;">
        <div class="row">
            <div class="col-md-6">
                <h2>Register for an Event</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label for="event_name" class="form-label">Event Name:</label>
                        <input type="text" class="form-control" id="event_name" name="event_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number:</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
                </form>
            </div>

            <div class="col-md-6">
                <h2>Your Registered Events</h2>
                <?php if (empty($events)): ?>
                    <p>You have not registered for any events yet.</p>
                <?php else: ?>
                    <table class="table table-striped" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                    <td><?php echo htmlspecialchars($event['name']); ?></td>
                                    <td><?php echo htmlspecialchars($event['email']); ?></td>
                                    <td><?php echo htmlspecialchars($event['phone']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

