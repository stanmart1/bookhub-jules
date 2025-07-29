<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnalyticsService;
use App\Services\ReportingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateAnalyticsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:generate-report 
                            {type : Type of report (sales, delivery, customer, comprehensive)}
                            {--frequency=daily : Frequency of report (daily, weekly, monthly)}
                            {--export : Export report to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate scheduled analytics reports';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsService $analyticsService, ReportingService $reportingService)
    {
        $type = $this->argument('type');
        $frequency = $this->option('frequency');
        $export = $this->option('export');

        $this->info("Generating {$frequency} {$type} report...");

        try {
            $report = match ($type) {
                'sales' => $reportingService->generateScheduledReport('sales', $frequency),
                'delivery' => $reportingService->generateScheduledReport('delivery', $frequency),
                'customer' => $reportingService->generateScheduledReport('customer', $frequency),
                'comprehensive' => $analyticsService->generateAnalyticsReport(
                    new \Illuminate\Http\Request(['date_range' => $this->getDateRange($frequency)])
                ),
                default => throw new \Exception("Unknown report type: {$type}"),
            };

            if (empty($report)) {
                $this->error("No data available for {$type} report");
                return 1;
            }

            $this->info("Report generated successfully!");

            // Display summary
            $this->displayReportSummary($report, $type);

            // Export if requested
            if ($export) {
                $this->info("Exporting report to CSV...");
                $filepath = $reportingService->exportReportToCsv($report, $type);
                $this->info("Report exported to: {$filepath}");
            }

            // Log successful generation
            Log::info("Analytics report generated successfully", [
                'type' => $type,
                'frequency' => $frequency,
                'exported' => $export,
                'generated_at' => now(),
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error generating report: " . $e->getMessage());
            
            Log::error("Error generating analytics report: " . $e->getMessage(), [
                'type' => $type,
                'frequency' => $frequency,
                'error' => $e->getMessage(),
            ]);
            
            return 1;
        }
    }

    /**
     * Get date range based on frequency.
     */
    private function getDateRange(string $frequency): int
    {
        return match ($frequency) {
            'weekly' => 7,
            'monthly' => 30,
            default => 1, // daily
        };
    }

    /**
     * Display report summary.
     */
    private function displayReportSummary(array $report, string $type): void
    {
        $this->line('');
        $this->info("=== {$type} Report Summary ===");

        if (isset($report['summary'])) {
            foreach ($report['summary'] as $key => $value) {
                $formattedKey = str_replace('_', ' ', ucfirst($key));
                $formattedValue = is_numeric($value) ? number_format($value, 2) : $value;
                $this->line("{$formattedKey}: {$formattedValue}");
            }
        }

        if (isset($report['generated_at'])) {
            $this->line("Generated At: {$report['generated_at']}");
        }

        $this->line('');
    }
} 