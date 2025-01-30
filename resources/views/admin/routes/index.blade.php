@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Manage Jeepney Routes</h1>
        <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Route
        </a>
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
                            <th>Route Name</th>
                            <th>Color</th>
                            <th>Description</th>
                            <th>Number of Stops</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routes as $route)
                        <tr>
                            <td>{{ $route->route_name }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $route->route_color }}">
                                    {{ $route->route_color }}
                                </span>
                            </td>
                            <td>{{ Str::limit($route->description, 50) }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $route->stops->count() }} stops
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.routes.stops', $route) }}" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-signpost"></i>
                                    </a>
                                    <a href="{{ route('admin.routes.edit', $route) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.routes.delete', $route) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this route? This will also delete all associated stops.')">
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
                {{ $routes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection