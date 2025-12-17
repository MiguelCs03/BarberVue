<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/citas-admin/index',[CitaController::class,'indexAdministrativo'])->name('citas-admin.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas para CLIENTES
    Route::middleware('role:cliente')->group(function () {
        
        Route::get('/citas-cliente/index',[CitaController::class,'indexCliente'])->name('citas-cliente.index');
        Route::get('/citas-cliente/{id}/show',[CitaController::class,'showCliente'])->name('citas-cliente.show');
        Route::get('/citas-cliente/{id}/edit',[CitaController::class,'editCliente'])->name('citas-cliente.edit');
        Route::get('/citas-cliente/create',[CitaController::class,'create'])->name('citas-cliente.create');
        Route::post('/cita/store',[CitaController::class, 'store'])->name('citas-cliente.store');
        Route::put('/cita-cliente/{id}/update',[CitaController::class, 'updateCliente'])->name('citas-cliente.update');
    });
    Route::post('/cita/{id}/cancelar',[CitaController::class, 'cancelarCita'])->name('citas.cancelar-cita');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });
    // Rutas para BARBEROS/ADMINISTRADORES
    Route::middleware('role:barbero')->group(function () {
        // User Management Routes
        
        // Product Management Routes
        Route::resource('products', ProductController::class);
        
        // Service Management Routes
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
        Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // Citas para administrativos
        #Route::get('/citas-admin/index',[CitaController::class,'indexAdministrativo'])->name('citas-admin.index');
        Route::get('/citas-admin/{id}/show',[CitaController::class,'showAdministrativo'])->name('citas-admin.show');
        // Route::get('/citas-admin/{id}/edit',[CitaController::class,'showAdministrativo'])->name('citas-admin.edit');
        Route::get('/citas-admin/{id}/edit',[CitaController::class,'editAdmin'])->name('citas-admin.edit');
        Route::get('/citas-admin/create',[CitaController::class,'createAdmin'])->name('citas-admin.create');
        Route::post('/cita-admin/store',[CitaController::class, 'storeAdmin'])->name('citas-admin.store');
    
        Route::put('/cita-admin/{id}/update', [CitaController::class, 'updateAdmin'])->name('citas-admin.update');
        // Inventory Management Routes
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');
        
        // Appointment Management Routes
        Route::resource('appointments', AppointmentController::class);
        
        // Analytics Route
        Route::get('/analytics/visits', function () {
            return Inertia::render('Analytics/Visits');
        })->name('analytics.visits');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Horarios Management Routes
        Route::resource('horarios', \App\Http\Controllers\HorarioBarberoController::class);
        Route::post('/horarios/excepciones', [\App\Http\Controllers\HorarioBarberoController::class, 'storeExcepcion'])->name('horarios.excepciones.store');
        Route::delete('/horarios/excepciones/{id}', [\App\Http\Controllers\HorarioBarberoController::class, 'destroyExcepcion'])->name('horarios.excepciones.destroy');
    });
    //jest
    // Rutas compartidas (disponibles para barbero y cliente)
    Route::post('/citas/barberos-disponibles', [CitaController::class, 'getBarberosDisponiblesV2'])
        ->name('barberos-disponibles');

    Route::post('/citas/barberos-disponibles-admin', [CitaController::class, 'getBarberosDisponiblesAdmin'])
        ->name('barberos-disponibles-admin');

    Route::post('/citas/barberos-disponibles-edicion-cliente', [CitaController::class, 'getBarberosDisponiblesParaEdicion'])
        ->name('barberos-disponibles-edicion');

    Route::get('/usuarios/busqueda-cliente-username', [UserController::class, 'buscarUsuarioPorUsername'])
        ->name('usuarios.busqueda-cliente-username');
    Route::get('/usuarios/busqueda-cliente-email', [UserController::class, 'buscarUsuarioPorEmail'])
        ->name('usuarios.busqueda-cliente-email');
    
    // Global Search Route
    Route::get('/api/search', [SearchController::class, 'search'])->name('search');
    
    // Theme Test Route
    Route::get('/theme-test', function () {
        return Inertia::render('ThemeTest');
    })->name('theme.test');
});


require __DIR__.'/auth.php';


