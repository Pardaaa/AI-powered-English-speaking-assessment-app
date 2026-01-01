<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChunkUploadController extends Controller
{
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'original_name' => 'required|string',
            'chunk' => 'required|file|max:10240', 
        ]);

        $uploadId = $request->input('upload_id');
        $chunkIndex = (int) $request->input('chunk_index');

        $dir = "chunks/{$uploadId}";
        Storage::disk('local')->makeDirectory($dir);

        $filename = sprintf("chunk_%06d.part", $chunkIndex);
        Storage::disk('local')->putFileAs($dir, $request->file('chunk'), $filename);

        return response()->json([
            'status' => 'ok',
            'saved' => $filename
        ]);
    }

    public function completeUpload(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string',
            'original_name' => 'required|string',
        ]);

        $uploadId = $request->input('upload_id');
        $originalName = $request->input('original_name');

        $chunkDir = "chunks/{$uploadId}";
        $chunkFiles = Storage::disk('local')->files($chunkDir);

        if (empty($chunkFiles)) {
            return response()->json(['error' => 'No chunks found for upload_id: '.$uploadId], 400);
        }

        sort($chunkFiles);

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'mp4');
        $allowed = ['mp4', 'webm', 'mp3', 'wav', 'm4a', 'ogg'];
        if (!in_array($ext, $allowed, true)) $ext = 'mp4';

        $rel = env('AI_UPLOAD_RELATIVE_PATH', '../ai-processing-service/uploads');
        $targetDir = realpath(base_path($rel)) ?: base_path($rel);

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $finalFilename = $uploadId . '.' . $ext;
        $finalPath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $finalFilename;

        $out = fopen($finalPath, 'wb');
        if (!$out) {
            return response()->json(['error' => 'Cannot create final file at: '.$finalPath], 500);
        }

        foreach ($chunkFiles as $chunk) {
            $chunkFull = Storage::disk('local')->path($chunk);
            $in = fopen($chunkFull, 'rb');
            if (!$in) {
                fclose($out);
                return response()->json(['error' => 'Cannot read chunk file: '.$chunkFull], 500);
            }
            stream_copy_to_stream($in, $out);
            fclose($in);
        }

        fclose($out);

        Storage::disk('local')->deleteDirectory($chunkDir);

        $baseUrl = rtrim(env('AI_SERVICE_BASE_URL', 'http://127.0.0.1:5000'), '/');
        $fileUrl = $baseUrl . '/uploads/' . $finalFilename;

        return response()->json([
            'status' => 'complete',
            'file_name' => $finalFilename,
            'file_url' => $fileUrl
        ]);
    }
}
