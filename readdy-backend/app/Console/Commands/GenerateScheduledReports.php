<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportingService;
use Illuminate\Support\Facades\Log;

class GenerateScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-scheduled {--frequency=daily : Frequency of reports to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all scheduled reports for the specified frequency';

    /**
     * Execute the console command.
     */
    public function handle(ReportingService $reportingService)
    {
        $frequency = $this->option('frequency');
        
        $this->info("Generating {$frequency} scheduled reports...");

        $reportTypes = ['sales', 'delivery', 'customer'];
        $successCount = 0;
        $errorCount = 0;

        foreach ($reportTypes as $type) {
            try {
                $this->line("Generating {$type} report...");
                
                $report = $reportingService->generateScheduledReport($type, $frequency);
                
                if (!empty($report)) {
                    $this->info("✓ {$type} report generated successfully");
                    $successCount++;
                    
                    // Log successful generation
                    Log::info("Scheduled report generated successfully", [
                        'type' => $type,
                        'frequency' => $frequency,
                        'generated_at' => now(),
                    ]);
                } else {
                    $this->warn("⚠ No data available for {$type} report");
                }

            } catch (\Exception $e) {
                $this->error("✗ Error generating {$type} report: " . $e->getMessage());
                $errorCount++;
                
                Log::error("Error generating scheduled report: " . $e->getMessage(), [
                    'type' => $type,
                    'frequency' => $frequency,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->line('');
        $this->info("=== Report Generation Summary ===");
        $this->line("Frequency: {$frequency}");
        $this->line("Successful: {$successCount}");
        $this->line("Errors: {$errorCount}");
        $this->line("Total: " . count($reportTypes));

        if ($errorCount > 0) {
            return 1;
        }

        return 0;
    }
} 