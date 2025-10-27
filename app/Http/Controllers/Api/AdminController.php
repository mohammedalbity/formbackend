<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        // Check if user is admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Admin only.',
            ], 403);
        }

        // Get date range from request
        $fromDate = $request->input('from_date', now()->subMonth());
        $toDate = $request->input('to_date', now());

        $stats = [
            // Total counts
            'total_users' => User::count(),
            'total_forms' => Form::count(),
            'total_submissions' => FormSubmission::count(),
            
            // Status breakdown
            'forms_by_status' => Form::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            
            'submissions_by_status' => FormSubmission::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            
            // Recent activity
            'recent_forms' => Form::with('user:id,name,email')
                ->latest()
                ->take(5)
                ->get(),
            
            'recent_submissions' => FormSubmission::with(['form:id,title', 'user:id,name,email'])
                ->latest('submitted_at')
                ->take(10)
                ->get(),
            
            // User statistics
            'active_users' => User::where('is_active', true)->count(),
            'users_by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get(),
            
            // Time-based statistics
            'forms_created_in_period' => Form::whereBetween('created_at', [$fromDate, $toDate])->count(),
            'submissions_in_period' => FormSubmission::whereBetween('submitted_at', [$fromDate, $toDate])->count(),
            
            // Popular forms (by submission count)
            'popular_forms' => Form::select('forms.*', DB::raw('count(form_submissions.id) as submission_count'))
                ->leftJoin('form_submissions', 'forms.id', '=', 'form_submissions.form_id')
                ->groupBy('forms.id')
                ->orderByDesc('submission_count')
                ->take(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
        ]);
    }

    /**
     * Get all users (admin only).
     */
    public function users(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Admin only.',
            ], 403);
        }

        $query = User::query();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount([
                'forms',                    // Forms created by user
                'formSubmissions as submissions_count'  // All submissions on user's forms
            ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get all forms (admin only).
     */
    public function allForms(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Admin only.',
            ], 403);
        }

        $query = Form::with('user:id,name,email')->withCount('submissions');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%");
            });
        }

        $forms = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $forms,
        ]);
    }

    /**
     * Get all submissions (admin only).
     */
    public function allSubmissions(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Admin only.',
            ], 403);
        }

        $query = FormSubmission::with(['form:id,title,title_ar', 'user:id,name,email', 'reviewer:id,name']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by form
        if ($request->has('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('submitted_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('submitted_at', '<=', $request->to_date);
        }

        $submissions = $query->latest('submitted_at')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $submissions,
        ]);
    }

    /**
     * Update user role or status (admin only).
     */
    public function updateUser(Request $request, string $id): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Admin only.',
            ], 403);
        }

        $user = User::findOrFail($id);

        // Prevent admin from modifying their own role/status
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify your own account',
            ], 400);
        }

        $validated = $request->validate([
            'role' => 'sometimes|in:admin,user',
            'is_active' => 'sometimes|boolean',
        ]);

        // Log the update attempt
        \Log::info('Admin updating user', [
            'admin_id' => Auth::id(),
            'user_id' => $user->id,
            'updates' => $validated
        ]);

        $user->update($validated);

        // Refresh the user to get the latest data
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Reset user password (admin only).
     */
    public function resetUserPassword(Request $request, string $id): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Admin only.',
            ], 403);
        }

        $user = User::findOrFail($id);

        // Prevent admin from resetting their own password this way
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Use profile settings to change your own password',
            ], 400);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => \Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }
}
