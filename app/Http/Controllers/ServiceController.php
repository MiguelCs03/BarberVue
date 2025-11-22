<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Services/Index');
    }

    public function create()
    {
        return Inertia::render('Services/Create');
    }

    public function store(Request $request)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('services.index');
    }

    public function show(string $id)
    {
        return Inertia::render('Services/Show');
    }

    public function edit(string $id)
    {
        return Inertia::render('Services/Edit');
    }

    public function update(Request $request, string $id)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('services.index');
    }

    public function destroy(string $id)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('services.index');
    }
}
