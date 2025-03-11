@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Admin Dashboard</h1>
        <button id="printDashboard" class="btn btn-secondary">
            <i class="fas fa-print mr-1"></i> Print Dashboard
        </button>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Destinations</h5>
                    <p class="card-text display-4">{{ $stats['destinations'] }}</p>
                    <a href="{{ route('admin.destinations.index') }}" class="btn btn-primary">
                        Manage Destinations
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Jeepney Routes</h5>
                    <p class="card-text display-4">{{ $stats['routes'] }}</p>
                    <a href="{{ route('admin.routes.index') }}" class="btn btn-primary">
                        Manage Routes
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Jeepney Stops</h5>
                    <p class="card-text display-4">{{ $stats['stops'] }}</p>
                    <a href="{{ route('admin.routes.index') }}" class="btn btn-primary">
                        View Routes & Stops
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text display-4">{{ $stats['users'] }}</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                        Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Visitor Age Distribution</h5>
            </div>
            <div class="card-body" style="min-height: 400px; position: relative;">
                <canvas id="ageChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Gender Distribution</h5>
            </div>
            <div class="card-body" style="min-height: 400px; position: relative;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>
</div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Destination Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Destination</th>
                                    <th>Total Visits</th>
                                    <th>Age Groups</th>
                                    <th>Gender Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($destinationStats as $stat)
                                    <tr>
                                        <td>{{ $stat['name'] }}</td>
                                        <td>{{ $stat['total_visits'] }}</td>
                                        <td>
                                            @foreach($stat['age_distribution'] as $group => $count)
                                                <div>{{ $group }}: {{ $count }}</div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($stat['gender_distribution'] as $gender => $count)
                                                <div>{{ ucfirst($gender) }}: {{ $count }}</div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden iframe for printing to prevent page freeze -->
