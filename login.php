<?php
session_start();
include("_inc.configs.php");

// If already logged in, go to admin
if(isset($_SESSION['yuvisession']) && $_SESSION['yuvisession'] === true) {
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo $APP_CONFIG['APP_NAME']; ?> Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.11.0/sweetalert2.css" />
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --primary: #3b82f6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        html, body {
            height: 100% !important;
            width: 100% !important;
            margin: 0;
            padding: 0;
            overflow: hidden !important;
            background-color: var(--bg-color); 
            color: var(--text-main); 
        }

        .login-card {
            background: var(--card-bg);
            padding: 3rem 2.5rem;
            border-radius: 1.5rem;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
            border: 1px solid rgba(255,255,255,0.05);
            text-align: center;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }

        .logo {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.75rem;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            color: var(--text-main);
            outline: none;
            transition: all 0.3s;
        }

        input:focus {
            border-color: var(--primary);
            background: rgba(255,255,255,0.1);
        }

        .captcha-box {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .captcha-img-wrapper {
            background: #fff;
            border-radius: 0.75rem;
            overflow: hidden;
            height: 48px;
            flex: 1;
        }

        .captcha-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            cursor: pointer;
        }

        button {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.9rem;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
            filter: brightness(1.1);
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo"><?php echo $APP_CONFIG['APP_NAME']; ?></div>
        <div class="subtitle">Admin Control Panel</div>

        <form id="loginForm">
            <div class="form-group">
                <label>Admin Access PIN</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="pin" placeholder="Enter 4-digit PIN" maxlength="4" required>
                </div>
            </div>

            <button type="submit" id="loginBtn">
                <span>Login Securely</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.11.0/sweetalert2.all.js"></script>

    <script>
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const pin = $('#pin').val();
            const btn = $('#loginBtn');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifying...');

            $.ajax({
                url: 'api.php',
                type: 'POST',
                data: {
                    action: 'login',
                    pin: pin
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Access Granted',
                            text: 'Redirecting to Admin Panel...',
                            timer: 1500,
                            showConfirmButton: false,
                            background: '#1e293b',
                            color: '#f8fafc'
                        }).then(() => {
                            window.location.href = 'admin.php';
                        });
                    } else {
                        btn.prop('disabled', false).html('<span>Login Securely</span> <i class="fas fa-arrow-right"></i>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: response.message,
                            background: '#1e293b',
                            color: '#f8fafc'
                        });
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<span>Login Securely</span> <i class="fas fa-arrow-right"></i>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Unable to process login request.',
                        background: '#1e293b',
                        color: '#f8fafc'
                    });
                }
            });
        });
    </script>
</body>
</html>
