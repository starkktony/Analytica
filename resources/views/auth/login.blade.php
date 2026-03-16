<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SielMetrics+</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --green-main:   #009539;
            --green-dark:   #0b7a2d;
            --green-light:  #0095393D;
            --green-border: #4caf73;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(180deg, #009539 0%, #002F12 100%);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding-left: 8%;
            overflow: hidden;
            position: relative;
        }

        /* CLSU logo as background */
        .bg-logo {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            width: 700px;
            height: auto;
            pointer-events: none;
            user-select: none;
        }

        /* Floating login box */
        .login-box {
            position: relative;
            z-index: 10;
            background: #ffffff;
            border-radius: 32px;
            padding: 70px 80px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Brand */
        .brand {
            font-weight: 800;
            font-size: 52px;
            letter-spacing: -1px;
            line-height: 1;
            margin-bottom: 40px;
            display: inline-flex;
            align-items: center;
        }

        .brand .siel    { color: #111111; }
        .brand .metrics { color: #4ade80; }
        .brand .plus    { color: #4ade80; font-size: 62px; font-weight: 900; }

        /* Form */
        form { width: 100%; }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: #333;
            font-size: 15px;
        }

        .input-group input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border-radius: 30px;
            border: 1.5px solid var(--green-border);
            background: var(--green-light);
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-group input:focus {
            border-color: var(--green-main);
            box-shadow: 0 0 0 4px rgba(0, 149, 57, 0.15);
        }

        .forgot {
            font-size: 12px;
            color: #000;
            text-decoration: none;
            display: block;
            text-align: left;
            margin: 4px 0 24px 4px;
        }
        .forgot:hover { text-decoration: underline; }

        .login-btn {
            display: block;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 30px;
            background: var(--green-main);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }

        .login-btn:hover {
            background: var(--green-dark);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .login-box {
                max-width: 90%;
                padding: 36px 28px;
            }

            .brand { font-size: 34px; margin-bottom: 28px; }
            .brand .plus { font-size: 40px; }
            .bg-logo { width: 400px; right: -80px; opacity: 0.3; }
        }
    </style>
</head>
<body>

    <img src="{{ asset('images/clsu-logo.png') }}" alt="CLSU Logo" class="bg-logo">

    <div class="login-box">
        <div class="brand">
            <span class="siel">Siel</span><span class="metrics">Metrics</span><span class="plus">+</span>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="email" name="email" placeholder="Email" required autofocus>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            @if ($errors->any())
                <div style="color:red; font-size:13px; margin: -6px 0 14px 4px; text-align:left;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div style="width:100%; text-align:left;">
                <a href="{{ route('password.request') }}" class="forgot">Forgot Password?</a>
            </div>

            <div style="width:100%; text-align:center;">
                <button type="submit" class="login-btn">LOGIN</button>
            </div>

        </form>
    </div>

</body>
</html>