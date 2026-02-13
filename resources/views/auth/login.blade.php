<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Analytica</title>

    <link href="https://fonts.googleapis.com/css2?family=Buttershine:wght@100;200;300;400;500;600;700&family=Inter:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --green-dark: #0b7a2d;     /* background green */
            --green-main: #009539;     /* button green */
            --green-light: #0095393D;    /* input background */
            --green-border: #4caf73;   /* input border */
            --text-dark: #000000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(180deg, #009539 0%, #002F12 100%);
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 20px;
        }

        .container {
            width: 100%;
            height: 700px;
            display: flex;
            border-radius: 40px;
            overflow: hidden;
        }

        /* ================================
           LEFT LOGIN PANEL
        ================================= */
        .login-panel {
            flex: 1;
            background: #ffffff;
            border-radius: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 80px;
            text-align: center;
            margin-right: 30px;
        }

        .brand {
            font-family: 'Buttershine', serif;
            font-weight: bold;
            font-size: 95px;
            color: var(--green-main);
            align-self: center;
            margin-bottom: 50px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: #000;
        }

        .input-group input {
            width: 120%;
            padding: 18px 20px 18px 50px;
            border-radius: 30px;
            border: 1.5px solid var(--green-border);
            background: var(--green-light);
            font-size: 16px;
            outline: none;
        }

        .forgot {
            font-size: 13px;
            margin: 5px 0 35px 10px;
            color: #000;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
            transition: text-decoration 0.2s ease;
        }

        .forgot:hover {
            text-decoration: underline;
        }

        .login-btn {
            display: inline-block; 
            width: 160px;
            padding: 14px;
            border: none;
            border-radius: 30px;
            background: var(--green-main);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            align-self: center;
        }

        /* ================================
           RIGHT LOGO PANEL
        ================================= */
        .logo-panel {
            flex: 1;
            position: relative;
        }
        .logo-img {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 650px;
            height: auto;
        }

        /* ================================
           RESPONSIVE
        ================================= */
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                height: auto;
            }

            .login-panel {
                border-radius: 40px 40px 0 0;
            }

            .logo-panel {
                border-radius: 0 0 40px 40px;
                padding: 60px 0;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="login-panel">
        <div class="brand">ANALYTICA</div>
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group" style="margin-bottom: 25px;">
                <i class="fa-solid fa-user"></i>
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    required
                    autofocus
                >
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >
            </div>

            @if ($errors->any())
                <div style="color: red; font-size: 13px; margin: 10px 0 20px 10px; text-align: left;">
                    {{ $errors->first() }}
                </div>
            @endif

            <div style="width: 100%; text-align: left; margin-bottom: 20px;">
                <a href="{{ route('password.request') }}" class="forgot">
                    Forgot Password?
                </a>
            </div>

            <div style="width: 100%; text-align: center;">
                <button type="submit" class="login-btn">LOGIN</button>
            </div>

        </form>

    </div>

    <div class="logo-panel">
            <img src="{{ asset('images/clsu-logo.png') }}" alt="CLSU Logo"class="logo-img">
    </div>

</div>

</body>
</html>
