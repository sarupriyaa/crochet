<?php
session_start();
include "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // ================= VALIDATION =================

    // Check empty fields
    if (empty($name) || empty($email) || empty($password)) {
        $error = "❌ All fields are required!";
    }

    // Name validation (only letters and spaces)
    elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
        $error = "❌ Name must contain only letters!";
    }

    // Email validation
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Invalid email format!";
    }

    // Password length validation
    elseif (strlen($password) < 8) {
        $error = "❌ Password must be at least 8 characters!";
    }

    // Terms validation
    elseif (!isset($_POST["terms"])) {
        $error = "❌ You must agree to Terms & Conditions!";
    }

    else {

        // Check existing email
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "❌ Email already registered!";

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = "user";

            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, role) 
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

            if ($stmt->execute()) {

                $success = "✅ Registration successful! You can now login.";

            } else {

                $error = "❌ Something went wrong!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="../search/search.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial;
        }

        body{
            min-height:100vh;
            background:linear-gradient(135deg,#ff9a9e,#fad0c4);
        }

        .page-wrapper{
            min-height:calc(100vh - 160px);
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px 20px;
        }

        .register-box{
            width:100%;
            max-width:400px;
            background:white;
            padding:30px;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,0.2);
        }

        h2{
            text-align:center;
            margin-bottom:20px;
        }

        .input-group{
            margin-bottom:15px;
        }

        .input-group input{
            width:100%;
            padding:12px;
            border:1px solid #ccc;
            border-radius:6px;
            outline:none;
        }

        .password-box{
            position:relative;
        }

        .password-box input{
            padding-right:45px;
        }

        .password-box i{
            position:absolute;
            right:14px;
            top:50%;
            transform:translateY(-50%);
            color:#444;
            cursor:pointer;
            font-size:18px;
            z-index:5;
        }

        .password-box i:hover{
            color:#ff4b2b;
        }

        .terms{
            font-size:14px;
            margin-bottom:15px;
        }

        button{
            width:100%;
            padding:12px;
            background:#ff4b2b;
            color:white;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:18px;
        }

        button:hover{
            background:#e63a1f;
        }

        .msg{
            padding:10px;
            margin-bottom:15px;
            border-radius:5px;
            text-align:center;
        }

        .error{
            background:#ffdddd;
            color:#900;
        }

        .success{
            background:#ddffdd;
            color:#060;
        }

        .bottom-text{
            text-align:center;
            margin-top:15px;
        }

    </style>
</head>

<body>

<?php include "navbar.php"; ?>

<div class="page-wrapper">

    <div class="register-box">

        <h2>
            <i class="fa fa-user-plus"></i> Sign Up
        </h2>

        <!-- ERROR MESSAGE -->
        <?php if ($error): ?>
            <div class="msg error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- SUCCESS MESSAGE -->
        <?php if ($success): ?>
            <div class="msg success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- NAME -->
            <div class="input-group">

                <input 
                    type="text"
                    name="name"
                    placeholder="Full Name"
                    required
                    pattern="[A-Za-z ]+"
                    title="Name should contain only letters">

            </div>

            <!-- EMAIL -->
            <div class="input-group">

                <input 
                    type="email"
                    name="email"
                    placeholder="Email"
                    required>

            </div>

            <!-- PASSWORD -->
            <div class="input-group password-box">

                <input 
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Password"
                    required
                    minlength="8"
                    title="Password must be at least 8 characters">

                <i class="fa-solid fa-eye-slash"
                   onclick="togglePassword(this)"></i>

            </div>

            <!-- TERMS -->
            <div class="terms">

                <label>
                    <input type="checkbox" name="terms">

                    I agree to
                    <a href="#">Terms & Conditions</a>

                </label>

            </div>

            <!-- BUTTON -->
            <button type="submit">
                Register
            </button>

        </form>

        <div class="bottom-text">
            Already have an account?
            <a href="login.php">Login</a>
        </div>

    </div>

</div>

<script>

function togglePassword(icon){

    const input = document.getElementById("password");

    if(input.type === "password"){

        input.type = "text";

        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");

    } else {

        input.type = "password";

        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
}

</script>

<?php include "footer.php"; ?>

</body>
</html>