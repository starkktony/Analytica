<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">

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

        /* Tab Navigation Styles */
        .tabs-container {
            background: white;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            padding: 0;
            margin: 0;
        }

        .tab-item {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 16px;
            font-weight: 600;
            padding: 16px 32px;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-item:hover {
            color: #009539;
        }

        .tab-item.active {
            color: #009539;
            border-bottom: 3px solid #009539;
        }
        
        .filter-bar {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 18px;
            background: #f3faf6;
            padding: 12px 20px;
            border-bottom: 1px solid #e0e0e0;
            flex-wrap: wrap;          /* allow wrapping */
            row-gap: 10px;
        }

        .filter-left i {
            font-size: 18px;
            color: #1f1f1f;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #009539;
            white-space: nowrap;
        }
        .filter-left {
            position: relative;
            padding-right: 16px;
            margin-right: 6px;
        }

        .filter-left::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 22px;
            background: #bdbdbd;
        }

        .filter-group select {
            font-size: 14px;
            padding: 4px 28px 4px 10px;
            border-radius: 8px;
            border: 1px solid #bdbdbd;
            background-color: #ffffff;
            color: #009539;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23009539' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #009539;
        }

        /* Stats and Charts Styles */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .stat-card .number {
            font-size: 48px;
            font-weight: bold;
            color: #009539;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 0 20px 20px 20px;
        }
        
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .chart-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .chart-placeholder {
            width: 100%;
            height: 300px;
            background: #f9f9f9;
            border: 1px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .student-level-content {
            display: none;
        }

        .student-level-content.active {
            display: block;
        }

        /* University Enrollment Styles */
        .university-header {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            margin: 20px;
            padding: 40px;
            border-radius: 30px;
            text-align: center;
            color: white;
        }

        .university-header h1 {
            font-size: 56px;
            font-weight: 700;
            margin: 0;
        }

        .university-chart {
            background: white;
            margin: 0 20px 20px 20px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .university-chart .chart-placeholder {
            height: 500px;
        }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">
        <div class="header">Enrollment Overview</div>

        <!-- Tab Navigation -->
        <div class="tabs-container">
            <button class="tab-item active" onclick="switchTab('enrollment')">Student Enrollment</button>
            <button class="tab-item" onclick="switchTab('demographics')">Student Demographics</button>
        </div>

        <div class="filter-bar">
            <div class="filter-left">
                <i class="bi bi-funnel-fill"></i>
            </div>

            <div class="filter-group">
                <label>Student Level:</label>
                <select id="studentLevel" onchange="filterStudentLevel()">
                    <option value="all">All</option>
                    <option value="university">University</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Semester:</label>
                <select>
                    <option>1st Sem, 2024 - 2025</option>
                </select>
            </div>

            <div class="filter-group">
                <label>College:</label>
                <select>
                    <option>All</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Program:</label>
                <select>
                    <option>All</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Year Level:</label>
                <select>
                    <option>All</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Region</label>
                <select>
                    <option>All</option>
                    <option>NCR</option>
                    <option>CAR</option>
                    <option>Region I</option>
                    <option>Region II</option>
                    <option>Region III</option>
                    <option>Region IV-A</option>
                    <option>Region IV-B</option>
                    <option>Region V</option>
                    <option>Region VI</option>
                    <option>Region VII</option>
                    <option>Region VIII</option>
                    <option>Region IX</option>
                    <option>Region X</option>
                    <option>Region XI</option>
                    <option>Region XII</option>
                    <option>Region XIII</option>
                    <option>BARMM</option>
                </select>
            </div>
            <div class="filter-group" id="placeGroup" style="display:none;">
                <label>Province</label>
                <select id="placeSelect">
                    <option>All</option>
                </select>
            </div>

        </div>

        <!-- Student Enrollment Tab Content -->
        <div id="enrollment-content" class="tab-content active">
            <!-- All Student Level View -->
            <div id="all-level" class="student-level-content active">
                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Total University Enrollees (1st Sem, 2025 - 2026)</h3>
                        <div class="number">16,196</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Total Graduate Enrollees (1st Sem, 2025 - 2026)</h3>
                        <div class="number">916</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Total Undergraduate Enrollees (1st Sem, 2025 - 2026)</h3>
                        <div class="number">15,280</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-container">
                    <div class="chart-card">
                        <h3>Enrollment Ranking of All Students by College (1st Sem, 2025 - 2026)</h3>
                        <div class="chart-placeholder">Horizontal Bar Chart</div>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Enrollment Trend of All Students per Semester by College (2020 - 2025)</h3>
                        <div class="chart-placeholder">Stacked Bar Chart</div>
                    </div>
                </div>
            </div>

            <!-- University Student Level View -->
            <div id="university-level" class="student-level-content">
                <div class="university-header">
                    <h1>University Enrollment</h1>
                </div>

                <div class="university-chart">
                    <div class="chart-placeholder">
                        Horizontal Bar Chart - University Enrollment by Region<br>
                        (Region 1: 862, Region 2: 298, Region 3: 13089, etc.)
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Demographics Tab Content -->
        <div id="demographics-content" class="tab-content">
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Demographics Content</h3>
                    <div class="number">-</div>
                </div>
            </div>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchTab(tabName) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab-item').forEach(tab => {
                tab.classList.remove('active');
            });

            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Add active class to clicked tab
            event.target.classList.add('active');

            // Show corresponding content
            document.getElementById(tabName + '-content').classList.add('active');
        }

        function filterStudentLevel() {
            const level = document.getElementById('studentLevel').value;
            
            // Hide all student level contents
            document.querySelectorAll('.student-level-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Show selected student level content
            document.getElementById(level + '-level').classList.add('active');
        }
// Complete Philippines Regions and Provinces/Cities Data
const regionPlaces = {
    "NCR": [
        "Caloocan",
        "Las Piñas",
        "Makati",
        "Malabon",
        "Mandaluyong",
        "Manila",
        "Marikina",
        "Muntinlupa",
        "Navotas",
        "Parañaque",
        "Pasay",
        "Pasig",
        "Quezon City",
        "San Juan",
        "Taguig",
        "Valenzuela"
    ],
    "CAR": [
        "Abra",
        "Apayao",
        "Benguet",
        "Ifugao",
        "Kalinga",
        "Mountain Province"
    ],
    "Region I": [
        "Ilocos Norte",
        "Ilocos Sur",
        "La Union",
        "Pangasinan"
    ],
    "Region II": [
        "Batanes",
        "Cagayan",
        "Isabela",
        "Nueva Vizcaya",
        "Quirino"
    ],
    "Region III": [
        "Aurora",
        "Bataan",
        "Bulacan",
        "Nueva Ecija",
        "Pampanga",
        "Tarlac",
        "Zambales"
    ],
    "Region IV-A": [
        "Batangas",
        "Cavite",
        "Laguna",
        "Quezon",
        "Rizal"
    ],
    "Region IV-B": [
        "Marinduque",
        "Occidental Mindoro",
        "Oriental Mindoro",
        "Palawan",
        "Romblon"
    ],
    "Region V": [
        "Albay",
        "Camarines Norte",
        "Camarines Sur",
        "Catanduanes",
        "Masbate",
        "Sorsogon"
    ],
    "Region VI": [
        "Aklan",
        "Antique",
        "Capiz",
        "Guimaras",
        "Iloilo",
        "Negros Occidental"
    ],
    "Region VII": [
        "Bohol",
        "Cebu",
        "Negros Oriental",
        "Siquijor"
    ],
    "Region VIII": [
        "Biliran",
        "Eastern Samar",
        "Leyte",
        "Northern Samar",
        "Samar",
        "Southern Leyte"
    ],
    "Region IX": [
        "Zamboanga del Norte",
        "Zamboanga del Sur",
        "Zamboanga Sibugay"
    ],
    "Region X": [
        "Bukidnon",
        "Camiguin",
        "Lanao del Norte",
        "Misamis Occidental",
        "Misamis Oriental"
    ],
    "Region XI": [
        "Davao de Oro",
        "Davao del Norte",
        "Davao del Sur",
        "Davao Occidental",
        "Davao Oriental"
    ],
    "Region XII": [
        "Cotabato",
        "Sarangani",
        "South Cotabato",
        "Sultan Kudarat"
    ],
    "Region XIII": [
        "Agusan del Norte",
        "Agusan del Sur",
        "Dinagat Islands",
        "Surigao del Norte",
        "Surigao del Sur"
    ],
    "BARMM": [
        "Basilan",
        "Lanao del Sur",
        "Maguindanao",
        "Sulu",
        "Tawi-Tawi"
    ]
};

// Update your existing JavaScript code with this:
document.addEventListener("DOMContentLoaded", function () {
    // Get the EXISTING region select (adjust the index if needed)
    const regionSelect = document.querySelectorAll(".filter-group select")[5];
    const placeGroup = document.getElementById("placeGroup");
    const placeSelect = document.getElementById("placeSelect");

    regionSelect.addEventListener("change", function () {
        const selectedRegion = this.value;

        // Reset places
        placeSelect.innerHTML = "<option>All</option>";

        if (selectedRegion === "All" || !regionPlaces[selectedRegion]) {
            placeGroup.style.display = "none";
            return;
        }

        // Show place dropdown
        placeGroup.style.display = "flex";

        // Populate places
        regionPlaces[selectedRegion].forEach(place => {
            const option = document.createElement("option");
            option.textContent = place;
            placeSelect.appendChild(option);
        });
    });
});
    </script>
</body>
</html>