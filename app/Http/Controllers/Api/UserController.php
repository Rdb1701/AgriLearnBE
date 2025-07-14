<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userAuth = Auth::user();
        $user = User::query()
            ->where('added_by', $userAuth->id)
            ->orderBy('name', "asc")
            ->paginate(10);

        return response()->json($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['role']     = "Student";
        $data['isActive'] = true;
        $data['added_by'] = Auth::user()->id;
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        return response()->json($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $data['added_by'] = Auth::user()->id;
        $data['role']     = "Student";
        $data['isActive'] = true;

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        
       return response(201);
    }

    public function deactivate(User $user)
    {
        $data =  $user->update([
            'isActive' => false
        ]);

        if ($data) {
            return response(201);
        } else {
            return response()->json([
                'message' => "error in deactivating account",
            ]);
        }
    }
}
