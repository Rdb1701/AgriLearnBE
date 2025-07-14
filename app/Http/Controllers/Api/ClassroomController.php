<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Http\Resources\ClassroomResource;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $classrooms = Classroom::query()->where('instructor_id', $user->id)->get();

        return response()->json($classrooms);
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
}
