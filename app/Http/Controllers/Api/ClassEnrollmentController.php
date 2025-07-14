<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendStudentsEmail;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClassEnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'        => 'required|string|max:255|unique:class_enrollments,email',
            'classroom_id' => 'required|max:255'
        ]);

        $classEnrollment =  ClassEnrollment::create([
            'email'        => $data['email'],
            'classroom_id' => $data['classroom_id'],
            'status'       => false
        ]);

        $classroom = \App\Models\Classroom::find($data['classroom_id']);
        $classroomName = $classroom->class_name ?? 'Your Class';

        $inviteLink = url('/join-classroom/' . $classroom->id . '?email=' . urlencode($data['email']));

        Mail::to($data['email'])->send(new SendStudentsEmail($classroomName, $inviteLink));

        return response()->json([
            $classEnrollment,
            'message' => 'Successfully Invited a Student'
        ]);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassEnrollment $classEnrollment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassEnrollment $classEnrollment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassEnrollment $classEnrollment)
    {
        //
    }
}
