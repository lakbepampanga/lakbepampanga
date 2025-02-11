@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Manage Destinations</h1>
        <a href="{{ route('admin.destinations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Destination
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Add Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.destinations.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="{{ route('admin.destinations.index') }}" class="btn btn-secondary">
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>City</th>
                            <th>Priority</th>
                            <th>Hours</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($destinations as $destination)
                        <tr>
                            <td>{{ $destination->name }}</td>
                            <td>
                                <span class="badge bg-{{ $destination->type === 'landmark' ? 'primary' : 'success' }}">
                                    {{ ucfirst($destination->type) }}
                                </span>
                            </td>
                            <td>{{ $destination->city }}</td>
                            <td>
                                <span class="badge bg-{{ $destination->priority === 1 ? 'danger' : 'warning' }}">
                                    Priority {{ $destination->priority }}
                                </span>
                            </td>
                            <td>
                                @if($destination->opening_time && $destination->closing_time)
                                    {{ \Carbon\Carbon::parse($destination->opening_time)->format('g:i A') }} - 
                                    {{ \Carbon\Carbon::parse($destination->closing_time)->format('g:i A') }}
                                @else
                                    Not specified
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">
                                    {{ number_format($destination->latitude, 4) }}, 
                                    {{ number_format($destination->longitude, 4) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.destinations.edit', $destination) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.destinations.delete', $destination) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this destination?')">
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
                {{ $destinations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection