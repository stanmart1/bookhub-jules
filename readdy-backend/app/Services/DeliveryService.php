<?php

namespace App\Services;

use App\Models\Order;
use App\Models\DeliveryLog;
use App\Models\DownloadLog;
use App\Models\BookFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeliveryService
{
    /**
     * Process digital delivery for an order
     */
    public function processDigitalDelivery(Order $order): array
    {
        try {
            DB::beginTransaction();

            if (!$order->canBeDelivered()) {
                throw new \Exception('Order cannot be delivered in its current status');
            }

            // Mark delivery as processing
            $order->markDeliveryAsProcessing();

            // Generate delivery token if not exists
            if (!$order->delivery_token) {
                $order->generateDeliveryToken();
            }

            // Process each book in the order
            $deliveryResults = [];
            foreach ($order->items as $item) {
                $book = $item->book;
                $bookFiles = $book->files;

                foreach ($bookFiles as $bookFile) {
                    $result = $this->processBookDelivery($order, $book, $bookFile);
                    $deliveryResults[] = $result;
                }
            }

            // Send delivery notifications
            $this->sendDeliveryNotifications($order);

            // Mark delivery as completed
            $order->markDeliveryAsDelivered();

            DB::commit();

            Log::info('Digital delivery processed successfully', [
                'order_id' => $order->id,
                'delivery_results' => $deliveryResults
            ]);

            return [
                'success' => true,
                'message' => 'Digital delivery processed successfully',
                'delivery_token' => $order->delivery_token,
                'delivery_results' => $deliveryResults
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing digital delivery', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            $order->markDeliveryAsFailed($e->getMessage());

            return [
                'success' => false,
                'message' => 'Error processing digital delivery: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process delivery for a specific book
     */
    private function processBookDelivery(Order $order, $book, BookFile $bookFile): array
    {
        try {
            // Create download log entry
            $downloadLog = DownloadLog::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'book_id' => $book->id,
                'book_file_id' => $bookFile->id,
                'download_token' => $this->generateDownloadToken(),
                'status' => DownloadLog::STATUS_INITIATED,
                'initiated_at' => now(),
                'expires_at' => now()->addDays(30), // 30 days expiry
                'total_bytes' => $bookFile->file_size,
                'metadata' => [
                    'book_title' => $book->title,
                    'file_name' => $bookFile->file_name,
                    'file_type' => $bookFile->file_type
                ]
            ]);

            // Add book to user's library if not already there
            $this->addBookToUserLibrary($order->user_id, $book->id, $order);

            return [
                'success' => true,
                'book_id' => $book->id,
                'book_title' => $book->title,
                'file_id' => $bookFile->id,
                'download_token' => $downloadLog->download_token,
                'expires_at' => $downloadLog->expires_at
            ];

        } catch (\Exception $e) {
            Log::error('Error processing book delivery', [
                'order_id' => $order->id,
                'book_id' => $book->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'book_id' => $book->id,
                'book_title' => $book->title,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate secure download token
     */
    private function generateDownloadToken(): string
    {
        do {
            $token = Str::random(64);
        } while (DownloadLog::where('download_token', $token)->exists());

        return $token;
    }

    /**
     * Add book to user's library
     */
    private function addBookToUserLibrary(int $userId, int $bookId, Order $order): void
    {
        // Check if book is already in user's library
        $existingLibrary = DB::table('user_libraries')
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        if (!$existingLibrary) {
            DB::table('user_libraries')->insert([
                'user_id' => $userId,
                'book_id' => $bookId,
                'purchase_date' => now(),
                'purchase_price' => $order->items->where('book_id', $bookId)->first()->price ?? 0,
                'payment_method' => $order->payment->gateway ?? 'unknown',
                'transaction_id' => $order->payment->transaction_id ?? null,
                'is_gift' => false,
                'gift_from' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Send delivery notifications
     */
    private function sendDeliveryNotifications(Order $order): void
    {
        // Send email notification
        $this->sendDeliveryEmail($order);

        // Send SMS notification (if configured)
        $this->sendDeliverySMS($order);

        // Send in-app notification
        $this->sendInAppNotification($order);
    }

    /**
     * Send delivery email
     */
    private function sendDeliveryEmail(Order $order): void
    {
        try {
            // Create delivery log entry
            DeliveryLog::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'delivery_type' => DeliveryLog::TYPE_EMAIL,
                'delivery_method' => DeliveryLog::METHOD_EMAIL,
                'status' => DeliveryLog::STATUS_PENDING,
                'recipient' => $order->user->email,
                'subject' => 'Your digital books are ready for download!',
                'content' => 'delivery_confirmation_email',
                'metadata' => [
                    'order_number' => $order->order_number,
                    'delivery_token' => $order->delivery_token
                ]
            ]);

            // TODO: Implement actual email sending logic
            // For now, mark as sent
            $order->update([
                'confirmation_email_sent' => true,
                'confirmation_email_sent_at' => now()
            ]);

            Log::info('Delivery email sent', ['order_id' => $order->id]);

        } catch (\Exception $e) {
            Log::error('Error sending delivery email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send delivery SMS
     */
    private function sendDeliverySMS(Order $order): void
    {
        try {
            if (!$order->user->phone) {
                return;
            }

            // Create delivery log entry
            DeliveryLog::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'delivery_type' => DeliveryLog::TYPE_SMS,
                'delivery_method' => DeliveryLog::METHOD_SMS,
                'status' => DeliveryLog::STATUS_PENDING,
                'recipient' => $order->user->phone,
                'content' => 'delivery_confirmation_sms',
                'metadata' => [
                    'order_number' => $order->order_number
                ]
            ]);

            // TODO: Implement actual SMS sending logic
            // For now, mark as sent
            $order->update([
                'confirmation_sms_sent' => true,
                'confirmation_sms_sent_at' => now()
            ]);

            Log::info('Delivery SMS sent', ['order_id' => $order->id]);

        } catch (\Exception $e) {
            Log::error('Error sending delivery SMS', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send in-app notification
     */
    private function sendInAppNotification(Order $order): void
    {
        try {
            // Create delivery log entry
            DeliveryLog::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'delivery_type' => DeliveryLog::TYPE_NOTIFICATION,
                'delivery_method' => DeliveryLog::METHOD_IN_APP,
                'status' => DeliveryLog::STATUS_DELIVERED,
                'recipient' => $order->user_id,
                'content' => 'Your digital books are ready for download!',
                'delivered_at' => now(),
                'metadata' => [
                    'order_number' => $order->order_number,
                    'delivery_token' => $order->delivery_token
                ]
            ]);

            Log::info('In-app delivery notification sent', ['order_id' => $order->id]);

        } catch (\Exception $e) {
            Log::error('Error sending in-app delivery notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate download token and get download info
     */
    public function validateDownloadToken(string $token): ?array
    {
        $downloadLog = DownloadLog::where('download_token', $token)
            ->with(['order', 'book', 'bookFile'])
            ->first();

        if (!$downloadLog) {
            return null;
        }

        if ($downloadLog->isExpired()) {
            $downloadLog->update(['status' => DownloadLog::STATUS_EXPIRED]);
            return null;
        }

        if ($downloadLog->isFailed()) {
            return null;
        }

        return [
            'download_log' => $downloadLog,
            'order' => $downloadLog->order,
            'book' => $downloadLog->book,
            'book_file' => $downloadLog->bookFile,
            'download_url' => $this->generateDownloadUrl($downloadLog)
        ];
    }

    /**
     * Generate secure download URL
     */
    private function generateDownloadUrl(DownloadLog $downloadLog): string
    {
        // Update download status to downloading
        $downloadLog->update([
            'status' => DownloadLog::STATUS_DOWNLOADING,
            'started_at' => now()
        ]);

        // Generate a temporary signed URL for the file
        $filePath = $downloadLog->bookFile->file_path;
        
        if (Storage::disk('local')->exists($filePath)) {
            return route('api.v1.books.files.download', [
                'book' => $downloadLog->book_id,
                'file' => $downloadLog->book_file_id,
                'token' => $downloadLog->download_token
            ]);
        }

        return null;
    }

    /**
     * Record download completion
     */
    public function recordDownloadCompletion(string $token): bool
    {
        $downloadLog = DownloadLog::where('download_token', $token)->first();

        if (!$downloadLog) {
            return false;
        }

        $downloadLog->update([
            'status' => DownloadLog::STATUS_COMPLETED,
            'completed_at' => now(),
            'bytes_downloaded' => $downloadLog->total_bytes
        ]);

        return true;
    }

    /**
     * Get delivery statistics
     */
    public function getDeliveryStatistics(int $days = 30): array
    {
        $deliveryStats = DeliveryLog::getStatistics($days);
        $downloadStats = DownloadLog::getStatistics($days);

        return [
            'delivery' => $deliveryStats,
            'downloads' => $downloadStats,
            'summary' => [
                'total_orders_delivered' => Order::deliveryDelivered()
                    ->where('delivered_at', '>=', now()->subDays($days))
                    ->count(),
                'total_orders_pending_delivery' => Order::deliveryPending()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->count(),
                'delivery_success_rate' => $deliveryStats['success_rate'],
                'download_success_rate' => $downloadStats['success_rate']
            ]
        ];
    }

    /**
     * Retry failed deliveries
     */
    public function retryFailedDeliveries(): array
    {
        $failedOrders = Order::deliveryFailed()
            ->where('delivery_attempts', '<', 3)
            ->get();

        $results = [];
        foreach ($failedOrders as $order) {
            $result = $this->processDigitalDelivery($order);
            $results[] = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'result' => $result
            ];
        }

        return [
            'total_retried' => count($failedOrders),
            'results' => $results
        ];
    }
}
