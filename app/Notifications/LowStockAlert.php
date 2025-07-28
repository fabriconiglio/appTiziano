<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $product;
    public $currentStock;
    public $threshold;
    public $inventoryType;

    /**
     * Create a new notification instance.
     */
    public function __construct($product, int $currentStock, int $threshold = 5, string $inventoryType = 'peluqueria')
    {
        $this->product = $product;
        $this->currentStock = $currentStock;
        $this->threshold = $threshold;
        $this->inventoryType = $inventoryType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $productName = $this->inventoryType === 'peluqueria' ? $this->product->name : $this->product->product_name;
        $routeName = $this->inventoryType === 'peluqueria' ? 'products.edit' : 'supplier-inventories.edit';
        
        return (new MailMessage)
            ->subject('🚨 Alerta de Stock Bajo - ' . $productName . ' (' . ucfirst($this->inventoryType) . ')')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Se ha detectado que un producto tiene stock bajo.')
            ->line('**Inventario:** ' . ucfirst($this->inventoryType))
            ->line('**Producto:** ' . $productName)
            ->line('**Stock Actual:** ' . $this->currentStock . ' unidades')
            ->line('**Umbral de Alerta:** ' . $this->threshold . ' unidades')
            ->action('Ver Producto', url('/' . ($this->inventoryType === 'peluqueria' ? 'products' : 'supplier-inventories') . '/' . $this->product->id . '/edit'))
            ->line('Por favor, revisa el inventario y realiza los pedidos necesarios.')
            ->salutation('Saludos, Sistema de Alertas Tiziano');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $productName = $this->inventoryType === 'peluqueria' ? $this->product->name : $this->product->product_name;
        return [
            'product_id' => $this->product->id,
            'product_name' => $productName,
            'inventory_type' => $this->inventoryType,
            'current_stock' => $this->currentStock,
            'threshold' => $this->threshold,
            'message' => "Stock bajo en {$productName} ({$this->inventoryType}): {$this->currentStock} unidades restantes"
        ];
    }
}
