<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = FormSubmission::with(['form', 'user:id,name,email', 'reviewer']);

        // Filter by form if specified
        if ($request->has('form_id')) {
            $form = Form::findOrFail($request->form_id);
            
            // Check permissions: Admin, Form Owner, or Form Submitter
            if (!$user->isAdmin() && $form->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this form submissions',
                ], 403);
            }
            
            $query->where('form_id', $request->form_id);
            
            // If not admin and not form owner, show only user's submissions
            if (!$user->isAdmin() && $form->user_id !== $user->id) {
                $query->where('user_id', $user->id);
            }
        } else {
            // If no form specified, apply user-based filtering
            if (!$user->isAdmin()) {
                // Show submissions where user is either:
                // 1. Form owner OR
                // 2. Submission creator
                $query->where(function ($q) use ($user) {
                    $q->whereHas('form', function ($formQuery) use ($user) {
                        $formQuery->where('user_id', $user->id);
                    })->orWhere('user_id', $user->id);
                });
            }
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('submitted_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('submitted_at', '<=', $request->to_date);
        }

        // Search in submission data (optional)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereJsonContains('data', $search)
                  ->orWhereHas('form', function ($formQuery) use ($search) {
                      $formQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('title_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $submissions = $query->orderBy('submitted_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $submissions,
            'meta' => [
                'is_admin' => $user->isAdmin(),
                'user_id' => $user->id,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'form_id' => 'required|exists:forms,id',
            'data' => 'required|array',
            'metadata' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $form = Form::findOrFail($request->form_id);

        // Check if form allows submissions
        if ($form->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Form is not available for submissions',
            ], 400);
        }

        // Check if anonymous submissions are allowed
        if (!$form->allow_anonymous && !Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required for this form',
            ], 401);
        }

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'data' => $request->data,
            'metadata' => $request->metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::id(),  // Always save authenticated user's ID
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
            'data' => $submission->load(['form', 'user:id,name']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $submission = FormSubmission::with(['form', 'user:id,name', 'reviewer'])->findOrFail($id);

        // Check permissions
        $canView = Auth::user()->isAdmin() || 
                   $submission->form->user_id === Auth::id() || 
                   $submission->user_id === Auth::id();

        if (!$canView) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $submission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $submission = FormSubmission::findOrFail($id);

        // Check permissions - only form owner or admin can update
        $canUpdate = Auth::user()->isAdmin() || $submission->form->user_id === Auth::id();

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'in:submitted,reviewed,approved,rejected',
            'review_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('status') && $request->status !== 'submitted') {
            $submission->markAsReviewed(
                Auth::id(),
                $request->status,
                $request->review_notes
            );
        } else {
            $submission->update($validator->validated());
        }

        return response()->json([
            'success' => true,
            'message' => 'Submission updated successfully',
            'data' => $submission->load(['form', 'user:id,name', 'reviewer']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $submission = FormSubmission::findOrFail($id);

        // Check permissions - only form owner or admin can delete
        $canDelete = Auth::user()->isAdmin() || $submission->form->user_id === Auth::id();

        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $submission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Submission deleted successfully',
        ]);
    }

    /**
     * Submit a form (public endpoint for anonymous submissions).
     */
    public function submit(Request $request, string $formId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'metadata' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $form = Form::findOrFail($formId);

        // Check if form allows submissions
        if ($form->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Form is not available for submissions',
            ], 400);
        }

        // Check if form is public or allows anonymous submissions
        if (!$form->is_public && (!Auth::check() || !$form->allow_anonymous)) {
            return response()->json([
                'success' => false,
                'message' => 'Form is not publicly accessible',
            ], 403);
        }

        $submission = FormSubmission::create([
            'form_id' => $formId,
            'data' => $request->data,
            'metadata' => $request->metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::check() ? Auth::id() : null,  // Save logged-in user ID
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
            'data' => [
                'id' => $submission->id,
                'submitted_at' => $submission->submitted_at,
            ],
        ], 201);
    }
}
