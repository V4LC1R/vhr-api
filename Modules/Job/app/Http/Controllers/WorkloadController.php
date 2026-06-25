<?php

namespace Modules\Job\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Job\Http\Requests\Workload\StoreWorkloadRequest;

class WorkloadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('job::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('job::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkloadRequest $request)
    {
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('job::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('job::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }
}
