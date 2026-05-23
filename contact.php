<?php 
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "snugglestitch";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$alert_message = "";

// Handle form submission logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['visitor_name']);
    $email = trim($_POST['visitor_email']);
    $message = trim($_POST['visitor_message']);

    if (!empty($name) && !empty($email) && !empty($message)) {
        // Safe prepared statement insertion matching your SQL schema
        $stmt = $mysqli->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        
        if ($stmt->execute()) {
            $alert_message = "<div style='padding:12px; margin-bottom:15px; background:#e6f4ea; color:#137333; border-radius:6px; font-weight:bold; font-size:14px;'>🎉 Message sent successfully!</div>";
        } else {
            $alert_message = "<div style='padding:12px; margin-bottom:15px; background:#fce8e6; color:#c5221f; border-radius:6px; font-weight:bold; font-size:14px;'>Error: Failed to save message.</div>";
        }
        $stmt->close();
    } else {
        $alert_message = "<div style='padding:12px; margin-bottom:15px; background:#fef7e0; color:#b06000; border-radius:6px; font-weight:bold; font-size:14px;'>Warning: Please fill out all required fields.</div>";
    }
}
$mysqli->close();

include "navbar.php"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Contact Us</title>
<style>
img{
 width:100%;
 height:50vh;
 object-fit:cover;
 margin:auto;
 margin-bottom:50px;
}
</style>

<link rel="stylesheet" href="contact.css">
<link rel="stylesheet" href="navbar.css">
<link rel="stylesheet" href="../search/search.css">

</head>

<body>

<section class="contact-section">
     <img src="images/yarns.jpg" alt="">

<div class="contact-container">

<div class="contact-form">

<h2>Send a message</h2>

<?php echo $alert_message; ?>

<form action="contact.php" method="POST">

<div class="input-row">
<input type="text" name="visitor_name" placeholder="Your name *" required>
<input type="email" name="visitor_email" placeholder="Email *" required>
</div>

<textarea name="visitor_message" placeholder="Message *" required></textarea>

<button type="submit">SUBMIT</button>

</form>

</div>

<div class="contact-info">

<h4>Email</h4>
<p class="email">info@snugglestitch.com</p>

<h4>Phone</h4>
<p>+977 9838420139</p>

<h3>HAVE A QUESTION? WE’RE HAPPY TO HELP.</h3>

<p>Reach out to us using the form.</p>

<h3>WANT TO COLLABORATE ON SOCIAL MEDIA?</h3>

<p>Please email support@snugglestitch.com</p>

</div>

</div>

</section>

<?php include "footer.php"; ?>

</body>
</html>