<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormComponent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->get('locale', 'en');
        
        if ($request->has('grouped') && $request->grouped) {
            // Return components grouped by category
            $components = FormComponent::getByCategory($locale);
            
            return response()->json([
                'success' => true,
                'data' => $components,
            ]);
        }

        $query = FormComponent::active()->ordered();

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Search by label
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                  ->orWhere('label_ar', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $components = $query->get()->map(function ($component) use ($locale) {
            return [
                'id' => $component->id,
                'type' => $component->type,
                'label' => $component->getLocalizedLabel($locale),
                'description' => $component->getLocalizedDescription($locale),
                'key' => $component->key,
                'properties' => $component->properties,
                'validation' => $component->validation,
                'conditional' => $component->conditional,
                'category' => $component->category,
                'icon' => $component->icon,
                'is_custom' => $component->is_custom,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $components,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Only admin can create components
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'label_ar' => 'nullable|string|max:255',
            'key' => 'required|string|max:255|unique:form_components,key',
            'properties' => 'required|array',
            'validation' => 'nullable|array',
            'conditional' => 'nullable|array',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category' => 'required|in:basic,advanced,layout',
            'sort_order' => 'integer|min:0',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $component = FormComponent::create([
            ...$validator->validated(),
            'is_custom' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Component created successfully',
            'data' => $component,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $component = FormComponent::findOrFail($id);
        $locale = $request->get('locale', 'en');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $component->id,
                'type' => $component->type,
                'label' => $component->getLocalizedLabel($locale),
                'description' => $component->getLocalizedDescription($locale),
                'key' => $component->key,
                'properties' => $component->properties,
                'validation' => $component->validation,
                'conditional' => $component->conditional,
                'category' => $component->category,
                'sort_order' => $component->sort_order,
                'icon' => $component->icon,
                'is_active' => $component->is_active,
                'is_custom' => $component->is_custom,
                'created_at' => $component->created_at,
                'updated_at' => $component->updated_at,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Only admin can update components
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $component = FormComponent::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|string|max:255',
            'label' => 'sometimes|required|string|max:255',
            'label_ar' => 'nullable|string|max:255',
            'key' => 'sometimes|required|string|max:255|unique:form_components,key,' . $id,
            'properties' => 'sometimes|required|array',
            'validation' => 'nullable|array',
            'conditional' => 'nullable|array',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category' => 'sometimes|required|in:basic,advanced,layout',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $component->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Component updated successfully',
            'data' => $component,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        // Only admin can delete components
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $component = FormComponent::findOrFail($id);

        // Don't allow deletion of system components
        if (!$component->is_custom) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system components',
            ], 400);
        }

        $component->delete();

        return response()->json([
            'success' => true,
            'message' => 'Component deleted successfully',
        ]);
    }
}
