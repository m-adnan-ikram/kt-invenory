<?php

namespace App\Http\Controllers;

use App\Models\Inventory\GoodReceiveNote;
use App\Models\Inventory\PurchaseOrder;
use Illuminate\Http\Request;

class StockInwardController extends Controller
{
    //
    public function index()
    {
        $pos = PurchaseOrder::with(['supplier', 'mr.requestedByUser', 'prn'])
            ->where('status', 1)
            ->latest()
            ->get();

        $inwards = GoodReceiveNote::with(['supplier', 'mr.requestedByUser', 'prn'])
            ->latest()
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Data fetched successfully.',
            'pos' => $pos,
            'inwards' => $inwards,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'po_id' => 'required|exists:purchase_orders,id',
            'items' => 'required|array|min:1',
            'items.*.received_now' => 'required|numeric|min:0',
        ]);
    
        foreach ($request->items as $item) {
            // Store logic for each inward item
            Inward::create([
                'purchase_order_id' => $request->po_id,
                'product_name' => $item['product_name'],
                'received_qty' => $item['received_now'],
                'received_at' => now(),
            ]);
        }
    
        return response()->json(['success' => true]);
    }
    
}
