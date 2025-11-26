<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Your logic to fetch enquiries
        return view('enquiry.index'); // Create this view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('enquiry.create'); // Create this view
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Your store logic
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Your show logic
        return view('enquiry.show'); // Create this view
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('enquiry.edit'); // Create this view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Your update logic
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Your delete logic
    }
}