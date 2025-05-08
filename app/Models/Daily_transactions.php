<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class daily_transactions extends Model
{
    //

    use HasFactory;
    protected $table ='tbl_daily_transactions';

    protected $fillable = [
        'item_id',
        'transaction_id',
        'customer_id',
        'quantity',
        'unit',
        'transaction_date',
        'user_id',
    ];


    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            Log::info('Transaction Created: ', $transaction->toArray());
            self::updateInventory($transaction, 'deduct');
        });

        static::deleted(function ($transaction) {
            Log::info('Transaction Deleted: ', $transaction->toArray());
            self::updateInventory($transaction, 'revert');
        });
    }

    private static function updateInventory($transaction, $action)
    {
        Log::info("updateInventory called for action: $action", ['transaction_id' => $transaction->transaction_id]);

        $inventory = Daily_inventory::where('stock_id', $transaction->item_id)
            ->where('transaction_date', $transaction->transaction_date)
            ->first();

        if (!$inventory) {
             Log::warning("No inventory record found for stock_id: {$transaction->item_id}, transaction_date: {$transaction->transaction_date}");
            return;
        }

        if ($action === 'deduct') {
            if ($inventory->Closing_quantity >= $transaction->quantity) {
                $inventory->Closing_quantity -= $transaction->quantity;
                $inventory->quantity_out += $transaction->quantity;
                $inventory->save();
                // Log::info("Inventory updated: Closing_quantity = {$inventory->Closing_quantity}, quantity_out = {$inventory->quantity_out}");
            } else {
                Log::error("Not enough stock! Closing_quantity: {$inventory->Closing_quantity}, Required: {$transaction->quantity}");
                // throw new \Exception("Not enough stock to complete the transaction.");
            }
        } elseif ($action === 'revert') {
            $inventory->Closing_quantity += $transaction->quantity;
            $inventory->quantity_out -= $transaction->quantity;
            $inventory->save();
            Log::info("Inventory reverted: Closing_quantity = {$inventory->Closing_quantity}, quantity_out = {$inventory->quantity_out}");
        }
    }


}
