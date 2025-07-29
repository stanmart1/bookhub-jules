<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeliveryService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ProcessDeliveryNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delivery:process-notifications {--order-id= : Process specific order ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process delivery notifications for completed orders';

    /**
     * Execute the console command.
     */
    public function handle(DeliveryService $deliveryService)
    {
        $this->info('Starting delivery notification process...');
        
        try {
            $orderId = $this->option('order-id');
            
            if ($orderId) {
                // Process specific order
                $order = Order::with(['user', 'items.book.bookFile'])->find($orderId);
                
                if (!$order) {
                    $this->error("Order with ID {$orderId} not found.");
                    return 1;
                }
                
                if ($order->status !== 'completed') {
                    $this->error("Order {$orderId} is not completed. Current status: {$order->status}");
                    return 1;
                }
                
                $this->info("Processing delivery notifications for order #{$order->order_number}...");
                $deliveryService->processDeliveryNotifications($order);
                
                $this->info("Delivery notifications processed for order #{$order->order_number}!");
                
            } else {
                // Process all completed orders from the last 24 hours
                $orders = Order::with(['user', 'items.book.bookFile'])
                    ->where('status', 'completed')
                    ->where('created_at', '>=', now()->subDay())
                    ->whereDoesntHave('items.book.bookFile', function ($query) {
                        $query->whereNull('id');
                    })
                    ->get();
                
                $this->info("Found {$orders->count()} completed orders to process...");
                
                $processedCount = 0;
                
                foreach ($orders as $order) {
                    $this->line("Processing order #{$order->order_number}...");
                    $deliveryService->processDeliveryNotifications($order);
                    $processedCount++;
                }
                
                $this->info("Processed delivery notifications for {$processedCount} orders!");
            }
            
            Log::info('Delivery notifications command executed successfully', [
                'order_id' => $orderId,
                'processed_count' => $orderId ? 1 : ($orders->count() ?? 0),
                'executed_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            $this->error('Error processing delivery notifications: ' . $e->getMessage());
            
            Log::error('Error in delivery notifications command: ' . $e->getMessage(), [
                'order_id' => $this->option('order-id'),
                'executed_at' => now(),
            ]);
            
            return 1;
        }
        
        return 0;
    }
} 