<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class EcommerceUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('ecommerce-users.index', compact('users'));
    }
}
