<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class UserController extends Controller
{
    // public function search(Request $request)
    // {
    //     $searchTerm = $request->query('search');

    //     $users = User::where('id', '!=', auth()->id())
    //         ->where('name', 'LIKE', "%$searchTerm%")
    //         ->get();

    //     return response()->json($users);
    // }

    

    // Handles the users online status
    public function updateLastSeen() {
        auth()->user()->update(['last_seen' => now()]);
        Log::info('User last seen updated');
        return response()->json(['message' => 'Last seen updated successfully']);
    }

    public function index()
    {
        // Fetch all users except the currently logged-in user
        $users = User::where('id', '!=', auth()->id())->get();
    
        // Return the users to the Vue frontend using Inertia
        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }
}
