<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Log;

class SendDownloadReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delivery:send-reminders {--days=3 : Number of days after purchase to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send download reminders for orders that haven\'t been downloaded yet';

    /**
     * Execute the console command.
     */
    public function handle(DeliveryService $deliveryService)
    {
        $this->info('Starting download reminder process...');
        
        try {
            $days = $this->option('days');
            
            $this->info("Sending reminders for orders older than {$days} days...");
            
            $deliveryService->scheduleDownloadReminders();
            
            $this->info('Download reminders sent successfully!');
            
            Log::info('Download reminders command executed successfully', [
                'days' => $days,
                'executed_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            $this->error('Error sending download reminders: ' . $e->getMessage());
            
            Log::error('Error in download reminders command: ' . $e->getMessage(), [
                'days' => $this->option('days'),
                'executed_at' => now(),
            ]);
            
            return 1;
        }
        
        return 0;
    }
} 