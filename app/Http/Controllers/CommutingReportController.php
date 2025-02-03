<?php

namespace App\Http\Controllers;

use App\Models\CommutingReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommutingReportController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_location' => 'required|string',
                'end_location' => 'required|string',
                'current_instructions' => 'required|string',
                'issue_type' => 'required|string',
                'description' => 'required|string|max:1000',
            ]);

            CommutingReport::create([
                'user_id' => Auth::id(),
                'start_location' => $validated['start_location'],
                'end_location' => $validated['end_location'],
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

    public function update(Request $request, CommutingReport $report)
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