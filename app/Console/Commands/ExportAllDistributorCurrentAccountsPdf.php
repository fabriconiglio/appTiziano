<?php

namespace App\Console\Commands;

use App\Models\DistributorClient;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ExportAllDistributorCurrentAccountsPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'distributor:export-all-pdfs {--output=storage/app/exports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exportar todas las cuentas corrientes de distribuidores a PDF';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputPath = $this->option('output');
        
        // Crear directorio si no existe
        if (!Storage::exists($outputPath)) {
            Storage::makeDirectory($outputPath);
        }

        $this->info('Iniciando exportación de cuentas corrientes a PDF...');
        
        $distributorClients = DistributorClient::orderBy('name')->orderBy('surname')->get();
        $totalClients = $distributorClients->count();
        
        $this->info("Total de distribuidores a procesar: {$totalClients}");
        
        $bar = $this->output->createProgressBar($totalClients);
        $bar->start();
        
        $exportedCount = 0;
        $errors = [];
        
        foreach ($distributorClients as $client) {
            try {
                $currentAccounts = $client->currentAccounts()
                    ->with(['user', 'distributorTechnicalRecord'])
                    ->orderBy('date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $currentBalance = $client->getCurrentBalance();
                $formattedBalance = $client->getFormattedBalance();

                // Calcular totales
                $totalDebts = $currentAccounts->where('type', 'debt')->sum('amount');
                $totalPayments = $currentAccounts->where('type', 'payment')->sum('amount');

                $data = [
                    'distributorClient' => $client,
                    'currentAccounts' => $currentAccounts,
                    'currentBalance' => $currentBalance,
                    'formattedBalance' => $formattedBalance,
                    'totalDebts' => $totalDebts,
                    'totalPayments' => $totalPayments,
                    'generatedAt' => now()->format('d/m/Y H:i:s')
                ];

                $pdf = Pdf::loadView('distributor_current_accounts.pdf', $data);
                
                $filename = 'cuenta_corriente_' . str_replace(' ', '_', $client->full_name) . '_' . now()->format('Y-m-d') . '.pdf';
                $filepath = $outputPath . '/' . $filename;
                
                Storage::put($filepath, $pdf->output());
                
                $exportedCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Error al exportar {$client->full_name}: " . $e->getMessage();
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Exportación completada!");
        $this->info("Archivos exportados: {$exportedCount}");
        $this->info("Directorio de salida: {$outputPath}");
        
        if (!empty($errors)) {
            $this->error("Errores encontrados:");
            foreach ($errors as $error) {
                $this->error("- {$error}");
            }
        }
        
        $this->info("Puedes encontrar todos los PDFs en: " . storage_path($outputPath));
        
        return Command::SUCCESS;
    }
} 