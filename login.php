<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] == "admin") {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: recipes.php");
        }
        exit();

    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="footer.css">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial;
        }

        body{
            min-height:100vh;
            background:linear-gradient(135deg,#ff7e5f,#feb47b);
        }

        .container{
            padding:60px 10px;
        }

        .login-box{
            width:50%;
            margin:auto;
            background:rgba(255,255,255,0.15);
            backdrop-filter:blur(10px);
            border-radius:12px;
            padding:50px;
            box-shadow:0 10px 30px rgba(0,0,0,0.2);
            color:white;
        }

        .login-box h2{
            text-align:center;
            margin-bottom:30px;
        }

        .input-group{
            margin-bottom:20px;
        }

        .input-group label{
            display:block;
            margin-bottom:6px;
        }

        .input-group input{
            width:100%;
            padding:12px;
            border:none;
            border-radius:6px;
            outline:none;
        }

        .password-box{
            position:relative;
        }

        .password-box i{
            position:absolute;
            right:10px;
            top:40px;
            color:#444;
            cursor:pointer;
        }

        button{
            width:100%;
            padding:13px;
            border:none;
            border-radius:6px;
            background:#ff4b2b;
            color:white;
            font-size:16px;
            cursor:pointer;
            margin-top:10px;
        }

        button:hover{
            background:#ff3a1a;
        }

        .bottom-text{
            text-align:center;
            margin-top:20px;
        }

        .bottom-text a{
            color:white;
            font-weight:bold;
        }

        .error{
            background:#ffdddd;
            color:#900;
            padding:10px;
            border-radius:5px;
            margin-bottom:15px;
            text-align:center;
        }

        @media (max-width: 768px){
            .login-box{
                width:90%;
                padding:30px 20px;
            }
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <div class="login-box">

        <h2><i class="fa fa-lock"></i> Sign In</h2>

        <?php if ($error != ""): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group password-box">
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <i class="fa-solid fa-eye-slash" onclick="togglePassword(this)"></i>
            </div>

            <button type="submit">LOGIN</button>
        </form>

        <div class="bottom-text">
            Don't have an account? <a href="register.php">Sign up</a>
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
<?php include "footer.php"?>
</body>
</html>