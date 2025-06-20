<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class MaterialController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ensure we only get materials that are not soft deleted
        $query = Material::query()
            ->whereNull('deleted_at') // Explicitly filter out soft deleted materials
            ->with(['course', 'user', 'verifications'])
            ->when($request->filled('verified'), function ($query) {
                $query->where('is_verified', true);
            })
            ->when($request->filled('course'), function ($query) use ($request) {
                $query->where('course_id', $request->course);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('fakultas'), function ($query) use ($request) {
                $fakultas = $request->fakultas === 'other' ? $request->fakultas_other : $request->fakultas;
                if ($fakultas) {
                    $query->where('fakultas', 'like', "%{$fakultas}%");
                }
            })
            ->when($request->filled('jurusan'), function ($query) use ($request) {
                $jurusan = $request->jurusan === 'other' ? $request->jurusan_other : $request->jurusan;
                if ($jurusan) {
                    $query->where('jurusan', 'like', "%{$jurusan}%");
                }
            })
            ->when($request->filled('semester'), function ($query) use ($request) {
                $query->where('semester', $request->semester);
            })
            ->when($request->filled('mata_kuliah'), function ($query) use ($request) {
                $mata_kuliah = $request->mata_kuliah === 'other' ? $request->mata_kuliah_other : $request->mata_kuliah;
                if ($mata_kuliah) {
                    $query->where('mata_kuliah', 'like', "%{$mata_kuliah}%");
                }
            });

        $materials = $query->latest()->paginate(10);
        $courses = Course::all();

        // Log query for debugging
        Log::info('Materials Index Query', [
            'total_materials' => $materials->total(),
            'current_page' => $materials->currentPage(),
            'per_page' => $materials->perPage(),
            'filters' => $request->all()
        ]);

        return view('materials.index', compact('materials', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::all();
        return view('materials.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar|max:20480', // Max 20MB, multiple file types
            'course_id' => 'required|exists:courses,id',
            'fakultas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'semester' => 'required|string|max:10',
            'mata_kuliah' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'fakultas_other' => 'nullable|required_if:fakultas,other|string|max:255',
            'jurusan_other' => 'nullable|required_if:jurusan,other|string|max:255',
            'mata_kuliah_other' => 'nullable|required_if:mata_kuliah,other|string|max:255',
        ], [
            'file.mimes' => 'File harus berformat: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, atau RAR',
            'file.max' => 'Ukuran file maksimal 20MB',
            'title.required' => 'Judul materi wajib diisi',
            'description.required' => 'Deskripsi materi wajib diisi',
            'course_id.required' => 'Mata kuliah wajib dipilih',
            'fakultas.required' => 'Fakultas wajib dipilih',
            'jurusan.required' => 'Jurusan wajib dipilih',
            'semester.required' => 'Semester wajib dipilih',
            'mata_kuliah.required' => 'Mata kuliah wajib dipilih',
            'fakultas_other.required_if' => 'Nama fakultas wajib diisi jika memilih "Lainnya"',
            'jurusan_other.required_if' => 'Nama jurusan wajib diisi jika memilih "Lainnya"',
            'mata_kuliah_other.required_if' => 'Nama mata kuliah wajib diisi jika memilih "Lainnya"',
        ]);

        // Handle "other" options for fakultas, jurusan, and mata_kuliah
        $fakultas = $validated['fakultas'] === 'other' ? ($request->input('fakultas_other') ?? '') : $validated['fakultas'];
        $jurusan = $validated['jurusan'] === 'other' ? ($request->input('jurusan_other') ?? '') : $validated['jurusan'];
        $mata_kuliah = $validated['mata_kuliah'] === 'other' ? ($request->input('mata_kuliah_other') ?? '') : $validated['mata_kuliah'];

        // Validate processed values
        if (empty($fakultas)) {
            return redirect()->back()->withInput()->withErrors(['fakultas' => 'Fakultas tidak boleh kosong']);
        }
        if (empty($jurusan)) {
            return redirect()->back()->withInput()->withErrors(['jurusan' => 'Jurusan tidak boleh kosong']);
        }
        if (empty($mata_kuliah)) {
            return redirect()->back()->withInput()->withErrors(['mata_kuliah' => 'Mata kuliah tidak boleh kosong']);
        }

        // Generate unique filename to prevent conflicts
        $originalName = $request->file('file')->getClientOriginalName();
        $extension = $request->file('file')->getClientOriginalExtension();
        $filename = time() . '_' . str_replace(' ', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;

        $filePath = $request->file('file')->storeAs('materials', $filename, 'public');

        // Log file upload attempt
        Log::info('Material Upload - File Storage', [
            'original_name' => $originalName,
            'stored_path' => $filePath,
            'file_exists' => Storage::disk('public')->exists($filePath)
        ]);

        try {
            DB::beginTransaction();

            // Create new material instance
            $material = new Material();
            $material->title = $validated['title'];
            $material->description = $validated['description'];
            $material->file_path = $filePath;
            $material->course_id = $validated['course_id'];
            $material->user_id = Auth::id();
            $material->original_filename = $originalName;
            $material->fakultas = $fakultas;
            $material->jurusan = $jurusan;
            $material->semester = $validated['semester'];
            $material->mata_kuliah = $mata_kuliah;
            $material->kategori = $validated['kategori'];

            // Save without triggering events that might cause issues
            $material->saveQuietly();

            // Log successful material creation
            Log::info('Material Upload - Database Entry Created', [
                'material_id' => $material->id,
                'user_id' => Auth::id(),
                'data' => $material->toArray()
            ]);

            DB::commit();
            return redirect()->route('materials.show', $material)
                ->with('success', 'Materi berhasil diunggah! Menunggu verifikasi dari dosen.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if creation failed
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            Log::error('Material Upload - Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat mengunggah materi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        $material->load(['course', 'user', 'discussions' => function ($query) {
            $query->whereNull('parent_id')->with(['user', 'replies.user']);
        }]);
        return view('materials.show', compact('material'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material)
    {
        // Check if user is the owner of the material
        if ($material->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit materi ini.');
        }

        $courses = Course::all();
        return view('materials.edit', compact('material', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find material - since SoftDeletes is disabled, use regular find
        $material = Material::findOrFail($id);
        
        // Check if material exists and is not soft deleted at DB level
        $dbMaterial = DB::table('materials')->where('id', $id)->first();
        if (!$dbMaterial || $dbMaterial->deleted_at !== null) {
            // If material is soft deleted at DB level, restore it
            if ($dbMaterial && $dbMaterial->deleted_at !== null) {
                Log::warning('Material was soft-deleted at DB level, restoring before update', [
                    'material_id' => $id,
                    'user_id' => Auth::id()
                ]);
                DB::table('materials')->where('id', $id)->update(['deleted_at' => null]);
                $material = Material::findOrFail($id); // Reload the model
            }
        }

        // Check if user is the owner of the material
        if ($material->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit materi ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar|max:20480',
            'course_id' => 'required|exists:courses,id',
            'fakultas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'semester' => 'required|string|max:10',
            'mata_kuliah' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'fakultas_other' => 'nullable|string|max:255',
            'jurusan_other' => 'nullable|string|max:255',
            'mata_kuliah_other' => 'nullable|string|max:255',
        ]);

        // Handle "other" options
        $fakultas = $validated['fakultas'] === 'other' ? $request->input('fakultas_other', '') : $validated['fakultas'];
        $jurusan = $validated['jurusan'] === 'other' ? $request->input('jurusan_other', '') : $validated['jurusan'];
        $mata_kuliah = $validated['mata_kuliah'] === 'other' ? $request->input('mata_kuliah_other', '') : $validated['mata_kuliah'];

        // Validate processed values
        if (empty($fakultas)) {
            return redirect()->back()->withInput()->withErrors(['fakultas' => 'Fakultas tidak boleh kosong']);
        }
        if (empty($jurusan)) {
            return redirect()->back()->withInput()->withErrors(['jurusan' => 'Jurusan tidak boleh kosong']);
        }
        if (empty($mata_kuliah)) {
            return redirect()->back()->withInput()->withErrors(['mata_kuliah' => 'Mata kuliah tidak boleh kosong']);
        }

        try {
            DB::beginTransaction();

            // Prepare update data
            $updateData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'course_id' => $validated['course_id'],
                'fakultas' => $fakultas,
                'jurusan' => $jurusan,
                'semester' => $validated['semester'],
                'mata_kuliah' => $mata_kuliah,
                'kategori' => $validated['kategori'],
                'updated_at' => now()
            ];

            // Handle file upload if present
            if ($request->hasFile('file')) {
                $originalName = $request->file('file')->getClientOriginalName();
                $extension = $request->file('file')->getClientOriginalExtension();
                $filename = time() . '_' . str_replace(' ', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                $filePath = $request->file('file')->storeAs('materials', $filename, 'public');

                if ($filePath) {
                    // Delete old file
                    if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                        Storage::disk('public')->delete($material->file_path);
                    }
                    
                    // Add file fields to update data
                    $updateData['file_path'] = $filePath;
                    $updateData['original_filename'] = $originalName;
                }
            }

            // Use direct DB update to completely bypass Eloquent model events
            // CRITICAL: Explicitly ensure material stays active
            $updateData['deleted_at'] = null;
            
            // Log the update attempt
            Log::info('Material Update - Before DB Update', [
                'material_id' => $material->id,
                'user_id' => Auth::id(),
                'update_data' => $updateData,
                'current_deleted_at' => $material->deleted_at
            ]);
            
            $updated = DB::table('materials')
                ->where('id', $material->id)
                ->update($updateData); // Remove whereNull check to ensure update happens

            if (!$updated) {
                throw new \Exception('Failed to update material in database - no rows affected');
            }

            // Verify the update worked and material is still active
            $updatedMaterial = DB::table('materials')
                ->where('id', $material->id)
                ->first();
            
            if (!$updatedMaterial) {
                throw new \Exception('Material not found after update');
            }
            
            if ($updatedMaterial->deleted_at !== null) {
                Log::error('Material accidentally soft-deleted during update, fixing', [
                    'material_id' => $material->id,
                    'deleted_at' => $updatedMaterial->deleted_at
                ]);
                
                // Force fix the deleted_at
                DB::table('materials')
                    ->where('id', $material->id)
                    ->update(['deleted_at' => null]);
                    
                $updatedMaterial = DB::table('materials')->where('id', $material->id)->first();
            }

            // Log successful update
            Log::info('Material Update Success', [
                'material_id' => $material->id,
                'user_id' => Auth::id(),
                'updated_fields' => array_keys($updateData),
                'material_still_active' => $updatedMaterial->deleted_at === null
            ]);

            DB::commit();

            // Get fresh model instance for redirect
            $material = Material::find($material->id);
            
            return redirect()->route('materials.show', $material)
                ->with('success', 'Materi berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if update failed
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui materi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        // Check if user is the owner of the material
        if ($material->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus materi ini.');
        }

        try {
            DB::beginTransaction();

            // Delete file from storage
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            // Use direct DB delete to avoid any model events
            DB::table('materials')->where('id', $material->id)->delete();

            DB::commit();

            return redirect()->route('materials.index')
                ->with('success', 'Materi berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus materi: ' . $e->getMessage());
        }
    }

    public function toggleSave(Material $material)
    {
        Auth::user()->savedMaterials()->toggle($material->id);
        return back()->with('success', 'Material saved status updated!');
    }

    public function download(Material $material)
    {
        $material->increment('downloads_count');

        $filePath = storage_path('app/public/' . $material->file_path);
        $downloadName = $material->original_filename ?? basename($material->file_path);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return response()->download($filePath, $downloadName);
    }

    public function saved()
    {
        $materials = Auth::user()->savedMaterials()->with(['course', 'user'])->paginate(10);
        return view('materials.saved', compact('materials'));
    }
}