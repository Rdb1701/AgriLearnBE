<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendStudentsEmail;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClassEnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $classroom_id = $request->query('classroom_id');

        $user = Auth::user();

        if ($user->role === "Instructor") {
            $class_enrollments = DB::table('class_enrollments as ce')
                ->leftJoin('classrooms as c', 'c.id', 'ce.classroom_id')
                ->leftJoin('users as us', 'us.email', 'ce.email')
                ->select('ce.*', 'us.name')
                ->orderBy('email', 'ASC')
                ->where('c.instructor_id', Auth::id())
                ->where('c.id', $classroom_id)
                ->get();
        } else {
            $class_enrollments = DB::table('class_enrollments as ce')
                ->leftJoin('classrooms as c', 'c.id', 'ce.classroom_id')
                ->leftJoin('users as us', 'us.email', 'ce.email')
                ->select('ce.*', 'us.name')
                ->orderBy('email', 'ASC')
                ->where('c.id', $classroom_id)
                ->get();
        }

        return response()->json($class_enrollments);
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

        $classroom =Classroom::find($data['classroom_id']);
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

            // if email is globally unique (not per classroom), this avoids DB exception
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



    public function joinViaCode(Request $request)
    {

        $request->validate([
            'section_code' => 'required|string',
        ]);

        $invite_code = $request->input('section_code');
        $email       = $request->input('email');

        // check if classroom exists with the code
        $classroom = Classroom::where('section_code', $invite_code)->first();

        if (!$classroom) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invite code.',
            ], 404);
        }

        //get the classroom_id
        $classroom_id = $classroom->id;

        // check if already enrolled
        $alreadyEnrolled = ClassEnrollment::where('email', $email)
            ->where('classroom_id', $classroom_id)
            ->first();

        if ($alreadyEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this class.',
            ], 409);
        }

        //enroll
        $enrollClass = ClassEnrollment::create([
            'email'        => $email,
            'classroom_id' => $classroom_id,
            'status'       => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enrolled successfully.',
            'data' => $enrollClass,
        ]);
    }


    public function getInstructor($classroom_id)
    {
        $user = Auth::user();

        $query = DB::table('class_enrollments as ce')
            ->leftJoin('classrooms as c', 'c.id', 'ce.classroom_id')
            ->leftJoin('users as u', 'u.id', 'c.instructor_id')
            ->select('u.name', 'u.id')
            ->where('c.id', $classroom_id)
            ->first();

        return response()->json($query);
    }




    public function getEnrollmentStatus()
    {

        $user = Auth::user();
        $email = $user->email;

        $classEnrollmentVerification = ClassEnrollment::query()->where('email', $email)->select('status', 'classroom_id')->get();


        return response()->json($classEnrollmentVerification);
    }


    public function acceptEnrollment($classroom_id)
    {
        $user = Auth::user();
        $email = $user->email;

        $acceptEnrollment = ClassEnrollment::where('classroom_id', $classroom_id)
            ->where('email', $email)
            ->update(['status' => true]);


        if ($acceptEnrollment) {
            return response()->json(['message' => 'Subject accepted successfully.']);
        } else {
            return response()->json(['message' => 'No Subject found to update.'], 404);
        }
    }


    public function rejectEnrollment($classroom_id)
    {
        $user = Auth::user();
        $email = $user->email;

        $acceptEnrollment = ClassEnrollment::where('classroom_id', $classroom_id)
            ->where('email', $email)
            ->delete();


        if ($acceptEnrollment) {
            return response()->json(['message' => 'Subject rejected successfully.']);
        } else {
            return response()->json(['message' => 'No Subject found to delete.'], 404);
        }
    }
}
