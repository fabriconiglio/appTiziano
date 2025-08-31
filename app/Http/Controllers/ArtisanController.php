<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ArtisanController extends Controller
{
    /**
     * Ejecutar comandos Artisan permitidos
     */
    public function executeCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = $request->input('command');
        
        // Lista de comandos permitidos por seguridad
        $allowedCommands = [
            'distributor:export-all-pdfs'
        ];

        if (!in_array($command, $allowedCommands)) {
            return response()->json([
                'success' => false,
                'message' => 'Comando no permitido'
            ], 403);
        }

        try {
            // Ejecutar el comando
            $exitCode = Artisan::call($command);
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comando ejecutado correctamente',
                    'output' => Artisan::output()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al ejecutar el comando',
                    'exitCode' => $exitCode
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Error ejecutando comando Artisan: ' . $e->getMessage(), [
                'command' => $command
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
} 