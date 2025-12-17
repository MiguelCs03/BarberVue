<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'apellido' => 'nullable|string|max:100',
            'email'    => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'telefono' => 'nullable|string|max:40',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'     => $request->name,
                'apellido' => $request->apellido,
                'email'    => $request->email,
                'telefono' => $request->telefono,
                'password' => Hash::make($request->password),
                'rol'      => 'cliente'
            ]);

            Cliente::create([
                'id' => $user->id,
            ]);

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect(route('dashboard', absolute: false));

        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'No se pudo completar el registro: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
