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
            'room_id' => 'required|exists:classrooms,id',
            'task_id' => 'required|exists:tasks,id',
            'amount' => 'required|integer|min:1',
            'reward' => 'required|integer|min:0',
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

        if (!$roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        return response()->json($roomTask);
    }

    public function update(Request $request, string $id)
    {
        $roomTask = RoomTask::find($id);

        if (!$roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'integer|min:1',
            'reward' => 'integer|min:0',
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
        if (!$roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        $roomTask->delete();
        return response()->json(['message' => 'Room Task deleted successfully']);
    }

    public function getUserRoomTasks(Classroom $classroom)
    {

        $userId = Auth::id();

        $roomTasks = RoomTask::where('room_id', $classroom->id)
            ->where('is_active', true)
            ->with(['task' => function ($query) use ($userId) {
                $query->where('is_completed', false);
            }])
            ->get();

        $result = $roomTasks->map(function ($roomTask) {
            $userProgress = $roomTask->userRoomTasks->first();
            
            return [
                'id' => $roomTask->id,
                'room_id' => $roomTask->room_id,
                'task' => $roomTask->task,
                'amount' => $roomTask->amount,
                'reward' => $roomTask->reward,
                'is_active' => $roomTask->is_active,
                'user_progress' => $userProgress ? [
                    'score' => $userProgress->score,
                    'is_completed' => $userProgress->is_completed,
                    'completed_at' => $userProgress->completed_at,
                ] : null,
            ];
        });

        return response()->json($result);
    }

    public function updateUserRoomTask(Request $request, RoomTask $roomTask)
    {
        $userId = Auth::id();
        
        $validator = Validator::make($request->all(), [
            'score' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$roomTask) {
            return response()->json(['message' => 'Room Task not found'], 404);
        }

        $userRoomTask = UserRoomTask::firstOrNew([
            'user_id' => $userId,
            'room_task_id' => $roomTask->id,
        ]);

        $userRoomTask->score = $request->score > $roomTask->amount ? $roomTask->amount : $request->score;
        
        if ($request->has('is_completed')) {
            $userRoomTask->is_completed = $request->is_completed;
            if ($request->is_completed && !$userRoomTask->completed_at) {
                $userRoomTask->completed_at = now();
            }
        }

        if ($userRoomTask->score >= $roomTask->amount) {
            $userRoomTask->is_completed = true;
            if (!$userRoomTask->completed_at) {
                $userRoomTask->completed_at = now();
            }
        }

        $userRoomTask->save();

        return response()->json($userRoomTask);
    }
}
