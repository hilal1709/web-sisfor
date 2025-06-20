<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes; // DISABLED to prevent issues

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Material extends Model
{
    use HasFactory; // SoftDeletes REMOVED

    protected static function boot()
    {
        parent::boot();

        // Keep only essential events, disable problematic ones
        static::deleting(function ($material) {
            Log::warning('Material Deleting Event Triggered', [
                'material_id' => $material->id,
                'is_force_delete' => $material->isForceDeleting(),
                'triggered_by' => Auth::id() ?? 'system'
            ]);
        });
    }

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'original_filename',
        'is_verified',
        'course_id',
        'user_id',
        'fakultas',
        'jurusan',
        'semester',
        'mata_kuliah',
        'kategori',
        'slug',
        'size',
        'type',
        'visibility',
        'download_count',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    public function verifications()
    {
        return $this->hasMany(MaterialVerification::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_materials');
    }
}
