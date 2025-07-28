<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Obtener alertas de stock no leídas para peluquería
        $peluqueriaAlerts = StockAlert::where('is_read', false)
            ->where('inventory_type', 'peluqueria')
            ->count();

        // Obtener alertas de stock no leídas para distribuidora
        $distribuidoraAlerts = StockAlert::where('is_read', false)
            ->where('inventory_type', 'distribuidora')
            ->count();

        return view('home', compact('peluqueriaAlerts', 'distribuidoraAlerts'));
    }
}
