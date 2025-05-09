<?php

namespace App\Http\Controllers;

use App\Models\Inventory\BidSummary;
use App\Models\Inventory\Product;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    //
    public function index()
    {
        // Only fetch POs with status == 1
         $pos = PurchaseOrder::with([
            'supplier',
            'mr.requestedByUser',
            'prn',
        ])->latest()->get();
    
        // Only fetch Bids with status == 1
        $bids = BidSummary::with([
            'supplier',
            'prn.details.product',
            'prn.mr.requestedByUser',
        ])->where('status', 1)->latest()->get();
    
        $suppliers = Supplier::all();
        $products  = Product::all();
    
        return response()->json([
            'success'   => true,
            'message'   => 'Data fetched successfully.',
            'bids'      => $bids,
            'suppliers' => $suppliers,
            'products'  => $products,
            'pos'       => $pos,
        ]);
    }
    
    
    
}
