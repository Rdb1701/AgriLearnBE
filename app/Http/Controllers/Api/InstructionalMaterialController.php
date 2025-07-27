<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstructionalMaterial;
use App\Http\Requests\StoreInstructionalMaterialRequest;
use App\Http\Requests\UpdateInstructionalMaterialRequest;

class InstructionalMaterialController extends Controller
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
    public function store(StoreInstructionalMaterialRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(InstructionalMaterial $instructionalMaterial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstructionalMaterialRequest $request, InstructionalMaterial $instructionalMaterial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InstructionalMaterial $instructionalMaterial)
    {
        //
    }
}
