<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportingController extends Controller
{
    /**
     * Generate sales report.
     */
    public function salesReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date_range' => 'nullable|integer|min:1|max:365',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $reportingService = app(ReportingService::class);
            $report = $reportingService->generateSalesReport($request);

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Sales report generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating sales report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating sales report',
                'errors' => ['report' => ['An error occurred while generating the sales report.']]
            ], 500);
        }
    }

    /**
     * Generate delivery report.
     */
    public function deliveryReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date_range' => 'nullable|integer|min:1|max:365',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $reportingService = app(ReportingService::class);
            $report = $reportingService->generateDeliveryReport($request);

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Delivery report generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating delivery report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating delivery report',
                'errors' => ['report' => ['An error occurred while generating the delivery report.']]
            ], 500);
        }
    }

    /**
     * Generate customer report.
     */
    public function customerReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date_range' => 'nullable|integer|min:1|max:365',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $reportingService = app(ReportingService::class);
            $report = $reportingService->generateCustomerReport($request);

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Customer report generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating customer report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating customer report',
                'errors' => ['report' => ['An error occurred while generating the customer report.']]
            ], 500);
        }
    }

    /**
     * Export report to CSV.
     */
    public function exportReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'report_type' => 'required|in:sales,delivery,customer',
                'date_range' => 'nullable|integer|min:1|max:365',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $reportingService = app(ReportingService::class);
            
            // Generate report data
            $reportData = match ($request->report_type) {
                'sales' => $reportingService->generateSalesReport($request),
                'delivery' => $reportingService->generateDeliveryReport($request),
                'customer' => $reportingService->generateCustomerReport($request),
            };

            if (empty($reportData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data available for export',
                ], 404);
            }

            // Export to CSV
            $filepath = $reportingService->exportReportToCsv($reportData, $request->report_type);

            // Generate download URL
            $downloadUrl = Storage::url($filepath);

            return response()->json([
                'success' => true,
                'data' => [
                    'filepath' => $filepath,
                    'download_url' => $downloadUrl,
                    'filename' => basename($filepath),
                    'report_type' => $request->report_type,
                ],
                'message' => 'Report exported successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error exporting report',
                'errors' => ['export' => ['An error occurred while exporting the report.']]
            ], 500);
        }
    }

    /**
     * Download exported report.
     */
    public function downloadReport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            $request->validate([
                'filepath' => 'required|string',
            ]);

            $filepath = $request->filepath;

            if (!Storage::exists($filepath)) {
                abort(404, 'Report file not found');
            }

            return Storage::download($filepath);

        } catch (\Exception $e) {
            Log::error('Error downloading report: ' . $e->getMessage());
            abort(500, 'Error downloading report');
        }
    }

    /**
     * Generate scheduled report.
     */
    public function scheduledReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'report_type' => 'required|in:sales,delivery,customer',
                'frequency' => 'required|in:daily,weekly,monthly',
            ]);

            $reportingService = app(ReportingService::class);
            $report = $reportingService->generateScheduledReport(
                $request->report_type,
                $request->frequency
            );

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Scheduled report generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating scheduled report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating scheduled report',
                'errors' => ['report' => ['An error occurred while generating the scheduled report.']]
            ], 500);
        }
    }

    /**
     * Get available report types.
     */
    public function getReportTypes(): JsonResponse
    {
        try {
            $reportTypes = [
                [
                    'type' => 'sales',
                    'name' => 'Sales Report',
                    'description' => 'Comprehensive sales analysis including revenue, orders, and top-selling books',
                    'available_frequencies' => ['daily', 'weekly', 'monthly'],
                ],
                [
                    'type' => 'delivery',
                    'name' => 'Delivery Report',
                    'description' => 'Delivery performance analysis including success rates and download statistics',
                    'available_frequencies' => ['daily', 'weekly', 'monthly'],
                ],
                [
                    'type' => 'customer',
                    'name' => 'Customer Report',
                    'description' => 'Customer behavior analysis including spending patterns and retention rates',
                    'available_frequencies' => ['weekly', 'monthly'],
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $reportTypes,
                'message' => 'Report types retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving report types: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving report types',
                'errors' => ['types' => ['An error occurred while retrieving report types.']]
            ], 500);
        }
    }

    /**
     * Get report generation history.
     */
    public function getReportHistory(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'report_type' => 'nullable|in:sales,delivery,customer',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            // Get list of generated report files
            $files = Storage::files('reports');
            
            $reports = collect($files)
                ->map(function ($file) {
                    $filename = basename($file);
                    $parts = explode('_', $filename);
                    
                    if (count($parts) >= 3) {
                        $reportType = $parts[0];
                        $date = $parts[2] ?? '';
                        $time = $parts[3] ?? '';
                        
                        return [
                            'filename' => $filename,
                            'filepath' => $file,
                            'report_type' => $reportType,
                            'generated_at' => $date . ' ' . str_replace('.csv', '', $time),
                            'size' => Storage::size($file),
                            'download_url' => Storage::url($file),
                        ];
                    }
                    
                    return null;
                })
                ->filter()
                ->sortByDesc('generated_at');

            // Filter by report type if specified
            if ($request->has('report_type')) {
                $reports = $reports->where('report_type', $request->report_type);
            }

            // Apply limit
            $limit = $request->input('limit', 20);
            $reports = $reports->take($limit);

            return response()->json([
                'success' => true,
                'data' => $reports->values(),
                'message' => 'Report history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving report history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving report history',
                'errors' => ['history' => ['An error occurred while retrieving report history.']]
            ], 500);
        }
    }
} 