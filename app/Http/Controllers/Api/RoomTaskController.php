<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\RoomTask;
use App\Models\Task;
use App\Models\UserRoomTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoomTaskController extends Controller
{
    // For setting up tasks in rooms
    public function getAllTasks()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }

    public function index(Request $request)
    {
        $query = RoomTask::with('task');

        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $roomTasks = $query->get();
        return response()->json($roomTasks);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id'   => 'required|exists:classrooms,id',
            'task_id'   => 'required|exists:tasks,id',
            'amount'    => 'required|integer|min:1',
            'reward'    => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $roomTask = RoomTask::create($request->all());
        return response()->json($roomTask, 201);
    }

    public function show(string $id)
    {
        $roomTask = RoomTask::find($id);
        $roomTask->load('task');

        if (! $roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        return response()->json($roomTask);
    }

    public function update(Request $request, string $id)
    {
        $roomTask = RoomTask::find($id);

        if (! $roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount'    => 'integer|min:1',
            'reward'    => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $roomTask->update($request->all());
        return response()->json($roomTask);
    }

    public function destroy(string $id)
    {
        $roomTask = RoomTask::find($id);
        if (! $roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        $roomTask->delete();
        return response()->json(['message' => 'Room Task deleted successfully']);
    }

    public function getStudentsRoomTasksProgress(Classroom $classroom)
    {
        // Get all enrolled students in the classroom
        $enrolledStudents = $classroom->classEnrollments()
            ->with('user:id,name,email')
            ->get()
            ->pluck('user')
            ->filter(); // Remove null users (in case email doesn't match)

        // Get all active room tasks with all user progress
        $roomTasks = RoomTask::where('room_id', $classroom->id)
            ->where('is_active', true)
            ->with(['task', 'userRoomTasks.user:id,name,email'])
            ->orderBy('amount', 'asc')
            ->get()
            ->groupBy('task.code')
            ->map(fn($g) => $g->first())
            ->values();

        $result = $roomTasks->map(function ($roomTask) use ($enrolledStudents) {
            // Map all students with their progress for this task
            $studentsProgress = $enrolledStudents->map(function ($student) use ($roomTask) {
                $userProgress = $roomTask->userRoomTasks
                    ->where('user_id', $student->id)
                    ->first();

                return [
                    'user' => [
                        'id'    => $student->id,
                        'name'  => $student->name,
                        'email' => $student->email,
                    ],
                    'progress' => $userProgress ? [
                        'score'        => $userProgress->score,
                        'is_completed' => $userProgress->is_completed,
                        'completed_at' => $userProgress->completed_at,
                    ] : [
                        'score'        => 0,
                        'is_completed' => false,
                        'completed_at' => null,
                    ],
                ];
            });

            // Calculate summary stats
            $completedCount = $studentsProgress->filter(fn($s) => $s['progress']['is_completed'])->count();
            $totalStudents = $enrolledStudents->count();

            return [
                'id'                => $roomTask->id,
                'room_id'           => $roomTask->room_id,
                'task'              => $roomTask->task,
                'amount'            => $roomTask->amount,
                'reward'            => $roomTask->reward,
                'is_active'         => $roomTask->is_active,
                'completion_rate'   => $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 1) : 0,
                'completed_count'   => $completedCount,
                'total_students'    => $totalStudents,
                'students_progress' => $studentsProgress,
            ];
        });

        return response()->json($result);
    }

    public function increamentUserRoomTask(Request $request, RoomTask $roomTask)
    {
        $userId = Auth::id();

        if (! $roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        $userRoomTask = UserRoomTask::firstOrNew([
            'user_id'      => $userId,
            'room_task_id' => $roomTask->id,
        ]);

        if ($request->has('increment')) {
            $userRoomTask->score += $request->increment;
        } else {
            $userRoomTask->score += 1;
        }

        $userRoomTask->score = $userRoomTask->score > $roomTask->amount ? $roomTask->amount : $userRoomTask->score;

        if ($request->has('is_completed')) {
            $userRoomTask->is_completed = $request->is_completed;
            if ($request->is_completed && ! $userRoomTask->completed_at) {
                $userRoomTask->completed_at = now();
            }
        }

        if ($userRoomTask->score >= $roomTask->amount) {
            $userRoomTask->is_completed = true;
            if (! $userRoomTask->completed_at) {
                $userRoomTask->completed_at = now();
            }
        }

        $userRoomTask->save();
        $userRoomTask->load('roomTask');
        return response()->json($userRoomTask);
    }

    public function getAllStudentActiveRoomTasks(Classroom $classroom)
    {
        $userId = Auth::id();
        
        $roomTasks = RoomTask::where('room_id', $classroom->id)
            ->where('is_active', true)
            ->with(['task', 'userRoomTasks' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->orderBy('amount', 'asc')
            ->get()
            ->groupBy('task.code')
            ->values();

        return response()->json($roomTasks);
    }

    public function getActiveRoomTasksWithProgress(Classroom $classroom)
    {
        $userId = Auth::id();

        $roomTasks = RoomTask::where('room_id', $classroom->id)
            ->where('is_active', true)

            ->whereDoesntHave('userRoomTasks', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where('is_completed', true);
            })

            ->with(['task', 'userRoomTasks' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])

            ->orderBy('amount', 'asc')
            ->get()
            ->groupBy('task.code')
            ->map(fn($g) => $g->first())
            ->values();

        $result = $roomTasks->map(function ($roomTask) {
            $userProgress = $roomTask->userRoomTasks->first();

            return [
                'id'            => $roomTask->id,
                'room_id'       => $roomTask->room_id,
                'task'          => $roomTask->task,
                'amount'        => $roomTask->amount,
                'reward'        => $roomTask->reward,
                'is_active'     => $roomTask->is_active,
                'user_progress' => $userProgress ? [
                    'score'        => $userProgress->score,
                    'is_completed' => $userProgress->is_completed,
                    'completed_at' => $userProgress->completed_at,
                ] : null,
            ];
        });

        return response()->json($result);
    }
}
