<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f5f5f5;
        }
        .header {
            background: #009539;
            color: white;
            padding: 10px;
            font-size: 62px;
            font-weight: bold;
        }
        .dropdown-menu {
            background: #2a2a2a;
            border: none;
            border-radius: 0;
            margin: 0;
            width: 100%;
        }
        .dropdown-item {
            color: #cfcfcf;
            padding: 10px 20px 10px 40px;
        }
        .dropdown-item:hover {
            background: #0f8f3a;
            color: white;
        }
        .dropdown-toggle::after {
            float: right;
            margin-top: 8px;
        }
        .sidebar-link {
            color: #cfcfcf;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }
        .sidebar-link:hover {
            background: #0f8f3a;
            color: white;
        }
        .submenu-link {
            display: block;
            color: #cfcfcf;
            text-decoration: none;
            position: relative;
            padding: 20px;
        }
        
        .sidebar-link[aria-expanded="true"] {
            background: #0f8f3a;
            color: white;
        }

        /* Container Boxes Styling */
        .container-boxes {
            padding: 15px;
            height: calc(100vh - 82px);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .box-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            flex: 0 0 auto;
        }
        .box-row.large {
            grid-template-columns: 1fr;
            flex: 1;
        }
        .box-row.double {
            grid-template-columns: repeat(2, 1fr);
            flex: 1;
        }
        .box-row:first-child {
            height: 180px;
        }
        .box {
            background: #d9d9d9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 1200px) {
            .box-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .box-row {
                grid-template-columns: 1fr;
            }
            .content {
                margin-left: 0;
            }
            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">
        <div class="header">Dashboard Overview</div>
        
        <div class="container-boxes">
            <!-- First Row: 4 boxes -->
            <div class="box-row">
                <div class="box"></div>
                <div class="box"></div>
                <div class="box"></div>
                <div class="box"></div>
            </div>

            <!-- Second Row: 1 large box -->
            <div class="box-row large">
                <div class="box large"></div>
            </div>

            <!-- Third Row: 2 boxes -->
            <div class="box-row double">
                <div class="box large"></div>
                <div class="box large"></div>
            </div>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>