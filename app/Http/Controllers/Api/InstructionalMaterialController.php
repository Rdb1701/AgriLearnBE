<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstructionalMaterial;
use App\Http\Requests\StoreInstructionalMaterialRequest;
use App\Http\Requests\UpdateInstructionalMaterialRequest;
use App\Http\Resources\InstructionMaterialResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstructionalMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    public function getMaterialByClassroom($classroom_id)
    {
        $materials = DB::table('instructional_materials as m')
            ->leftJoin('classrooms as c', 'c.id', 'm.classroom_id')
            ->leftJoin('users as u', 'u.id', 'c.instructor_id')
            ->select('m.*', 'u.name')
            ->where('classroom_id', $classroom_id)
            ->distinct()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($materials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstructionalMaterialRequest $request)
    {
        $files = $request->file('files');
        $filePaths = [];
        $fileTypes = [];

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $uniqueName = time() . '_' . Str::random(5) . '_' . $originalName;
            $path = $file->storeAs('instructional_materials', $uniqueName, 'public');

            $filePaths[] = $path;
            $fileNames[] = $originalName;
            $fileTypes[] = $file->getClientOriginalExtension();
        }


        $material = InstructionalMaterial::create([
            'classroom_id' => $request->input('classroom_id'),
            'uploaded_by'  => $request->input('uploaded_by'),
            'title'        => $request->input('title'),
            'description'  => $request->input('description'),
            'file_type'    => json_encode($fileTypes),
            'file_path'    => json_encode($filePaths),
            'isOffline'    => $request->input('isOffline', false),
        ]);

        return response()->json([
            'message'   => 'Instructional materials uploaded successfully.',
            'materials' => $material
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(InstructionalMaterial $material)
    {
        return new InstructionMaterialResource($material);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstructionalMaterialRequest $request, InstructionalMaterial $material)
    {
        try {

            $material->title = $request->input('title');
            $material->description = $request->input('description');

            // Get current files
            $currentFilePaths = json_decode($material->file_path, true) ?? [];
            $currentFileTypes = json_decode($material->file_type, true) ?? [];


            if ($request->has('delete_files')) {
                $filesToDelete = $request->input('delete_files');

                foreach ($filesToDelete as $fileToDelete) {

                    $index = array_search($fileToDelete, $currentFilePaths);

                    if ($index !== false) {

                        if (Storage::disk('public')->exists($fileToDelete)) {
                            Storage::disk('public')->delete($fileToDelete);
                        }


                        unset($currentFilePaths[$index]);
                        unset($currentFileTypes[$index]);
                    }
                }

                // Re-index after deletion
                $currentFilePaths = array_values($currentFilePaths);
                $currentFileTypes = array_values($currentFileTypes);
            }

            // Handle new file uploads
            if ($request->hasFile('new_files')) {
                $newFiles = $request->file('new_files');

                foreach ($newFiles as $file) {
                    $path = $file->store('instructional_materials', 'public');
                    $currentFilePaths[] = $path;
                    $currentFileTypes[] = $file->getClientOriginalExtension();
                }
            }

            // Ensure we have at least one file
            if (empty($currentFilePaths)) {
                return response()->json([
                    'message' => 'At least one file is required.',
                    'errors' => [
                        'files' => ['At least one file is required.']
                    ]
                ], 422);
            }

            // Update file arrays in database
            $material->file_path = json_encode($currentFilePaths);
            $material->file_type = json_encode($currentFileTypes);

            $material->save();

            return response()->json([
                'message' => 'Instructional material updated successfully.',
                'material' => new InstructionMaterialResource($material)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating instructional material: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update instructional material.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InstructionalMaterial $material)
    {
        try {
            // Get file paths to delete from storage
            $filePaths = json_decode($material->file_path, true) ?? [];

            // Delete files from storage
            foreach ($filePaths as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            // Delete the database record
            $material->delete();

            return response()->json([
                'message' => 'Instructional material deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting instructional material: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete instructional material.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
