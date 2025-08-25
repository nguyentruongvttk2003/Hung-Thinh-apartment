<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileUploadController extends Controller
{
    private $allowedTypes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'rtf'],
        'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
        'audio' => ['mp3', 'wav', 'aac', 'ogg', 'flac'],
    ];

    private $maxFileSizes = [
        'image' => 10 * 1024 * 1024, // 10MB
        'document' => 50 * 1024 * 1024, // 50MB
        'video' => 500 * 1024 * 1024, // 500MB
        'audio' => 100 * 1024 * 1024, // 100MB
    ];

    /**
     * Upload single file
     */
    public function uploadSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'type' => 'required|string|in:image,document,video,audio,other',
            'folder' => 'sometimes|string',
            'metadata' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $file = $request->file('file');
        $type = $request->input('type');
        $folder = $request->input('folder', 'general');
        $metadata = $request->input('metadata', []);

        // Validate file type and size
        $validation = $this->validateFile($file, $type);
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }

        try {
            $uploadResult = $this->processFileUpload($file, $type, $folder, $metadata);
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => $uploadResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'required|file',
            'type' => 'required|string|in:image,document,video,audio,other',
            'folder' => 'sometimes|string',
            'metadata' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $files = $request->file('files');
        $type = $request->input('type');
        $folder = $request->input('folder', 'general');
        $metadata = $request->input('metadata', []);

        $uploadedFiles = [];
        $errors = [];

        foreach ($files as $index => $file) {
            // Validate each file
            $validation = $this->validateFile($file, $type);
            if (!$validation['valid']) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $validation['message']
                ];
                continue;
            }

            try {
                $uploadResult = $this->processFileUpload($file, $type, $folder, $metadata);
                $uploadedFiles[] = $uploadResult;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => 'Upload failed: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => count($uploadedFiles) > 0,
            'message' => count($uploadedFiles) . ' files uploaded successfully',
            'uploaded_files' => $uploadedFiles,
            'errors' => $errors,
            'stats' => [
                'total' => count($files),
                'success' => count($uploadedFiles),
                'failed' => count($errors)
            ]
        ]);
    }

    /**
     * Upload avatar/profile image
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $file = $request->file('avatar');
        $user = auth()->user();

        try {
            // Delete old avatar if exists
            if ($user->avatar) {
                $this->removeFile($user->avatar);
            }

            // Upload new avatar
            $uploadResult = $this->processFileUpload($file, 'image', 'avatars', [
                'user_id' => $user->id,
                'is_avatar' => true
            ]);

            // Update user avatar path
            $user->update(['avatar' => $uploadResult['path']]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar' => $uploadResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload feedback attachments
     */
    public function uploadFeedbackAttachment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:5',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:20480', // 20MB max
            'feedback_id' => 'required|integer|exists:feedbacks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $files = $request->file('files');
        $feedbackId = $request->input('feedback_id');

        $uploadedFiles = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $type = $this->getFileType($file);
                $uploadResult = $this->processFileUpload($file, $type, "feedbacks/{$feedbackId}", [
                    'feedback_id' => $feedbackId,
                    'is_attachment' => true
                ]);
                
                $uploadedFiles[] = $uploadResult;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => 'Upload failed: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => count($uploadedFiles) > 0,
            'message' => count($uploadedFiles) . ' attachments uploaded successfully',
            'attachments' => $uploadedFiles,
            'errors' => $errors
        ]);
    }

    /**
     * Get file information
     */
    public function getFileInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $path = $request->input('path');

        if (!Storage::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        try {
            $fileInfo = [
                'path' => $path,
                'url' => Storage::url($path),
                'size' => Storage::size($path),
                'size_formatted' => $this->formatFileSize(Storage::size($path)),
                'mime_type' => Storage::mimeType($path),
                'last_modified' => Carbon::createFromTimestamp(Storage::lastModified($path))->toISOString(),
                'exists' => true
            ];

            return response()->json([
                'success' => true,
                'file' => $fileInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting file info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file
     */
    public function deleteFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $path = $request->input('path');

        try {
            $deleted = $this->removeFile($path);
            
            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'File deleted successfully' : 'File not found or could not be deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List files in folder
     */
    public function listFiles(Request $request)
    {
        $folder = $request->input('folder', '');
        $type = $request->input('type', 'all');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        try {
            $files = Storage::files($folder);
            
            // Filter by type if specified
            if ($type !== 'all') {
                $files = array_filter($files, function($file) use ($type) {
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    return isset($this->allowedTypes[$type]) && in_array($extension, $this->allowedTypes[$type]);
                });
            }

            // Get file information
            $fileList = [];
            foreach ($files as $file) {
                $fileList[] = [
                    'path' => $file,
                    'url' => Storage::url($file),
                    'name' => basename($file),
                    'size' => Storage::size($file),
                    'size_formatted' => $this->formatFileSize(Storage::size($file)),
                    'mime_type' => Storage::mimeType($file),
                    'last_modified' => Carbon::createFromTimestamp(Storage::lastModified($file))->toISOString(),
                ];
            }

            // Simple pagination
            $total = count($fileList);
            $offset = ($page - 1) * $perPage;
            $paginatedFiles = array_slice($fileList, $offset, $perPage);

            return response()->json([
                'success' => true,
                'files' => $paginatedFiles,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage),
                    'has_more' => ($offset + $perPage) < $total
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error listing files: ' . $e->getMessage()
            ], 500);
        }
    }

    // Private helper methods

    private function validateFile($file, $type)
    {
        if (!$file->isValid()) {
            return ['valid' => false, 'message' => 'Invalid file upload'];
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $size = $file->getSize();

        // Check file extension
        if (isset($this->allowedTypes[$type]) && !in_array($extension, $this->allowedTypes[$type])) {
            return [
                'valid' => false, 
                'message' => "File type '{$extension}' is not allowed for type '{$type}'"
            ];
        }

        // Check file size
        if (isset($this->maxFileSizes[$type]) && $size > $this->maxFileSizes[$type]) {
            $maxSizeFormatted = $this->formatFileSize($this->maxFileSizes[$type]);
            return [
                'valid' => false, 
                'message' => "File size exceeds maximum allowed size of {$maxSizeFormatted} for type '{$type}'"
            ];
        }

        return ['valid' => true];
    }

    private function processFileUpload($file, $type, $folder, $metadata = [])
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $path = $folder . '/' . $fileName;

        // Store the file
        $storedPath = $file->storeAs($folder, $fileName, 'public');

        return [
            'original_name' => $originalName,
            'stored_name' => $fileName,
            'path' => $storedPath,
            'url' => Storage::url($storedPath),
            'type' => $type,
            'extension' => $extension,
            'size' => $file->getSize(),
            'size_formatted' => $this->formatFileSize($file->getSize()),
            'mime_type' => $file->getMimeType(),
            'folder' => $folder,
            'metadata' => $metadata,
            'uploaded_at' => Carbon::now()->toISOString()
        ];
    }

    private function getFileType($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        foreach ($this->allowedTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }
        
        return 'other';
    }

    private function removeFile($path)
    {
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }
        return false;
    }

    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
