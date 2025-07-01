<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;

class MediaHelper
{

    public static function storeMedia(array $files, $postId = null)
    {
        $mediaFiles = [];
        foreach ($files as $file) {
            $prefix = $postId ? $postId . '_' : '';
            $filename = $prefix . uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('posts/media', $filename, 'public');
            $type = explode('/', $file->getMimeType())[0];
            
            $mediaFiles[] = [
                'path' => $path,
                'type' => $type
            ];
        }
        return $mediaFiles;
    }

    public static function duplicateMedia(Media $media)
    {
        $newPath = 'posts/media/' . uniqid() . '_' . basename($media->path);
        Storage::disk('public')->copy($media->path, $newPath);
        
        return [
            'path' => $newPath,
            'type' => $media->type
        ];
    }

    public static function deleteMediaFiles(array $paths)
    {
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}
