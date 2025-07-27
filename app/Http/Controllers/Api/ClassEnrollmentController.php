<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendStudentsEmail;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClassEnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $class_enrollments = DB::table('class_enrollments as ce')
            ->leftJoin('classrooms as c', 'c.id', 'ce.classroom_id')
            ->leftJoin('users as us', 'us.email', 'ce.email')
            ->select('ce.*', 'us.name')
            ->orderBy('email', 'ASC')
            ->where('c.instructor_id', Auth::id())
            ->get();

        return $class_enrollments;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $emailsInput = $request->input('emails');
        $emails = is_array($emailsInput) ? $emailsInput : [$emailsInput];

        $data = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id'
        ]);

        $classroom = \App\Models\Classroom::find($data['classroom_id']);
        $classroomName = $classroom->class_name ?? 'Your Class';

        $results = [];

        foreach ($emails as $email) {
            // Check if email already exists (globally or for this classroom)
            $alreadyEnrolled = ClassEnrollment::where('email', $email)
                ->where('classroom_id', $data['classroom_id'])
                ->exists();

            if ($alreadyEnrolled) {
                $results[] = ['email' => $email, 'status' => 'already invited'];
                continue;
            }

            // Try insert — if email is globally unique (not per classroom), this avoids DB exception
            try {
                ClassEnrollment::create([
                    'email'        => $email,
                    'classroom_id' => $data['classroom_id'],
                    'status'       => false,
                ]);

                $inviteLink = url('/join-classroom/' . $classroom->id . '?email=' . urlencode($email));
                // Mail::to($email)->send(new SendStudentsEmail($classroomName, $inviteLink));

                $results[] = ['email' => $email, 'status' => 'invited'];
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle duplicate or DB errors gracefully
                if ($e->getCode() == '23505') {
                    $results[] = ['email' => $email, 'status' => 'already exists'];
                } else {
                    $results[] = ['email' => $email, 'status' => 'error'];
                }
            }
        }

        return response()->json([
            'results' => $results,
            'message' => 'Invitations processed.'
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
        $classEnrollment->delete();

        return response(null, 204);
    }
}
