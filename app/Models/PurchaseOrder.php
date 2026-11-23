<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'product_name', 'order_quantity', 'progress',
        'po_date', 'production_start', 'estimated_finish', 'actual_finish',
        'order_status', 'remarks',
    ];

    protected $casts = [
        'order_quantity'   => 'integer',
        'progress'         => 'integer',
        'po_date'          => 'date',
        'production_start' => 'date',
        'estimated_finish' => 'date',
        'actual_finish'    => 'date',
        'order_status'     => OrderStatus::class,
    ];

    /** Status yang ditampilkan: Delayed dihitung otomatis. */
    public function getDisplayStatusAttribute(): OrderStatus
    {
        $status = $this->order_status;

        if (
            in_array($status, [OrderStatus::Waiting, OrderStatus::OnProcess], true)
            && $this->estimated_finish
            && $this->estimated_finish->isBefore(today())
        ) {
            return OrderStatus::Delayed;
        }

        return $status;
    }

    /** Filter berdasarkan status yang ditampilkan (termasuk Delayed otomatis). */
    public function scopeDisplayStatus(Builder $q, string $status): Builder
    {
        $overdue = fn ($q) => $q->whereIn('order_status', ['Waiting', 'On Process'])
                                ->whereNotNull('estimated_finish')
                                ->whereDate('estimated_finish', '<', today());

        if ($status === 'Delayed') {
            return $q->where($overdue);
        }

        return $q->where('order_status', $status)
                 ->when(in_array($status, ['Waiting', 'On Process']),
                     fn ($q) => $q->whereNot($overdue));
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $q->when($term, fn ($q) => $q->where(fn ($q) => $q
            ->where('order_number', 'like', "%{$term}%")
            ->orWhere('customer_name', 'like', "%{$term}%")
            ->orWhere('product_name', 'like', "%{$term}%")));
    }

    protected static function booted(): void
    {
        static::saving(function (self $po) {
            if ($po->order_status === OrderStatus::Completed) {
                $po->progress = 100;
                $po->actual_finish ??= today();
            }
        });
    }
}