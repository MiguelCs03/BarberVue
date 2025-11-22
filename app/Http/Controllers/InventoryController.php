<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Index');
    }

    public function create()
    {
        return Inertia::render('Inventory/Create');
    }

    public function store(Request $request)
    {
        // TODO: Implement when backend is ready
        return redirect()->route('inventory.index');
    }

    public function show(string $id)
    {
        return Inertia::render('Inventory/Show');
    }
}
