<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Form;
use Symfony\Component\HttpFoundation\Response;

class CheckFormOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Admin has access to everything
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Check if route has form_id parameter
        $formId = $request->route('id') ?? $request->route('formId');
        
        if ($formId) {
            $form = Form::find($formId);
            
            if (!$form) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form not found',
                ], 404);
            }

            // Check if user owns the form
            if ($form->user_id !== $user->id && !$form->is_public) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this form',
                ], 403);
            }
        }

        return $next($request);
    }
}
