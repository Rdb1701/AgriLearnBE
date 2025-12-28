<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\GameSave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    /**
     * Save game data for authenticated user in a classroom
     */
    public function save(Request $request, Classroom $classroom)
    {
        Log::info('Game Save Request:', $request->all());
        try {
            $validated = $request->validate([
                'character'               => 'required|string',
                'map_name'                => 'required|string',

                'player_position'         => 'required|array',
                'player_position.x'       => 'required|numeric',
                'player_position.y'       => 'required|numeric',

                'stamina'                 => 'required|array',
                'stamina.max_stamina'     => 'required|numeric|min:0',
                'stamina.current_stamina' => 'required|numeric|min:0',
                'money'                   => 'required|array',
                'money.gold'              => 'required|integer|min:0',
                'farm'                    => 'required|array',
                'farm.dirt_tiles'         => 'nullable|array',
                'farm.wet_tiles'          => 'nullable|array',
                'farm.crops'              => 'nullable|array',
                'inventory'               => 'required|array',
                'date_time'               => 'required|array',
                'date_time.seconds'       => 'required|integer|min:0|max:59',
                'date_time.minutes'       => 'required|integer|min:0|max:59',
                'date_time.hours'         => 'required|integer|min:0|max:23',
                'date_time.days'          => 'required|integer|min:0',
            ]);

            $user = Auth::user();

            // if (!$user->classrooms()->where('id', $classroomId)->exists()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'You do not belong to this classroom'
            //     ], 403);
            // }

            $saveData = $validated;

            $gameSave = GameSave::updateOrCreate(
                [
                    'user_id'      => $user->id,
                    'classroom_id' => $classroom->id,
                ],
                [
                    'save_data' => $saveData,
                ]
            );

            return response()->json([
                'success'      => true,
                'message'      => 'Game saved successfully',
                'saved_at'     => $gameSave->updated_at->toDateTimeString(),
                'classroom_id' => $classroom->id,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save game data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Load game data for authenticated user in a classroom
     */
    public function load(Classroom $classroom)
    {
        try {
            $user = Auth::user();

            $gameSave = GameSave::where('user_id', $user->id)
                ->where('classroom_id', $classroom->id)
                ->first();

            if (! $gameSave) {
                return response()->json([
                    'success' => false,
                    'message' => 'No save data found for this classroom',
                ], 404);
            }

            return response()->json([
                'success'      => true,
                'data'         => $gameSave->save_data,
                'saved_at'     => $gameSave->updated_at->toDateTimeString(),
                'classroom_id' => $gameSave->classroom_id,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load game data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete game save for authenticated user in a classroom
     */
    public function delete(Classroom $classroom)
    {
        try {
            $user = Auth::user();

            $deleted = GameSave::where('user_id', $user->id)
                ->where('classroom_id', $classroom->id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Save data deleted successfully',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No save data found to delete',
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete save data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all saves for authenticated user across all classrooms
     */
    public function getAllSaves()
    {
        try {
            $user = Auth::user();

            $gameSaves = GameSave::where('user_id', $user->id)
                ->with('classroom:id,name') // Load classroom name
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($save) {
                    return [
                        'id'             => $save->id,
                        'classroom_id'   => $save->classroom_id,
                        'classroom_name' => $save->classroom->name ?? 'Unknown',
                        'last_saved'     => $save->updated_at->diffForHumans(),
                        'saved_at'       => $save->updated_at->toDateTimeString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'saves'   => $gameSaves,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saves',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user has a save in a classroom
     */
    public function hasSave(Classroom $classroom)
    {
        try {
            $user = Auth::user();

            $exists = GameSave::where('user_id', $user->id)
                ->where('classroom_id', $classroom->id)
                ->exists();

            return response()->json([
                'success'      => true,
                'has_save'     => $exists,
                'classroom_id' => $classroom->id,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check save status',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
