<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Book;
use App\Models\User;
use App\Models\DownloadLog;
use App\Models\DeliveryLog;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportingService
{
    /**
     * Generate sales report.
     */
    public function generateSalesReport(Request $request): array
    {
        try {
            $dateRange = $request->input('date_range', '30');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Calculate date range
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
            } else {
                $end = Carbon::now()->endOfDay();
                $start = Carbon::now()->subDays($dateRange)->startOfDay();
            }

            // Sales summary
            $totalSales = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->sum('total_amount');

            $totalOrders = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->count();

            $averageOrderValue = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->avg('total_amount') ?? 0;

            // Sales by day
            $dailySales = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Top selling books
            $topSellingBooks = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', 'completed')
                ->selectRaw('order_items.title, order_items.author, COUNT(*) as sales_count, SUM(order_items.price) as revenue')
                ->groupBy('order_items.title', 'order_items.author')
                ->orderByDesc('revenue')
                ->limit(20)
                ->get();

            // Sales by payment method
            $salesByPaymentMethod = Order::join('payments', 'orders.payment_id', '=', 'payments.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', 'completed')
                ->selectRaw('payments.gateway_name, COUNT(*) as orders, SUM(orders.total_amount) as revenue')
                ->groupBy('payments.gateway_name')
                ->orderByDesc('revenue')
                ->get();

            return [
                'summary' => [
                    'total_sales' => $totalSales,
                    'total_orders' => $totalOrders,
                    'average_order_value' => round($averageOrderValue, 2),
                    'period_start' => $start->format('Y-m-d'),
                    'period_end' => $end->format('Y-m-d'),
                ],
                'daily_sales' => $dailySales,
                'top_selling_books' => $topSellingBooks,
                'sales_by_payment_method' => $salesByPaymentMethod,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (\Exception $e) {
            Log::error('Error generating sales report: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate delivery report.
     */
    public function generateDeliveryReport(Request $request): array
    {
        try {
            $dateRange = $request->input('date_range', '30');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Calculate date range
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
            } else {
                $end = Carbon::now()->endOfDay();
                $start = Carbon::now()->subDays($dateRange)->startOfDay();
            }

            // Delivery summary
            $totalDeliveries = DeliveryLog::whereBetween('created_at', [$start, $end])->count();
            $successfulDeliveries = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->where('status', 'success')
                ->count();

            $failedDeliveries = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->where('status', 'failed')
                ->count();

            $deliverySuccessRate = $totalDeliveries > 0 ? ($successfulDeliveries / $totalDeliveries) * 100 : 0;

            // Download summary
            $totalDownloads = DownloadLog::whereBetween('created_at', [$start, $end])->count();
            $uniqueDownloads = DownloadLog::whereBetween('created_at', [$start, $end])
                ->distinct('user_id', 'book_id')
                ->count();

            $totalDownloadSize = DownloadLog::whereBetween('created_at', [$start, $end])
                ->sum('file_size');

            // Delivery performance by day
            $dailyDeliveryPerformance = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get()
                ->groupBy('date');

            // Top downloaded books
            $topDownloadedBooks = DownloadLog::join('books', 'download_logs.book_id', '=', 'books.id')
                ->whereBetween('download_logs.created_at', [$start, $end])
                ->selectRaw('books.title, books.author, COUNT(*) as download_count')
                ->groupBy('books.title', 'books.author')
                ->orderByDesc('download_count')
                ->limit(20)
                ->get();

            // Delivery issues
            $deliveryIssues = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->where('status', 'failed')
                ->selectRaw('error_message, COUNT(*) as count')
                ->groupBy('error_message')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            return [
                'summary' => [
                    'total_deliveries' => $totalDeliveries,
                    'successful_deliveries' => $successfulDeliveries,
                    'failed_deliveries' => $failedDeliveries,
                    'delivery_success_rate' => round($deliverySuccessRate, 2),
                    'total_downloads' => $totalDownloads,
                    'unique_downloads' => $uniqueDownloads,
                    'total_download_size' => $totalDownloadSize,
                    'period_start' => $start->format('Y-m-d'),
                    'period_end' => $end->format('Y-m-d'),
                ],
                'daily_delivery_performance' => $dailyDeliveryPerformance,
                'top_downloaded_books' => $topDownloadedBooks,
                'delivery_issues' => $deliveryIssues,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (\Exception $e) {
            Log::error('Error generating delivery report: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate customer report.
     */
    public function generateCustomerReport(Request $request): array
    {
        try {
            $dateRange = $request->input('date_range', '30');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Calculate date range
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
            } else {
                $end = Carbon::now()->endOfDay();
                $start = Carbon::now()->subDays($dateRange)->startOfDay();
            }

            // Customer summary
            $totalCustomers = Order::whereBetween('created_at', [$start, $end])
                ->distinct('user_id')
                ->count('user_id');

            $newCustomers = User::whereBetween('created_at', [$start, $end])->count();
            $returningCustomers = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('user_id, COUNT(*) as order_count')
                ->groupBy('user_id')
                ->having('order_count', '>', 1)
                ->count();

            // Customer spending analysis
            $customerSpending = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->selectRaw('user_id, COUNT(*) as order_count, SUM(total_amount) as total_spent, AVG(total_amount) as avg_order_value')
                ->groupBy('user_id')
                ->orderByDesc('total_spent')
                ->limit(50)
                ->with('user:id,name,email')
                ->get();

            // Customer activity
            $customerActivity = ActivityLog::whereBetween('created_at', [$start, $end])
                ->selectRaw('user_id, activity_type, COUNT(*) as activity_count')
                ->groupBy('user_id', 'activity_type')
                ->orderByDesc('activity_count')
                ->limit(100)
                ->with('user:id,name,email')
                ->get()
                ->groupBy('user_id');

            // Customer retention
            $customerRetention = $this->calculateCustomerRetention($start, $end);

            return [
                'summary' => [
                    'total_customers' => $totalCustomers,
                    'new_customers' => $newCustomers,
                    'returning_customers' => $returningCustomers,
                    'customer_retention_rate' => $customerRetention['retention_rate'],
                    'period_start' => $start->format('Y-m-d'),
                    'period_end' => $end->format('Y-m-d'),
                ],
                'customer_spending' => $customerSpending,
                'customer_activity' => $customerActivity,
                'customer_retention' => $customerRetention,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (\Exception $e) {
            Log::error('Error generating customer report: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate customer retention.
     */
    private function calculateCustomerRetention(Carbon $start, Carbon $end): array
    {
        try {
            // Get customers from previous period
            $previousStart = $start->copy()->subDays($end->diffInDays($start));
            $previousEnd = $start->copy()->subDay();

            $previousCustomers = Order::whereBetween('created_at', [$previousStart, $previousEnd])
                ->distinct('user_id')
                ->pluck('user_id')
                ->toArray();

            if (empty($previousCustomers)) {
                return [
                    'retention_rate' => 0,
                    'retained_customers' => 0,
                    'total_previous_customers' => 0,
                ];
            }

            // Get returning customers in current period
            $retainedCustomers = Order::whereBetween('created_at', [$start, $end])
                ->whereIn('user_id', $previousCustomers)
                ->distinct('user_id')
                ->count();

            $retentionRate = count($previousCustomers) > 0 ? ($retainedCustomers / count($previousCustomers)) * 100 : 0;

            return [
                'retention_rate' => round($retentionRate, 2),
                'retained_customers' => $retainedCustomers,
                'total_previous_customers' => count($previousCustomers),
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating customer retention: ' . $e->getMessage());
            return [
                'retention_rate' => 0,
                'retained_customers' => 0,
                'total_previous_customers' => 0,
            ];
        }
    }

    /**
     * Export report to CSV.
     */
    public function exportReportToCsv(array $reportData, string $reportType): string
    {
        try {
            $filename = $reportType . '_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $filepath = 'reports/' . $filename;

            $csvData = $this->convertReportToCsv($reportData, $reportType);

            Storage::put($filepath, $csvData);

            return $filepath;

        } catch (\Exception $e) {
            Log::error('Error exporting report to CSV: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert report data to CSV format.
     */
    private function convertReportToCsv(array $reportData, string $reportType): string
    {
        $csv = [];

        switch ($reportType) {
            case 'sales':
                $csv = $this->convertSalesReportToCsv($reportData);
                break;
            case 'delivery':
                $csv = $this->convertDeliveryReportToCsv($reportData);
                break;
            case 'customer':
                $csv = $this->convertCustomerReportToCsv($reportData);
                break;
            default:
                throw new \Exception('Unknown report type: ' . $reportType);
        }

        return $csv;
    }

    /**
     * Convert sales report to CSV.
     */
    private function convertSalesReportToCsv(array $reportData): string
    {
        $csv = [];
        
        // Summary
        $csv[] = ['Sales Report Summary'];
        $csv[] = ['Total Sales', $reportData['summary']['total_sales']];
        $csv[] = ['Total Orders', $reportData['summary']['total_orders']];
        $csv[] = ['Average Order Value', $reportData['summary']['average_order_value']];
        $csv[] = ['Period Start', $reportData['summary']['period_start']];
        $csv[] = ['Period End', $reportData['summary']['period_end']];
        $csv[] = ['Generated At', $reportData['generated_at']];
        $csv[] = [];

        // Daily Sales
        $csv[] = ['Daily Sales'];
        $csv[] = ['Date', 'Revenue', 'Orders'];
        foreach ($reportData['daily_sales'] as $daily) {
            $csv[] = [$daily->date, $daily->revenue, $daily->orders];
        }
        $csv[] = [];

        // Top Selling Books
        $csv[] = ['Top Selling Books'];
        $csv[] = ['Title', 'Author', 'Sales Count', 'Revenue'];
        foreach ($reportData['top_selling_books'] as $book) {
            $csv[] = [$book->title, $book->author, $book->sales_count, $book->revenue];
        }

        return $this->arrayToCsv($csv);
    }

    /**
     * Convert delivery report to CSV.
     */
    private function convertDeliveryReportToCsv(array $reportData): string
    {
        $csv = [];
        
        // Summary
        $csv[] = ['Delivery Report Summary'];
        $csv[] = ['Total Deliveries', $reportData['summary']['total_deliveries']];
        $csv[] = ['Successful Deliveries', $reportData['summary']['successful_deliveries']];
        $csv[] = ['Failed Deliveries', $reportData['summary']['failed_deliveries']];
        $csv[] = ['Delivery Success Rate', $reportData['summary']['delivery_success_rate'] . '%'];
        $csv[] = ['Total Downloads', $reportData['summary']['total_downloads']];
        $csv[] = ['Unique Downloads', $reportData['summary']['unique_downloads']];
        $csv[] = ['Total Download Size', $reportData['summary']['total_download_size']];
        $csv[] = ['Period Start', $reportData['summary']['period_start']];
        $csv[] = ['Period End', $reportData['summary']['period_end']];
        $csv[] = ['Generated At', $reportData['generated_at']];
        $csv[] = [];

        // Top Downloaded Books
        $csv[] = ['Top Downloaded Books'];
        $csv[] = ['Title', 'Author', 'Download Count'];
        foreach ($reportData['top_downloaded_books'] as $book) {
            $csv[] = [$book->title, $book->author, $book->download_count];
        }

        return $this->arrayToCsv($csv);
    }

    /**
     * Convert customer report to CSV.
     */
    private function convertCustomerReportToCsv(array $reportData): string
    {
        $csv = [];
        
        // Summary
        $csv[] = ['Customer Report Summary'];
        $csv[] = ['Total Customers', $reportData['summary']['total_customers']];
        $csv[] = ['New Customers', $reportData['summary']['new_customers']];
        $csv[] = ['Returning Customers', $reportData['summary']['returning_customers']];
        $csv[] = ['Customer Retention Rate', $reportData['summary']['customer_retention_rate'] . '%'];
        $csv[] = ['Period Start', $reportData['summary']['period_start']];
        $csv[] = ['Period End', $reportData['summary']['period_end']];
        $csv[] = ['Generated At', $reportData['generated_at']];
        $csv[] = [];

        // Customer Spending
        $csv[] = ['Customer Spending Analysis'];
        $csv[] = ['Customer Name', 'Email', 'Order Count', 'Total Spent', 'Average Order Value'];
        foreach ($reportData['customer_spending'] as $customer) {
            $csv[] = [
                $customer->user->name ?? 'N/A',
                $customer->user->email ?? 'N/A',
                $customer->order_count,
                $customer->total_spent,
                $customer->avg_order_value,
            ];
        }

        return $this->arrayToCsv($csv);
    }

    /**
     * Convert array to CSV string.
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Generate scheduled report.
     */
    public function generateScheduledReport(string $reportType, string $frequency = 'daily'): array
    {
        try {
            $endDate = Carbon::now()->endOfDay();
            
            switch ($frequency) {
                case 'weekly':
                    $startDate = Carbon::now()->subWeek()->startOfDay();
                    break;
                case 'monthly':
                    $startDate = Carbon::now()->subMonth()->startOfDay();
                    break;
                default:
                    $startDate = Carbon::now()->subDay()->startOfDay();
                    break;
            }

            $request = new Request([
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);

            switch ($reportType) {
                case 'sales':
                    return $this->generateSalesReport($request);
                case 'delivery':
                    return $this->generateDeliveryReport($request);
                case 'customer':
                    return $this->generateCustomerReport($request);
                default:
                    throw new \Exception('Unknown report type: ' . $reportType);
            }

        } catch (\Exception $e) {
            Log::error('Error generating scheduled report: ' . $e->getMessage());
            return [];
        }
    }
} 