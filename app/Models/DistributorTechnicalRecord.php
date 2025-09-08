<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorTechnicalRecord extends Model
{
    protected $fillable = [
        'distributor_client_id',
        'user_id',
        'purchase_date',
        'purchase_type',
        'total_amount',
        'final_amount',
        'advance_payment',
        'payment_method',
        'products_purchased',
        'observations',
        'photos',
        'next_purchase_notes'
    ];

    protected $casts = [
        'products_purchased' => 'array',
        'photos' => 'array',
        'purchase_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'advance_payment' => 'decimal:2'
    ];

    public function distributorClient()
    {
        return $this->belongsTo(DistributorClient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplierInventories()
    {
        return $this->belongsToMany(SupplierInventory::class, 'distributor_technical_record_supplier_inventory');
    }

    public function currentAccounts()
    {
        return $this->hasMany(DistributorCurrentAccount::class);
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }


}
