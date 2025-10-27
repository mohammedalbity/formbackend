<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormAnalyticsController extends Controller
{
    /**
     * Get analytics for a specific form
     */
    public function getFormAnalytics(Request $request, string $formId): JsonResponse
    {
        $form = Form::findOrFail($formId);

        // Check if user has permission to view this form
        if (!Auth::user()->isAdmin() && $form->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        // Get all submissions for this form
        $submissions = FormSubmission::where('form_id', $formId)->get();
        $totalSubmissions = $submissions->count();

        // Basic statistics
        $basicStats = [
            'total_submissions' => $totalSubmissions,
            'status_breakdown' => FormSubmission::where('form_id', $formId)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'submissions_by_date' => FormSubmission::where('form_id', $formId)
                ->select(DB::raw('DATE(submitted_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->take(30)
                ->get(),
        ];

        // Field analysis
        $fieldAnalysis = $this->analyzeFormFields($form, $submissions);

        // Response time (average time between form creation and submission)
        $averageResponseTime = $this->calculateAverageResponseTime($submissions);

        return response()->json([
            'success' => true,
            'data' => [
                'form' => [
                    'id' => $form->id,
                    'title' => $form->title,
                    'title_ar' => $form->title_ar,
                    'status' => $form->status,
                    'created_at' => $form->created_at,
                ],
                'basic_stats' => $basicStats,
                'field_analysis' => $fieldAnalysis,
                'average_response_time' => $averageResponseTime,
                'recent_submissions' => FormSubmission::where('form_id', $formId)
                    ->with(['user:id,name,email', 'reviewer:id,name,email'])
                    ->latest('submitted_at')
                    ->take(10)
                    ->get(),
            ],
        ]);
    }

    /**
     * Analyze form fields and their responses
     */
    private function analyzeFormFields(Form $form, $submissions)
    {
        $fieldAnalysis = [];
        $formStructure = $form->structure;

        if (!$formStructure) {
            \Log::warning('Form structure is null', ['form_id' => $form->id]);
            return $fieldAnalysis;
        }

        // Handle different structure formats
        $components = [];
        if (is_array($formStructure)) {
            if (isset($formStructure['components'])) {
                $components = $formStructure['components'];
            } elseif (isset($formStructure['display']) && $formStructure['display'] === 'wizard' && isset($formStructure['pages'])) {
                // Wizard/Multi-page form
                foreach ($formStructure['pages'] as $page) {
                    if (isset($page['components'])) {
                        $components = array_merge($components, $page['components']);
                    }
                }
            } else {
                // Structure might be the components array directly
                $components = $formStructure;
            }
        }

        if (empty($components)) {
            \Log::warning('No components found in form structure', [
                'form_id' => $form->id,
                'structure_keys' => is_array($formStructure) ? array_keys($formStructure) : 'not_array'
            ]);
            return $fieldAnalysis;
        }

        // Extract all components from all pages/sections
        $allComponents = $this->extractAllComponents($components);

        foreach ($allComponents as $component) {
            $fieldKey = $component['key'] ?? null;
            $fieldType = $component['type'] ?? null;
            $fieldLabel = $component['label'] ?? $fieldKey;

            if (!$fieldKey) {
                continue;
            }

            $analysis = [
                'field_key' => $fieldKey,
                'field_label' => $fieldLabel,
                'field_type' => $fieldType,
                'total_responses' => 0,
                'empty_responses' => 0,
            ];

            // Analyze based on field type
            switch ($fieldType) {
                case 'radio':
                case 'select':
                case 'selectboxes':
                    $analysis['value_distribution'] = $this->analyzeChoiceField($submissions, $fieldKey);
                    break;

                case 'checkbox':
                    $analysis['yes_count'] = 0;
                    $analysis['no_count'] = 0;
                    foreach ($submissions as $submission) {
                        $value = $this->getFieldValue($submission->data, $fieldKey);
                        if ($value !== null) {
                            $analysis['total_responses']++;
                            if ($value === true || $value === 'true' || $value === 1) {
                                $analysis['yes_count']++;
                            } else {
                                $analysis['no_count']++;
                            }
                        } else {
                            $analysis['empty_responses']++;
                        }
                    }
                    break;

                case 'number':
                    $numbers = [];
                    foreach ($submissions as $submission) {
                        $value = $this->getFieldValue($submission->data, $fieldKey);
                        if ($value !== null && is_numeric($value)) {
                            $numbers[] = (float) $value;
                            $analysis['total_responses']++;
                        } else {
                            $analysis['empty_responses']++;
                        }
                    }
                    if (!empty($numbers)) {
                        $analysis['min'] = min($numbers);
                        $analysis['max'] = max($numbers);
                        $analysis['average'] = array_sum($numbers) / count($numbers);
                        $analysis['median'] = $this->calculateMedian($numbers);
                    }
                    break;

                case 'textfield':
                case 'textarea':
                case 'email':
                case 'phoneNumber':
                    $analysis['filled_count'] = 0;
                    $analysis['average_length'] = 0;
                    $totalLength = 0;
                    foreach ($submissions as $submission) {
                        $value = $this->getFieldValue($submission->data, $fieldKey);
                        if ($value !== null && $value !== '') {
                            $analysis['filled_count']++;
                            $totalLength += strlen($value);
                        } else {
                            $analysis['empty_responses']++;
                        }
                    }
                    if ($analysis['filled_count'] > 0) {
                        $analysis['average_length'] = round($totalLength / $analysis['filled_count'], 2);
                    }
                    break;

                default:
                    // For other field types, just count responses
                    foreach ($submissions as $submission) {
                        $value = $this->getFieldValue($submission->data, $fieldKey);
                        if ($value !== null && $value !== '') {
                            $analysis['total_responses']++;
                        } else {
                            $analysis['empty_responses']++;
                        }
                    }
            }

            $fieldAnalysis[] = $analysis;
        }

        return $fieldAnalysis;
    }

    /**
     * Extract all components from nested structure
     */
    private function extractAllComponents($components, &$allComponents = [])
    {
        if (!is_array($components)) {
            return $allComponents;
        }

        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            // Add component if it has a key (actual form field)
            if (isset($component['key']) && !empty($component['key'])) {
                $allComponents[] = $component;
            }

            // Check for nested components (panels, fieldsets, etc.)
            if (isset($component['components']) && is_array($component['components'])) {
                $this->extractAllComponents($component['components'], $allComponents);
            }

            // Check for columns layout
            if (isset($component['columns']) && is_array($component['columns'])) {
                foreach ($component['columns'] as $column) {
                    if (isset($column['components']) && is_array($column['components'])) {
                        $this->extractAllComponents($column['components'], $allComponents);
                    }
                }
            }

            // Check for wizard pages
            if (isset($component['pages']) && is_array($component['pages'])) {
                foreach ($component['pages'] as $page) {
                    if (isset($page['components']) && is_array($page['components'])) {
                        $this->extractAllComponents($page['components'], $allComponents);
                    }
                }
            }

            // Check for rows (in tables)
            if (isset($component['rows']) && is_array($component['rows'])) {
                foreach ($component['rows'] as $row) {
                    if (is_array($row)) {
                        $this->extractAllComponents($row, $allComponents);
                    }
                }
            }
        }

        return $allComponents;
    }

    /**
     * Analyze choice field (radio, select, selectboxes)
     */
    private function analyzeChoiceField($submissions, $fieldKey)
    {
        $distribution = [];

        foreach ($submissions as $submission) {
            $value = $this->getFieldValue($submission->data, $fieldKey);
            
            if ($value === null || $value === '') {
                continue;
            }

            // Handle array values (selectboxes)
            if (is_array($value)) {
                foreach ($value as $key => $selected) {
                    if ($selected) {
                        $distribution[$key] = ($distribution[$key] ?? 0) + 1;
                    }
                }
            } else {
                // Handle single value (radio, select)
                $valueStr = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
                $distribution[$valueStr] = ($distribution[$valueStr] ?? 0) + 1;
            }
        }

        // Sort by count descending
        arsort($distribution);

        return $distribution;
    }

    /**
     * Get field value from submission data
     */
    private function getFieldValue($data, $fieldKey)
    {
        if (!is_array($data)) {
            return null;
        }

        return $data[$fieldKey] ?? null;
    }

    /**
     * Calculate median of an array of numbers
     */
    private function calculateMedian($numbers)
    {
        sort($numbers);
        $count = count($numbers);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
        } else {
            return $numbers[$middle];
        }
    }

    /**
     * Calculate average response time
     */
    private function calculateAverageResponseTime($submissions)
    {
        if ($submissions->isEmpty()) {
            return null;
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($submissions as $submission) {
            if ($submission->submitted_at && $submission->created_at) {
                $diff = $submission->submitted_at->diffInMinutes($submission->created_at);
                $totalMinutes += $diff;
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        return round($totalMinutes / $count, 2);
    }
}