<iframe id="printFrame" style="display:none;"></iframe>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ageCtx = document.getElementById('ageChart');
    const genderCtx = document.getElementById('genderChart');
    let ageChart, genderChart;
    
    if (!ageCtx || !genderCtx) {
        console.error('Could not find chart canvas elements');
        return;
    }

    // Process destination stats to get overall age and gender distributions
    const destinationStats = {!! json_encode($destinationStats) !!};
    
    // Aggregate age distribution data
    const ageDistribution = {};
    destinationStats.forEach(stat => {
        Object.entries(stat.age_distribution).forEach(([ageGroup, count]) => {
            ageDistribution[ageGroup] = (ageDistribution[ageGroup] || 0) + count;
        });
    });

    // Aggregate gender distribution data
    const genderDistribution = {};
    destinationStats.forEach(stat => {
        Object.entries(stat.gender_distribution).forEach(([gender, count]) => {
            genderDistribution[gender] = (genderDistribution[gender] || 0) + count;
        });
    });

    // Create Age Chart
    ageChart = new Chart(ageCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(ageDistribution),
            datasets: [{
                data: Object.values(ageDistribution),
                backgroundColor: Object.keys(ageDistribution).map((_, index) => 
                    `hsl(${index * 360 / Object.keys(ageDistribution).length}, 70%, 50%)`
                ),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20
                    }
                },
                title: {
                    display: true,
                    text: 'Age Distribution of Visitors',
                    padding: {
                        top: 10,
                        bottom: 30
                    }
                }
            }
        }
    });

    // Create Gender Chart
    genderChart = new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(genderDistribution).map(gender => 
                gender.charAt(0).toUpperCase() + gender.slice(1)
            ),
            datasets: [{
                data: Object.values(genderDistribution),
                backgroundColor: [
                    'hsl(200, 70%, 50%)',  // Blue for male
                    'hsl(340, 70%, 50%)',  // Pink for female
                    'hsl(150, 70%, 50%)'   // Green for other (if exists)
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20
                    }
                },
                title: {
                    display: true,
                    text: 'Gender Distribution of Visitors',
                    padding: {
                        top: 10,
                        bottom: 30
                    }
                }
            }
        }
    });

    // Improved print functionality that doesn't freeze the main page
    document.getElementById('printDashboard').addEventListener('click', function() {
        // Show a loading state on the button
        const printBtn = this;
        const originalBtnText = printBtn.innerHTML;
        printBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Preparing...';
        printBtn.disabled = true;
        
        // Use setTimeout to prevent UI blocking
        setTimeout(function() {
            try {
                // Get chart images
                const ageChartImage = ageChart.toBase64Image();
                const genderChartImage = genderChart.toBase64Image();
                
                // Get the print frame
                const printFrame = document.getElementById('printFrame');
                const frameDoc = printFrame.contentWindow.document;
                
                // Write the print content to the iframe
                frameDoc.open();
                frameDoc.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Dashboard Report</title>
                        <style>
                            body { 
                                font-family: Arial, sans-serif; 
                                padding: 20px;
                                max-width: 1000px;
                                margin: 0 auto;
                            }
                            .print-header { 
                                text-align: center; 
                                margin-bottom: 20px; 
                            }
                            .print-section { 
                                margin-bottom: 30px; 
                                page-break-inside: avoid; 
                            }
                            .print-title { 
                                font-size: 16px; 
                                font-weight: bold; 
                                margin-bottom: 10px; 
                                border-bottom: 1px solid #ddd; 
                                padding-bottom: 5px; 
                            }
                            .print-stats { 
                                display: flex; 
                                flex-wrap: wrap; 
                                justify-content: space-between; 
                                margin-bottom: 20px; 
                            }
                            .print-stat { 
                                width: 24%; 
                                padding: 10px; 
                                box-sizing: border-box; 
                                border: 1px solid #ddd; 
                                margin-bottom: 10px; 
                            }
                            .print-number { 
                                font-size: 24px; 
                                font-weight: bold; 
                                margin: 10px 0; 
                            }
                            .print-label { 
                                font-size: 14px; 
                            }
                            .print-charts {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 30px;
                            }
                            .print-chart {
                                width: 48%;
                                border: 1px solid #ddd;
                                padding: 15px;
                                box-sizing: border-box;
                            }
                            .print-chart-title {
                                text-align: center;
                                margin-bottom: 10px;
                                font-weight: bold;
                            }
                            .print-chart img {
                                width: 100%;
                                max-height: 350px;
                                object-fit: contain;
                            }
                            .print-table { 
                                width: 100%; 
                                border-collapse: collapse; 
                            }
                            .print-table th, .print-table td { 
                                border: 1px solid #ddd; 
                                padding: 8px; 
                                text-align: left; 
                            }
                            .print-table th { 
                                background-color: #f2f2f2; 
                            }
                            .print-footer { 
                                text-align: center; 
                                font-size: 12px; 
                                margin-top: 30px; 
                                padding-top: 10px;
                                border-top: 1px solid #ddd;
                            }
                            .print-date {
                                text-align: right;
                                font-size: 12px;
                                margin-bottom: 20px;
                            }
                            @media print {
                                @page {
                                    size: portrait;
                                    margin: 0.5cm;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-date">
                            ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}
                        </div>
                        
                        <div class="print-header">
                            <h1>Admin Dashboard Report</h1>
                            <p>Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
                        </div>
                        
                        <div class="print-section">
                            <div class="print-title">Summary Statistics</div>
                            <div class="print-stats">
                                <div class="print-stat">
                                    <div class="print-label">Destinations</div>
                                    <div class="print-number">${document.querySelector('.card-text.display-4').textContent}</div>
                                </div>
                                <div class="print-stat">
                                    <div class="print-label">Jeepney Routes</div>
                                    <div class="print-number">${document.querySelectorAll('.card-text.display-4')[1].textContent}</div>
                                </div>
                                <div class="print-stat">
                                    <div class="print-label">Jeepney Stops</div>
                                    <div class="print-number">${document.querySelectorAll('.card-text.display-4')[2].textContent}</div>
                                </div>
                                <div class="print-stat">
                                    <div class="print-label">Users</div>
                                    <div class="print-number">${document.querySelectorAll('.card-text.display-4')[3].textContent}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="print-section">
                            <div class="print-title">Visitor Demographics</div>
                            <div class="print-charts">
                                <div class="print-chart">
                                    <div class="print-chart-title">Age Distribution</div>
                                    <img src="${ageChartImage}" alt="Age Distribution Chart">
                                </div>
                                <div class="print-chart">
                                    <div class="print-chart-title">Gender Distribution</div>
                                    <img src="${genderChartImage}" alt="Gender Distribution Chart">
                                </div>
                            </div>
                        </div>
                        
                        <div class="print-section">
                            <div class="print-title">Destination Statistics</div>
                            ${document.querySelector('.table-responsive').innerHTML}
                        </div>
                        
                        <div class="print-footer">
                            © ${new Date().getFullYear()} Jeepney Routing System - Admin Dashboard Report
                        </div>
                    </body>
                    </html>
                `);
                frameDoc.close();
                
                // Reset button state
                printBtn.innerHTML = originalBtnText;
                printBtn.disabled = false;
                
                // Print the iframe after a short delay to ensure content is loaded
                setTimeout(function() {
                    try {
                        printFrame.contentWindow.focus();
                        printFrame.contentWindow.print();
                    } catch (e) {
                        console.error('Print error:', e);
                        alert('There was an error while printing. Please try again.');
                    }
                }, 500);
                
            } catch (e) {
                console.error('Print preparation error:', e);
                alert('There was an error preparing the print. Please try again.');
                
                // Reset button on error
                printBtn.innerHTML = originalBtnText;
                printBtn.disabled = false;
            }
        }, 100);
    });
});
</script>

@endpush
@endsection