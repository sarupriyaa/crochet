<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "snugglestitch";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$popup_message = "";
$popup_class = "";

$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_admin) {

    $name = trim($_POST["visitor_name"]);
    $email = trim($_POST["visitor_email"]);
    $message = trim($_POST["visitor_message"]);

    if (!empty($name) && !empty($email) && !empty($message)) {

        $stmt = $mysqli->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $popup_message = "Message sent successfully!";
            $popup_class = "success-popup";
        } else {
            $popup_message = "Failed to save message.";
            $popup_class = "error-popup";
        }

        $stmt->close();

    } else {

        $popup_message = "Please fill all required fields.";
        $popup_class = "warning-popup";
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

<link rel="stylesheet" href="navbar.css">
<link rel="stylesheet" href="../search/search.css">
<link rel="stylesheet" href="contact.css?v=<?php echo time(); ?>">

<style>

.popup-alert{
    position:fixed;
    top:25px;
    right:25px;
    min-width:320px;
    max-width:420px;
    padding:18px 20px;
    border-radius:10px;
    color:white;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:0 6px 20px rgba(0,0,0,0.18);
    z-index:99999;
    animation:slideRight 0.4s ease;
    font-family:Arial,sans-serif;
}

.popup-left{
    display:flex;
    align-items:center;
    gap:12px;
}

.popup-icon{
    font-size:22px;
    font-weight:bold;
}

.popup-text{
    font-size:15px;
    font-weight:600;
}

.close-popup{
    background:none;
    border:none;
    color:white;
    font-size:22px;
    cursor:pointer;
    margin-left:15px;
}

.success-popup{
    background:#22c55e;
}

.error-popup{
    background:#ef4444;
}

.warning-popup{
    background:#f59e0b;
}

.admin-contact-text{
    background:#fff7ed;
    color:#9a3412;
    padding:32px;
    border-radius:18px;
    border:1px solid #fed7aa;
    font-size:18px;
    font-weight:600;
    line-height:1.7;
    text-align:center;
}

.admin-contact-text a{
    display:inline-block;
    margin-top:18px;
    background:#a8664b;
    color:white;
    padding:13px 22px;
    border-radius:30px;
    text-decoration:none;
}

</style>

</head>

<body>

<?php if (!empty($popup_message)): ?>

<div class="popup-alert <?php echo $popup_class; ?>" id="popupAlert">

    <div class="popup-left">

        <div class="popup-icon">

            <?php
            if ($popup_class == "success-popup") {
                echo "✓";
            } elseif ($popup_class == "error-popup") {
                echo "✕";
            } else {
                echo "!";
            }
            ?>

        </div>

        <div class="popup-text">
            <?php echo htmlspecialchars($popup_message); ?>
        </div>

    </div>

    <button class="close-popup" onclick="closePopup()">×</button>

</div>

<?php endif; ?>

<section class="contact-section">

    <img src="images/yarns.jpg" alt="Yarns">

    <div class="contact-container">

        <div class="contact-form">

            <?php if ($is_admin): ?>

                <div class="admin-contact-text">

                    You are logged in as admin.
                    <br>
                    Contact messages are only for customers and guests.
                    <br><br>

                    <a href="admin_contacts.php">
                        View Customer Messages
                    </a>

                </div>

            <?php else: ?>

                <h2>Send a message</h2>

                <form action="contact.php" method="POST">

                    <div class="input-row">

                        <input 
                            type="text" 
                            name="visitor_name" 
                            placeholder="Your name *" 
                            required
                        >

                        <input 
                            type="email" 
                            name="visitor_email" 
                            placeholder="Email *" 
                            required
                        >

                    </div>

                    <textarea 
                        name="visitor_message" 
                        placeholder="Message *" 
                        required
                    ></textarea>

                    <button type="submit">
                        SUBMIT
                    </button>

                </form>

            <?php endif; ?>

        </div>

        <div class="contact-info">

            <h4>Email</h4>
            <p class="email">info@daisyhook.com</p>

            <h4>Phone</h4>
            <p>+977 9838420139</p>

            <h3>HAVE A QUESTION? WE’RE HAPPY TO HELP.</h3>

            <p>
                Reach out to us using the form.
            </p>

            <h3>WANT TO COLLABORATE ON SOCIAL MEDIA?</h3>

            <p>
                Please email support@daisyhook.com
            </p>

        </div>

    </div>

</section>

<?php include "footer.php"; ?>

<script>

function closePopup(){

    const popup = document.getElementById("popupAlert");

    if(popup){
        popup.style.display = "none";
    }
}

setTimeout(function(){

    const popup = document.getElementById("popupAlert");

    if(popup){
        popup.style.display = "none";
    }

},4000);

</script>

</body>
</html>