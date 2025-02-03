<?php

namespace App\Http\Controllers;
use App\Models\CommutingReport;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CommutingReportController; // Add this line


class ReportController extends Controller
{
    public function index()
    {
        // Get both types of reports
        $itineraryReports = Report::with('user')->get();
        $commutingReports = CommutingReport::with('user')->get();
        
        // Merge the collections
        $allReports = $itineraryReports->concat($commutingReports)
            ->sortByDesc('created_at');
        
        // Paginate the merged collection
        $page = request('page', 1);
        $perPage = 10;
        
        $reports = new \Illuminate\Pagination\LengthAwarePaginator(
            $allReports->forPage($page, $perPage),
            $allReports->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );
            
        return view('admin.reports.index', compact('reports'));
    }

    public function show($id)
    {
        // Try to find in both report types
        $report = Report::find($id) ?? CommutingReport::find($id);
        
        if (!$report) {
            abort(404);
        }

        return view('admin.reports.show', compact('report'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'destination_name' => 'required|string',
                'itinerary_id' => 'required|exists:saved_itineraries,id',
                'current_instructions' => 'required|string',
                'issue_type' => 'required|string',
                'description' => 'required|string|max:1000',
            ]);

            Report::create([
                'user_id' => Auth::id(),
                'saved_itinerary_id' => $validated['itinerary_id'],
                'destination_name' => $validated['destination_name'],
                'current_instructions' => $validated['current_instructions'],
                'issue_type' => $validated['issue_type'],
                'description' => $validated['description'],
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_review,resolved',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report->update($validated);

        if ($validated['status'] === 'resolved') {
            $report->resolved_at = now();
            $report->save();
        }

        return redirect()->back()->with('success', 'Report updated successfully');
    }
}