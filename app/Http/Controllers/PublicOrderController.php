<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PublicOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = OrderStatus::tryFrom((string) $request->query('status'))?->value;

        $orders = PurchaseOrder::query()
            ->search($request->query('q'))
            ->when($status, fn ($q) => $q->displayStatus($status))
            ->orderByDesc('po_date')
            ->paginate(20)
            ->withQueryString();

        $all = PurchaseOrder::all()->countBy(fn ($po) => $po->display_status->value);

        return view('orders', [
            'orders'   => $orders,
            'counts'   => $all,
            'total'    => $all->sum(),
            'statuses' => OrderStatus::cases(),
        ]);
    }
}