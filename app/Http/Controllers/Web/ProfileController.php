<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the user profile.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        return view('profile.index', compact('user'));
    }

    /**
     * Update the user profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('profile.index')->with('success', 'Perfil atualizado com sucesso!');
    }
} 