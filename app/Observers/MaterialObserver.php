<?php

namespace App\Observers;

use App\Models\Material;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialObserver
{
    /**
     * Handle the Material "updating" event.
     * TEMPORARILY DISABLED to prevent interference with updates
     */
    public function updating(Material $material): void
    {
        // Log the update attempt
        Log::info('Material Update Attempt', [
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'original_data' => $material->getOriginal(),
            'new_data' => $material->getDirty()
        ]);

        // CRITICAL: Prevent any accidental soft deletion
        if ($material->isDirty('deleted_at')) {
            Log::warning('Blocked deleted_at change during update', [
                'material_id' => $material->id,
                'user_id' => Auth::id(),
                'attempted_deleted_at' => $material->deleted_at
            ]);
            
            // Force deleted_at to remain null
            $material->deleted_at = null;
        }
        
        // Additional safety: ensure material stays active
        $material->setAttribute('deleted_at', null);
    }

    /**
     * Handle the Material "updated" event.
     */
    public function updated(Material $material): void
    {
        Log::info('Material Updated Successfully', [
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'title' => $material->title,
            'is_active' => $material->deleted_at === null,
            'final_deleted_at' => $material->deleted_at
        ]);
        
        // Double-check: if somehow deleted_at got set, fix it immediately
        if ($material->deleted_at !== null) {
            Log::error('Material accidentally soft-deleted, fixing immediately', [
                'material_id' => $material->id,
                'user_id' => Auth::id()
            ]);
            
            DB::table('materials')
                ->where('id', $material->id)
                ->update(['deleted_at' => null]);
        }
    }

    /**
     * Handle the Material "deleting" event.
     */
    public function deleting(Material $material): void
    {
        Log::warning('Material Deletion Attempt', [
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'title' => $material->title,
            'deletion_type' => 'hard_delete' // Since SoftDeletes is disabled
        ]);
    }

    /**
     * Handle the Material "deleted" event.
     */
    public function deleted(Material $material): void
    {
        Log::warning('Material Deleted', [
            'material_id' => $material->id,
            'user_id' => Auth::id(),
            'title' => $material->title,
            'deletion_type' => 'hard_delete' // Since SoftDeletes is disabled
        ]);
    }
}