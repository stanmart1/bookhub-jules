<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookFileUploadRequest;
use App\Models\Book;
use App\Models\BookFile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookFileController extends Controller
{
    /**
     * Upload a file for a book.
     */
    public function upload(BookFileUploadRequest $request, Book $book): JsonResponse
    {
        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $fileType = $request->input('file_type');
            $isPrimary = $request->boolean('is_primary', false);
            $duration = $request->input('duration');

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = "book-files/{$book->id}/{$filename}";

            // Store the file
            $storedPath = Storage::disk('local')->putFileAs(
                "book-files/{$book->id}",
                $file,
                $filename
            );

            if (!$storedPath) {
                throw new \Exception('Failed to store file');
            }

            // If this is primary, unset other primary files of the same type
            if ($isPrimary) {
                BookFile::where('book_id', $book->id)
                    ->where('file_type', $fileType)
                    ->update(['is_primary' => false]);
            }

            // Create book file record
            $bookFile = BookFile::create([
                'book_id' => $book->id,
                'file_type' => $fileType,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'duration' => $duration,
                'is_primary' => $isPrimary,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $bookFile,
                'message' => 'File uploaded successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading book file: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file',
                'errors' => ['upload' => ['An error occurred while uploading the file.']]
            ], 500);
        }
    }

    /**
     * Download a book file (with access control).
     */
    public function download(Book $book, BookFile $file): JsonResponse
    {
        try {
            // Check if user owns the book or if it's free
            if (!$book->is_free && !$book->owners()->where('user_id', auth()->id())->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'errors' => ['access' => ['You do not have access to this book.']]
                ], 403);
            }

            // Check if file exists
            if (!Storage::disk('local')->exists($file->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                    'errors' => ['file' => ['The requested file does not exist.']]
                ], 404);
            }

            // Increment download count
            $book->increment('download_count');

            // Generate temporary download URL (for security)
            $downloadUrl = Storage::disk('local')->temporaryUrl(
                $file->file_path,
                now()->addMinutes(30),
                [
                    'ResponseContentDisposition' => 'attachment; filename="' . basename($file->file_path) . '"'
                ]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $downloadUrl,
                    'file_name' => basename($file->file_path),
                    'file_size' => $file->file_size,
                    'file_type' => $file->file_type,
                ],
                'message' => 'Download URL generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error downloading book file: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating download URL',
                'errors' => ['download' => ['An error occurred while generating the download URL.']]
            ], 500);
        }
    }

    /**
     * List all files for a book.
     */
    public function list(Book $book): JsonResponse
    {
        try {
            $files = $book->files()->orderBy('is_primary', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $files,
                'message' => 'Book files retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving book files: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving book files',
                'errors' => ['database' => ['An error occurred while retrieving the files.']]
            ], 500);
        }
    }

    /**
     * Delete a book file.
     */
    public function delete(Book $book, BookFile $file): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Delete the physical file
            if (Storage::disk('local')->exists($file->file_path)) {
                Storage::disk('local')->delete($file->file_path);
            }

            // Delete the database record
            $file->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting book file: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file',
                'errors' => ['delete' => ['An error occurred while deleting the file.']]
            ], 500);
        }
    }

    /**
     * Set a file as primary for its type.
     */
    public function setPrimary(Book $book, BookFile $file): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Unset other primary files of the same type
            BookFile::where('book_id', $book->id)
                ->where('file_type', $file->file_type)
                ->update(['is_primary' => false]);

            // Set this file as primary
            $file->update(['is_primary' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $file,
                'message' => 'File set as primary successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting primary file: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error setting primary file',
                'errors' => ['update' => ['An error occurred while setting the primary file.']]
            ], 500);
        }
    }
} 