@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Manage Stops for Route: {{ $route->route_name }}</h1>
        <div>
            <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Routes
            </a>
            <a href="{{ route('admin.routes.stops.create', $route) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Stop
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Stop Name</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stops as $stop)
                        <tr>
                            <td>{{ $stop->order_in_route }}</td>
                            <td>{{ $stop->stop_name }}</td>
                            <td>
                                <span class="text-muted">
                                    {{ number_format($stop->latitude, 6) }}, 
                                    {{ number_format($stop->longitude, 6) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.stops.edit', $stop) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.stops.delete', $stop) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this stop?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $stops->links() }}
            </div>
        </div>
    </div>
</div>
@endsection