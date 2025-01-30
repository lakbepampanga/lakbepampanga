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
</div>


@endsection