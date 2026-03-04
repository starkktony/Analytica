<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Faculty Approval</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>

    <style>
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
        }
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        .header {
            background: #009539;
            color: white;
            padding: 0 30px;
            font-size: 36px;
            font-weight: bold;
            height: 75px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .content { margin-left: 210px; }

        /* Filter Bar — matches faculty profile design */
        .filter-bar {
            font-family: 'Bricolage Grotesque', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 14px 30px;
            border-bottom: 1px solid #b0b5b0;
            height: 40px;
        }

        .page-title {
            font-size: 16px;
            font-weight: 700;
            color: #2d2d2d;
            margin-right: 10px;
        }

        /* "Filters:" pushed to the right */
        .filter-bar-label {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            margin-right: 5px;
            margin-left: auto;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #2d2d2d;
            white-space: nowrap;
        }

        .filter-group select {
            font-size: 12px;
            padding: 5px 28px 5px 12px;
            border-radius: 20px;
            border: 1px solid #8a8f8a;
            background-color: #f5f5f5;
            color: #2d2d2d;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%232d2d2d' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 8px;
            min-width: 90px;
            cursor: pointer;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #009539;
            background-color: white;
        }

        .clear-filters-btn {
            background: #009539;
            color: white;
            border: none;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 8px;
            text-decoration: none;
            display: inline-block;
        }
        .clear-filters-btn:hover { background: #00802e; color: white; }

        /* Header drill badge */
        .drill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.22);
            border: 1px solid rgba(255,255,255,0.45);
            border-radius: 20px;
            padding: 3px 14px 3px 10px;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }
        .drill-badge i { font-size: 13px; }

        /* Stats Cards - matching teaching-load design */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 28px 30px 18px 30px;
        }
        .stat-card {
            border-radius: 15px;
            padding: 20px 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100px;
        }
        .stat-card.green { background: #009539; }
        .stat-card.green .icon-box {
            background: white; width: 48px; height: 48px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 15px; left: 15px;
        }
        .stat-card.green .icon-box i { font-size: 20px; color: #009539; }
        .stat-card.green .stat-content { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; flex: 1; }
        .stat-card.green .stat-number { font-size: 44px; font-weight: 700; color: white; line-height: 1; }
        .stat-card.green .stat-label  { font-size: 12px; color: rgba(255,255,255,0.85); font-weight: 600; margin-top: 4px; }
        
        .stat-card.white { background: white; }
        .stat-card.white .icon-box {
            background: #009539; width: 48px; height: 48px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 15px; left: 15px;
        }
        .stat-card.white .icon-box i { font-size: 20px; color: white; }
        .stat-card.white .stat-content { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; flex: 1; }
        .stat-number { font-size: 44px; font-weight: 700; color: #1f1f1f; line-height: 1; }
        .stat-label  { font-size: 12px; color: #666; font-weight: 600; margin-top: 4px; }

        /* Charts */
        .charts-row {
            display: grid;
            gap: 20px;
            padding: 0 30px 20px 30px;
        }
        .charts-row.two-col { grid-template-columns: 1fr 1fr; }
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 24px 24px 14px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
        }
        .chart-title    { font-size: 14px; font-weight: 700; color: #1f1f1f; margin-bottom: 2px; }
        .chart-subtitle { font-size: 11px; color: #999; font-weight: 500; margin-bottom: 10px; }

        .empty-chart {
            width: 100%; height: 320px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #ccc; gap: 8px;
        }
        .empty-chart i    { font-size: 36px; }
        .empty-chart span { font-size: 13px; font-weight: 600; }

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin: 0 30px 30px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
        }
        
        .table-card .chart-title {
            font-size: 14px;
            font-weight: 700;
            color: #1f1f1f;
            margin-bottom: 15px;
        }

        .table th {
            font-size: 12px;
            font-weight: 600;
            vertical-align: middle;
        }
        
        .table td {
            font-size: 12px;
            vertical-align: middle;
        }
        
        .badge {
            font-size: 12px;
            padding: 5px 8px;
        }
        
        .progress {
            height: 20px;
            border-radius: 10px;
            margin: 0;
        }
        
        .progress-bar {
            font-size: 11px;
            font-weight: 600;
            line-height: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
        }
        .summary-card h6 {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        .summary-card h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .summary-card small {
            font-size: 11px;
            color: #999;
        }

        .section-divider {
            position: relative;
            margin: 30px 30px 20px 30px;
            border-top: 2px dashed #bbb;
        }
        .section-divider span {
            position: absolute;
            top: -10px;
            left: 20px;
            background: #e8ebe8;
            padding: 0 15px;
            font-size: 14px;
            font-weight: 700;
            color: #333;
        }

        .text-success { color: #009539 !important; }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">

        {{-- Page header --}}
        <div class="header">
            Faculty Approval Dashboard
            @if($filters['main_signatory'])
                <span class="drill-badge">
                    <i class="bi bi-person-badge"></i>
                    {{ getSignatoryName($filters['main_signatory']) }}
                </span>
            @endif
        </div>

        {{-- Filter Bar — same design as teaching-load --}}
        <form method="GET" action="{{ route('stzfaculty.approval') }}" id="filterForm">
            <div class="filter-bar">
                <div class="filter-bar-label">Filters:</div>

                {{-- Semester --}}
                <div class="filter-group">
                    <label>Semester:</label>
                    <select id="mainSemester" name="main_semester" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach($availableSemesters as $sem)
                            <option value="{{ $sem->sem_id }}" {{ $filters['main_semester'] == $sem->sem_id ? 'selected' : '' }}>
                                {{ $sem->semester }} {{ $sem->sy }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Main Signatory --}}
                <div class="filter-group">
                    <label>Signatory:</label>
                    <select id="mainSignatory" name="main_signatory" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="dh" {{ $filters['main_signatory'] == 'dh' ? 'selected' : '' }}>Dept Head</option>
                        <option value="dean" {{ $filters['main_signatory'] == 'dean' ? 'selected' : '' }}>Dean</option>
                        <option value="director" {{ $filters['main_signatory'] == 'director' ? 'selected' : '' }}>Director</option>
                        <option value="ds" {{ $filters['main_signatory'] == 'ds' ? 'selected' : '' }}>DS</option>
                        <option value="dot_uni" {{ $filters['main_signatory'] == 'dot_uni' ? 'selected' : '' }}>DOT UNI</option>
                        <option value="nstp" {{ $filters['main_signatory'] == 'nstp' ? 'selected' : '' }}>NSTP</option>
                        <option value="eteeap" {{ $filters['main_signatory'] == 'eteeap' ? 'selected' : '' }}>ETEEAP</option>
                        <option value="vpaa" {{ $filters['main_signatory'] == 'vpaa' ? 'selected' : '' }}>VPAA</option>
                    </select>
                </div>

                @if($filters['main_semester'] || $filters['main_signatory'])
                    <a href="{{ route('stzfaculty.approval') }}" class="clear-filters-btn">Clear Filters</a>
                @endif
            </div>
        </form>

        {{-- Stats Cards --}}
        <div class="stats-container">
            <div class="stat-card white">
                <div class="icon-box"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($totalDocuments) }}</div>
                    <div class="stat-label">Total Documents</div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="icon-box"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($fullyApproved) }}</div>
                    <div class="stat-label">Fully Approved</div>
                </div>
            </div>
            <div class="stat-card white">
                <div class="icon-box"><i class="bi bi-clock-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($pendingApproval) }}</div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
            <div class="stat-card white">
                <div class="icon-box"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($declined) }}</div>
                    <div class="stat-label">Declined/Rejected</div>
                </div>
            </div>
        </div>

        {{-- Row 1: Overall Status Donut + Signatory Bar Chart --}}
        <div class="charts-row two-col">
            <div class="chart-card">
                <div class="chart-title">
                    {{ $filters['main_signatory'] ? getSignatoryName($filters['main_signatory']) . ' — Approval Status' : 'Overall Approval Status' }}
                </div>
                <div class="chart-subtitle">Distribution of document approval status</div>
                <div id="overallStatusChart" style="height: 320px;"></div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    {{ $filters['main_signatory'] ? getSignatoryName($filters['main_signatory']) . ' — Status Breakdown' : 'Status by Signatory Type' }}
                </div>
                <div class="chart-subtitle">Approved / Pending / Declined counts per signatory</div>
                <div id="signatoryTypeChart" style="height: 320px;"></div>
            </div>
        </div>

        {{-- Timeline Section Divider --}}
        <div class="section-divider"><span>TIMELINE ANALYSIS</span></div>

        {{-- Timeline Chart with its own filter --}}
        <div style="padding: 0 30px 20px 30px;">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    {{ $filters['timeline_signatory'] ? getSignatoryName($filters['timeline_signatory']) . ' Document Trends' : 'Document Trends by Year' }}
                </div>
                
                {{-- Timeline Filter --}}
                <form method="GET" action="{{ route('stzfaculty.approval') }}" id="timelineForm">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                        <div class="filter-group">
                            <label>Filter by Signatory:</label>
                            <select id="timelineSignatory" name="timeline_signatory" onchange="this.form.submit()">
                                <option value="">All Signatories</option>
                                <option value="dh" {{ $filters['timeline_signatory'] == 'dh' ? 'selected' : '' }}>Dept Head</option>
                                <option value="dean" {{ $filters['timeline_signatory'] == 'dean' ? 'selected' : '' }}>Dean</option>
                                <option value="director" {{ $filters['timeline_signatory'] == 'director' ? 'selected' : '' }}>Director</option>
                                <option value="ds" {{ $filters['timeline_signatory'] == 'ds' ? 'selected' : '' }}>DS</option>
                                <option value="dot_uni" {{ $filters['timeline_signatory'] == 'dot_uni' ? 'selected' : '' }}>DOT UNI</option>
                                <option value="nstp" {{ $filters['timeline_signatory'] == 'nstp' ? 'selected' : '' }}>NSTP</option>
                                <option value="eteeap" {{ $filters['timeline_signatory'] == 'eteeap' ? 'selected' : '' }}>ETEEAP</option>
                                <option value="vpaa" {{ $filters['timeline_signatory'] == 'vpaa' ? 'selected' : '' }}>VPAA</option>
                            </select>
                        </div>
                        @if($filters['timeline_signatory'])
                            <a href="{{ route('stzfaculty.approval', array_merge(request()->query(), ['timeline_signatory' => ''])) }}" class="clear-filters-btn">Clear</a>
                        @endif
                    </div>
                </form>

                <div id="timelineStackedChart" style="height: 400px;"></div>
                
                <div class="mt-3 text-center small text-muted">
                    <i class="bi bi-info-circle"></i>
                    Stacked bars show document status composition (Approved, Pending, Declined) | 
                    <strong>Green line shows total documents</strong>
                </div>
            </div>
        </div>

        {{-- Signatory Performance Section Divider --}}
        <div class="section-divider"><span>SIGNATORY PERFORMANCE SUMMARY</span></div>

        {{-- Summary Cards Row --}}
        <div style="padding: 0 30px 20px 30px;">
            <div class="row g-3">
                @php
                    $avgRate = collect($signatoryRows)->avg('stats.rate');
                    $highestRate = collect($signatoryRows)->max('stats.rate');
                    $highestSignatory = collect($signatoryRows)->firstWhere('stats.rate', $highestRate)['label'] ?? 'N/A';
                    $totalPending = collect($signatoryRows)->sum('stats.pending');
                    $totalDeclined = collect($signatoryRows)->sum('stats.declined');
                    $totalOverall = collect($signatoryRows)->sum('stats.total');
                @endphp
                <div class="col-md-3">
                    <div class="summary-card">
                        <h6>Average Approval Rate</h6>
                        <h3 class="text-success">{{ number_format($avgRate, 1) }}%</h3>
                        <small>Across all signatories</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <h6>Highest Approval Rate</h6>
                        <h3 class="text-primary">{{ number_format($highestRate, 1) }}%</h3>
                        <small>{{ $highestSignatory }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <h6>Total Pending</h6>
                        <h3 class="text-warning">{{ number_format($totalPending) }}</h3>
                        <small>{{ $totalOverall > 0 ? number_format(($totalPending/$totalOverall)*100, 1) : 0 }}% of total</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <h6>Total Declined</h6>
                        <h3 class="text-danger">{{ number_format($totalDeclined) }}</h3>
                        <small>{{ $totalOverall > 0 ? number_format(($totalDeclined/$totalOverall)*100, 1) : 0 }}% of total</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Signatory Table --}}
        <div class="table-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart-steps me-2"></i>
                Detailed Signatory Performance Metrics
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2">Signatory</th>
                            <th colspan="3" class="text-center">Status Count</th>
                            <th rowspan="2" class="text-center">Total</th>
                            <th colspan="2" class="text-center">Performance Metrics</th>
                        </tr>
                        <tr>
                            <th class="text-center bg-success text-white">Approved</th>
                            <th class="text-center bg-warning">Pending</th>
                            <th class="text-center bg-danger text-white">Declined</th>
                            <th class="text-center">Approval Rate</th>
                            <th class="text-center">Completion %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($signatoryRows as $row)
                            @php
                                $completionRate = $row['stats']['total'] > 0 
                                    ? (($row['stats']['approved'] + $row['stats']['declined']) / $row['stats']['total'] * 100) 
                                    : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $row['label'] }}</strong></td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ number_format($row['stats']['approved']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ number_format($row['stats']['pending']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ number_format($row['stats']['declined']) }}</span>
                                </td>
                                <td class="text-center fw-bold">{{ number_format($row['stats']['total']) }}</td>
                                <td class="text-center" style="min-width: 150px;">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: {{ $row['stats']['rate'] }}%">
                                            {{ number_format($row['stats']['rate'], 1) }}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center" style="min-width: 150px;">
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: {{ $completionRate }}%">
                                            {{ number_format($completionRate, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        @php
                            $totalApproved = collect($signatoryRows)->sum('stats.approved');
                            $totalPending = collect($signatoryRows)->sum('stats.pending');
                            $totalDeclined = collect($signatoryRows)->sum('stats.declined');
                            $totalOverall = collect($signatoryRows)->sum('stats.total');
                            $avgApprovalRate = $totalOverall > 0 ? ($totalApproved / $totalOverall * 100) : 0;
                            $avgCompletionRate = $totalOverall > 0 ? (($totalApproved + $totalDeclined) / $totalOverall * 100) : 0;
                        @endphp
                        <tr>
                            <th>TOTAL / AVERAGE</th>
                            <th class="text-center">{{ number_format($totalApproved) }}</th>
                            <th class="text-center">{{ number_format($totalPending) }}</th>
                            <th class="text-center">{{ number_format($totalDeclined) }}</th>
                            <th class="text-center">{{ number_format($totalOverall) }}</th>
                            <th class="text-center">{{ number_format($avgApprovalRate, 1) }}%</th>
                            <th class="text-center">{{ number_format($avgCompletionRate, 1) }}%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-3 text-center small text-muted">
                <i class="bi bi-info-circle"></i>
                <span class="mx-2">Approval Rate = (Approved / Total) × 100</span>
                <span class="mx-2">|</span>
                <span class="mx-2">Completion Rate = (Approved + Declined) / Total × 100</span>
                <span class="mx-2">|</span>
                <span class="mx-2">Data as of {{ date('F j, Y') }}</span>
            </div>
        </div>

    </div><!-- /.content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass PHP data to JavaScript
        const timelineYears = @json($timelineYears);
        const overallStats = {
            totalDocuments: {{ $totalDocuments }},
            fullyApproved: {{ $fullyApproved }},
            pendingApproval: {{ $pendingApproval }},
            declined: {{ $declined }},
            overallApproved: {{ $overallApproved }},
            overallPending: {{ $overallPending }},
            overallDeclined: {{ $overallDeclined }}
        };
        
        const signatoryStats = {
            dh: @json($dhStats),
            dean: @json($deanStats),
            director: @json($directorStats),
            ds: @json($dsStats),
            dot_uni: @json($dotUniStats),
            nstp: @json($nstpStats),
            eteeap: @json($eteeapStats),
            vpaa: @json($vpaaStats)
        };
        
        const timeline = {
            years: @json($timelineYears),
            documentCounts: @json(array_values($yearlyDocumentCounts)),
            approvedCounts: @json(array_values($yearlyApprovedCounts)),
            declinedCounts: @json(array_values($yearlyDeclinedCounts)),
            pendingCounts: @json(array_values($yearlyPendingCounts))
        };
        
        const currentFilter = '{{ $filters['main_signatory'] }}';
        const timelineFilter = '{{ $filters['timeline_signatory'] }}';

        const FONT  = { family: "'Bricolage Grotesque', sans-serif", size: 12, color: '#444' };
        const GREEN = '#009539';
        const BLUE  = '#2c7be5';
        const YELLOW = '#ffc107';
        const RED = '#dc3545';
        const cfg   = { responsive: true, displayModeBar: false };
        const CHART_H = 320;

        function renderDonutChart() {
            let approved, pending, declined;
            const keys = ['dh','dean','director','ds','dot_uni','nstp','eteeap','vpaa'];

            if (currentFilter && keys.includes(currentFilter)) {
                approved = signatoryStats[currentFilter].approved;
                pending  = signatoryStats[currentFilter].pending;
                declined = signatoryStats[currentFilter].declined;
            } else {
                approved = overallStats.overallApproved;
                pending  = overallStats.overallPending;
                declined = overallStats.overallDeclined;
            }

            if (approved + pending + declined === 0) {
                document.getElementById('overallStatusChart').innerHTML = 
                    '<div class="empty-chart"><i class="bi bi-pie-chart"></i><span>No data available</span></div>';
                return;
            }

            Plotly.newPlot('overallStatusChart', [{
                type: 'pie',
                hole: 0.45,
                values: [approved, pending, declined],
                labels: ['Approved', 'Pending', 'Declined'],
                marker: { colors: [GREEN, YELLOW, RED] },
                textinfo: 'label+percent',
                textfont: { size: 12, color: 'white' },
                hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>%{percent}<extra></extra>'
            }], {
                font: FONT,
                paper_bgcolor: 'white',
                margin: { t: 10, r: 140, b: 10, l: 10 },
                legend: { orientation: 'v', x: 1.02, y: 0.5, xanchor: 'left', font: { size: 11 } },
                showlegend: true
            }, cfg);
        }

        function renderBarChart() {
            const allLabels = ['Dept Head','Dean','Director','DS','DOT UNI','NSTP','ETEEAP','VPAA'];
            const keys = ['dh','dean','director','ds','dot_uni','nstp','eteeap','vpaa'];

            const approved = keys.map(k => signatoryStats[k].approved);
            const pending = keys.map(k => signatoryStats[k].pending);
            const declined = keys.map(k => signatoryStats[k].declined);

            if (approved.reduce((a,b) => a + b, 0) + pending.reduce((a,b) => a + b, 0) + declined.reduce((a,b) => a + b, 0) === 0) {
                document.getElementById('signatoryTypeChart').innerHTML = 
                    '<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>';
                return;
            }

            let approvedColors, pendingColors, declinedColors;
            if (currentFilter && keys.includes(currentFilter)) {
                const idx = keys.indexOf(currentFilter);
                approvedColors = keys.map((_, i) => i === idx ? GREEN : 'rgba(0,149,57,0.2)');
                pendingColors = keys.map((_, i) => i === idx ? YELLOW : 'rgba(255,193,7,0.2)');
                declinedColors = keys.map((_, i) => i === idx ? RED : 'rgba(220,53,69,0.2)');
            } else {
                approvedColors = GREEN;
                pendingColors = YELLOW;
                declinedColors = RED;
            }

            Plotly.newPlot('signatoryTypeChart', [
                {
                    name: 'Approved',
                    type: 'bar',
                    x: allLabels,
                    y: approved,
                    marker: { color: approvedColors },
                    hovertemplate: '<b>%{x}</b><br>Approved: %{y}<extra></extra>'
                },
                {
                    name: 'Pending',
                    type: 'bar',
                    x: allLabels,
                    y: pending,
                    marker: { color: pendingColors },
                    hovertemplate: '<b>%{x}</b><br>Pending: %{y}<extra></extra>'
                },
                {
                    name: 'Declined',
                    type: 'bar',
                    x: allLabels,
                    y: declined,
                    marker: { color: declinedColors },
                    hovertemplate: '<b>%{x}</b><br>Declined: %{y}<extra></extra>'
                }
            ], {
                font: FONT,
                paper_bgcolor: 'white',
                plot_bgcolor: 'white',
                barmode: 'group',
                margin: { t: 30, b: 70, l: 50, r: 20 },
                showlegend: true,
                legend: { orientation: 'h', y: -0.2, x: 0.5, xanchor: 'center', font: { size: 11 } },
                xaxis: { tickangle: -30, tickfont: { size: 11 } },
                yaxis: { title: { text: 'Count', font: { size: 11 } }, gridcolor: '#efefef', zeroline: false }
            }, cfg);
        }

        function renderTimelineChart() {
            const years = timeline.years || timelineYears;
            const approved = timeline.approvedCounts || years.map(() => 0);
            const declined = timeline.declinedCounts || years.map(() => 0);
            const pending = years.map((_, i) => {
                return Math.max(0, (timeline.documentCounts[i] || 0) - approved[i] - declined[i]);
            });
            const totalLine = timeline.documentCounts || years.map(() => 0);

            if (totalLine.reduce((a,b) => a + b, 0) === 0) {
                document.getElementById('timelineStackedChart').innerHTML = 
                    '<div class="empty-chart"><i class="bi bi-graph-up"></i><span>No timeline data available</span></div>';
                return;
            }

            Plotly.newPlot('timelineStackedChart', [
                {
                    name: 'Approved',
                    type: 'bar',
                    x: years,
                    y: approved,
                    marker: { color: GREEN },
                    hovertemplate: '<b>%{x}</b><br>Approved: %{y}<extra></extra>'
                },
                {
                    name: 'Pending',
                    type: 'bar',
                    x: years,
                    y: pending,
                    marker: { color: YELLOW },
                    hovertemplate: '<b>%{x}</b><br>Pending: %{y}<extra></extra>'
                },
                {
                    name: 'Declined',
                    type: 'bar',
                    x: years,
                    y: declined,
                    marker: { color: RED },
                    hovertemplate: '<b>%{x}</b><br>Declined: %{y}<extra></extra>'
                },
                {
                    name: 'Total Documents',
                    type: 'scatter',
                    mode: 'lines+markers',
                    x: years,
                    y: totalLine,
                    line: { color: '#006400', width: 3 },
                    marker: { color: '#006400', size: 8 },
                    hovertemplate: '<b>%{x}</b><br>Total: %{y}<extra></extra>'
                }
            ], {
                font: FONT,
                paper_bgcolor: 'white',
                plot_bgcolor: '#fafafa',
                barmode: 'stack',
                margin: { t: 40, b: 60, l: 60, r: 40 },
                showlegend: true,
                legend: { orientation: 'h', y: -0.15, x: 0.5, xanchor: 'center', font: { size: 11 } },
                xaxis: { title: { text: 'Year', font: { size: 11 } }, tickmode: 'linear', dtick: 1, gridcolor: '#e0e0e0' },
                yaxis: { title: { text: 'Document Count', font: { size: 11 } }, gridcolor: '#e0e0e0', zeroline: false },
                hovermode: 'x'
            }, cfg);
        }

        // Render all charts
        renderDonutChart();
        renderBarChart();
        renderTimelineChart();
    </script>
</body>
</html>

@php
function getSignatoryName($value) {
    $names = [
        'dh' => 'Department Head',
        'dean' => 'Dean',
        'director' => 'Director',
        'ds' => 'DS',
        'dot_uni' => 'DOT UNI',
        'nstp' => 'NSTP',
        'eteeap' => 'ETEEAP',
        'vpaa' => 'VPAA'
    ];
    return $names[$value] ?? $value;
}
@endphp