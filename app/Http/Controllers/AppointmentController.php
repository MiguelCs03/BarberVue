<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Appointments/Index');
    }

    public function create()
    {
        return Inertia::render('Appointments/Create');
    }

    public function store(Request $request)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('appointments.index');
    }

    public function show(string $id)
    {
        return Inertia::render('Appointments/Show');
    }

    public function edit(string $id)
    {
        return Inertia::render('Appointments/Edit');
    }

    public function update(Request $request, string $id)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('appointments.index');
    }

    public function destroy(string $id)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('appointments.index');
    }
}
