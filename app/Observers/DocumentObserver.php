<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Support\Facades\Log;
use Exception;

class DocumentObserver
{
    /**
     * Handle the document "deleting" event.
     * Delete the physical file from storage before the database record is removed.
     *
     * @return void
     */
    public function deleting(Document $document)
    {
        try {
            $fileSystem = GetStorageProvider::getStorage();
            $fileSystem->delete($document);
        } catch (Exception $e) {
            // Log the error but don't prevent the database deletion
            // The file might already be deleted or the storage might be unavailable
            Log::warning('Failed to delete document file from storage', [
                'document_id' => $document->id,
                'path' => $document->path,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
