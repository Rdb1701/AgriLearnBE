<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\ClassEnrollment;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $classrooms = Classroom::query()->where('status', true)->where('instructor_id', $user->id)->get();

        return response()->json($classrooms);
    }


    public function getArchive()
    {
        $user = Auth::user();
        $classrooms = Classroom::query()->where('status', false)->where('instructor_id', $user->id)->get();

        return response()->json($classrooms);
    }

    public function getClassroomStatus($classroom_id)
    {
        $classroom = Classroom::query()->where('id', $classroom_id)->first();

        return response()->json($classroom);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassroomRequest $request)
    {

        $data = $request->validated();

        //if not authenticated
        if (!Auth::user()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $classroom = Classroom::create($data);

        return response()->json($classroom);
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $classroom)
    {
        return new ClassroomResource($classroom);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        $data = $request->validated();

        $data['instructor_id'] = Auth::user()->id;

        //if not authenticated
        if (!Auth::user()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $classroom->update($data);

        return new ClassroomResource($classroom);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return response()->json([
            'message' => "Successfully Deleted"
        ]);
    }


    public function getStudentClass()
    {

        $user = Auth::user();
        $email = $user->email;

        //dd($email);

        $classrooms = ClassEnrollment::with('classroom')
            ->where('email', $email)
            ->whereHas('classroom', function ($query) {
                $query->where('status', true);
            })
            //->where('status', true)
            ->get()
            ->pluck('classroom')
            ->filter()
            ->values();

        return response()->json($classrooms);
    }


    public function archiveClass($classroom_id)
    {
        $user = Auth::user();

        $archive = Classroom::where('id', $classroom_id)
            ->update(['status' => false]);


        if ($archive) {
            return response()->json(['message' => 'Subject udpated successfully.']);
        } else {
            return response()->json(['message' => 'No Subject found to delete.'], 404);
        }
    }

    public function toggleSimulationStatus($classroom_id)
    {
        $classroom = Classroom::find($classroom_id);

        if (! $classroom) {
            return response()->json(['message' => 'Classroom not found'], 404);
        }

        $classroom->is_simulation_on = ! $classroom->is_simulation_on;
        $classroom->save();

        return response()->json([
            'message' => 'Simulation status updated successfully',
            'is_simulation_on' => $classroom->is_simulation_on,
        ]);
    }

    public function isSimulationOn($classroom_id)
    {
        $classroom = Classroom::find($classroom_id);

        if (! $classroom) {
            return response()->json(['message' => 'Classroom not found'], 404);
        }

        return response()->json([
            'is_simulation_on' => $classroom->is_simulation_on,
        ]);
    }
}
