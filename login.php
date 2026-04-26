<?php
require_once 'includes/db.php';
session_start();
$msg = "";

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if($row = mysqli_fetch_assoc($res)) {
        if(password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            $msg = "Sign in failed. Incorrect username or password.";
        }
    } else {
        $msg = "Sign in failed. Incorrect username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | School Event Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="logo.png">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #3b82f6;
            --accent: #dbeafe;
            --dark: #0f172a;
            --light: #ffffff;
            --glass: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(37, 99, 235, 0.1);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #f8fafc;
            margin: 0; 
            display: flex; 
            height: 100vh; 
            overflow: hidden;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
        }
        
        /* Subtle Light Background Animation */
        .bg-animate {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 20% 30%, rgba(37, 99, 235, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.05) 0%, transparent 40%);
            animation: moveBg 15s infinite alternate;
        }
        @keyframes moveBg {
            from { transform: scale(1); }
            to { transform: scale(1.05); }
        }

        .login-container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            animation: floatIn 0.8s ease-out;
        }
        @keyframes floatIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-form-area {
            flex: 1;
            padding: 60px;
            background: white;
            border-right: 1px solid var(--glass-border);
        }

        .info-area {
            flex: 1.2;
            padding: 60px;
            background: #f1f5f9;
            position: relative;
        }

        .logo-img { height: 40px; margin-bottom: 30px; }
        
        .title { font-weight: 800; font-size: 2.5rem; letter-spacing: -0.05em; margin-bottom: 15px; color: var(--dark); }
        .subtitle { color: var(--text-muted); margin-bottom: 40px; line-height: 1.6; }

        .form-label { font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 10px; display: block; }
        .form-control { 
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 20px;
            color: var(--text-main);
            transition: all 0.3s;
        }
        .form-control:focus { 
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-login {
            background: var(--primary);
            border: none;
            border-radius: 16px;
            padding: 18px;
            width: 100%;
            color: white;
            font-weight: 700;
            margin-top: 30px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -5px rgba(37, 99, 235, 0.3);
        }
        .btn-login:hover { background: var(--dark); transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(15, 23, 42, 0.2); }

        .badge-tech {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #dbeafe;
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .feature-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 15px;
            transition: all 0.3s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }
        .feature-card:hover { transform: translateX(8px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); border-color: var(--primary); }
        .feature-card i { color: var(--primary); font-size: 1.5rem; margin-right: 15px; }
        .feature-title { font-weight: 800; color: var(--dark); font-size: 1rem; }
        .feature-desc { font-size: 0.85rem; color: var(--text-muted); }

        @media (max-width: 900px) {
            .info-area { display: none; }
            .login-container { max-width: 450px; margin: 20px; }
            body { overflow-y: auto; height: auto; min-height: 100vh; padding: 40px 0; }
        }
        @media (max-width: 480px) {
            .login-form-area { padding: 40px 30px; }
            .title { font-size: 2rem; }
        }
    </style>
</head>
<body>

<div class="bg-animate"></div>

<div class="login-container">
    <div class="login-form-area">
        <img src="logo.png" alt="EventHub Logo" class="logo-img">
        <h1 class="title">Admin Login</h1>
        <p class="subtitle">Log in to manage your school event files.</p>

        <?php if($msg) echo "<div class='alert alert-danger mb-4 py-3 px-4 small border-0' style='background:rgba(239,68,68,0.1); color:#f87171; border-radius:12px;'>$msg</div>"; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control w-100" placeholder="Enter your username" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control w-100" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Log In Now</button>
        </form>
    </div>

    <div class="info-area">
        <div class="badge-tech">
            <i class="bi bi-cpu"></i> Smart Photo Check
        </div>
        <h2 class="mb-4 fw-bold" style="font-size: 2rem;">Easy Photo Management</h2>
        <p class="subtitle mb-5">Use SchoolEventHub to easily store and share high-quality photos from school events.</p>

        <div class="feature-card d-flex align-items-center">
            <i class="bi bi-check-all"></i>
            <div>
                <div class="feature-title">Photo Quality Check</div>
                <div class="feature-desc">Our system checks if photos are clear and good to use.</div>
            </div>
        </div>

        <div class="feature-card d-flex align-items-center">
            <i class="bi bi-qr-code"></i>
            <div>
                <div class="feature-title">Quick QR Codes</div>
                <div class="feature-desc">Create QR codes so parents can see photos instantly.</div>
            </div>
        </div>

        <div class="feature-card d-flex align-items-center">
            <i class="bi bi-fingerprint"></i>
            <div>
                <div class="feature-title">Photo Protection</div>
                <div class="feature-desc">Your photos are safe and tracked with unique IDs.</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
