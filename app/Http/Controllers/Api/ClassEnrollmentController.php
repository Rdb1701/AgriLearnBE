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
            'emails'        => 'required|array',
            'emails.*'      => 'required|email|max:255',
            'classroom_id'  => 'required|exists:classrooms,id'
        ]);

        $classroom = \App\Models\Classroom::find($data['classroom_id']);
        $classroomName = $classroom->class_name ?? 'Your Class';

        $results = [];

        foreach ($data['emails'] as $email) {
            // Skip if already enrolled
            if (ClassEnrollment::where('email', $email)->where('classroom_id', $data['classroom_id'])->exists()) {
                $results[] = ['email' => $email, 'status' => 'already invited'];
                continue;
            }

            $classEnrollment = ClassEnrollment::create([
                'email'        => $email,
                'classroom_id' => $data['classroom_id'],
                'status'       => false
            ]);

            $inviteLink = url('/join-classroom/' . $classroom->id . '?email=' . urlencode($email));
            Mail::to($email)->send(new SendStudentsEmail($classroomName, $inviteLink));

            $results[] = ['email' => $email, 'status' => 'invited'];
        }

        return response()->json([
            'results' => $results,
            'message' => 'Bulk invitations processed.'
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
