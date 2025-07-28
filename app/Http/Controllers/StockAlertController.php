<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function index()
    {
        $alerts = StockAlert::with(['product', 'supplierInventory'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stock_alerts.index', compact('alerts'));
    }

    public function peluqueria()
    {
        $alerts = StockAlert::with(['product', 'supplierInventory'])
            ->where('inventory_type', 'peluqueria')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stock_alerts.peluqueria', compact('alerts'));
    }

    public function distribuidora()
    {
        $alerts = StockAlert::with(['product', 'supplierInventory'])
            ->where('inventory_type', 'distribuidora')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stock_alerts.distribuidora', compact('alerts'));
    }

    public function markAsRead(StockAlert $alert)
    {
        $alert->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        StockAlert::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function markAllAsReadByType(Request $request)
    {
        $type = $request->input('type');
        
        StockAlert::where('is_read', false)
            ->where('inventory_type', $type)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function destroy(StockAlert $alert)
    {
        $alert->delete();
        
        return redirect()->back()
            ->with('success', 'Alerta eliminada correctamente');
    }

    public function getUnreadCount()
    {
        $count = StockAlert::where('is_read', false)->count();
        
        return response()->json(['count' => $count]);
    }

    public function getUnreadCountPeluqueria()
    {
        $count = StockAlert::where('is_read', false)
            ->where('inventory_type', 'peluqueria')
            ->count();
        
        return response()->json(['count' => $count]);
    }

    public function getUnreadCountDistribuidora()
    {
        $count = StockAlert::where('is_read', false)
            ->where('inventory_type', 'distribuidora')
            ->count();
        
        return response()->json(['count' => $count]);
    }
}
