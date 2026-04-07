<?php
session_start();
include "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered!";
        } else {

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Default role = user
            $role = "user";

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong!";
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial;
        }

        body {
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:linear-gradient(135deg,#ff9a9e,#fad0c4);
        }

        .register-box {
            width:90%;
            max-width:400px;
            background:white;
            padding:30px;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,0.2);
        }

        h2 {
            text-align:center;
            margin-bottom:20px;
        }

        .input-group {
            margin-bottom:15px;
        }

        .input-group input {
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:6px;
        }

        button {
            width:100%;
            padding:12px;
            background:#ff4b2b;
            color:white;
            border:none;
            border-radius:6px;
            cursor:pointer;
        }

        button:hover {
            background:#e63a1f;
        }

        .msg {
            padding:10px;
            margin-bottom:15px;
            border-radius:5px;
            text-align:center;
        }

        .error {
            background:#ffdddd;
            color:#900;
        }

        .success {
            background:#ddffdd;
            color:#060;
        }

        .bottom-text {
            text-align:center;
            margin-top:15px;
        }
    </style>
</head>

<body>

<div class="register-box">

    <h2><i class="fa fa-user-plus"></i> Sign Up</h2>

    <?php if ($error): ?>
        <div class="msg error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-group">
            <input type="text" name="name" placeholder="Full Name" required>
        </div>

        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit">Register</button>

    </form>

    <div class="bottom-text">
        Already have an account? <a href="login.php">Login</a>
    </div>

</div>

</body>
</html>