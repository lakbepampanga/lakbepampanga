@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Admin Dashboard</h1>
    
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ageCtx = document.getElementById('ageChart');
    const genderCtx = document.getElementById('genderChart');
    
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
    new Chart(ageCtx, {
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
    new Chart(genderCtx, {
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
});
</script>

@endpush
@endsection
