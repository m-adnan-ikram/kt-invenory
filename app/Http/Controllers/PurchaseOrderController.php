<?php

namespace App\Http\Controllers;

use App\Models\Inventory\BidDetail;
use App\Models\Inventory\BidSummary;
use App\Models\Inventory\Product;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderDetail;
use App\Models\Inventory\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     
        $bids = BidSummary::with([
            'supplier',
            'prn.details.product',
            'prn.mr.requestedByUser',
        ])
        ->where('status', 1)
        ->latest()
        ->get()
        ->unique('prn_id')
        ->values(); // ensures reindexing     

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bid_detail_ids'   => 'required|array|min:1',
            'bid_detail_ids.*' => 'exists:bid_details,id',
        ]);
    
        DB::beginTransaction();
        try {
            $details = BidDetail::with(['product', 'bid', 'bid.supplier', 'bid.prn', 'bid.mr'])
                ->whereIn('id', $validated['bid_detail_ids'])
                ->get();
            if ($details->isEmpty()) {
                return response()->json(['message' => 'No valid bid details found.'], 404);
            }
            // Assume all details are for the same bid/mr/prn/supplier
            $firstDetail = $details->first();
            $bid         = $firstDetail->bid;
            $supplier    = $bid->supplier;
            $mr  = $bid->mr;
            $prn = $bid->prn;
            $total = 0;
            // Placeholder array to hold detail rows
            $poDetails = [];
    
            foreach ($details as $detail) {
                $qty      = $detail->qty;
                $rate     = $detail->rate;
                $subTotal = $qty * $rate;
                $tax = ($subTotal * 0.05); // 5% Tax (adjust logic as needed)
                $delivery = 100; // Fixed or computed
                $discount = ($subTotal * 0.02); // 2% Discount (adjust as needed)
                $netAmount = $subTotal + $tax + $delivery - $discount;
                $total    += $netAmount;
                $poDetails[] = [
                    'product_id' => $detail->product_id,
                    'qty'        => $qty,
                    'rate'       => $rate,
                    'sub_total'  => $subTotal,
                    'tax'        => $tax,
                    'delivery'   => $delivery,
                    'discount'   => $discount,
                    'net_amount' => $netAmount,
                ];
            }
            // Create PO
            $po = PurchaseOrder::create([
                'bid_id'  => $bid->id,
                'mr_id'   => $mr->id,
                'prn_id'  => $prn->id,
                'supplier_id' => $supplier->id,
                'total'       => $total,
                'remaining'   => $total,
                'status'      => 1, // Processing
            ]);
            // Save PO details
            foreach ($poDetails as &$detail) {
                $detail['po_id'] = $po->id;
            }
            PurchaseOrderDetail::insert($poDetails);
            DB::commit();
            return response()->json([
                'message' => 'Purchase Order created successfully.',
                'po_id' => $po->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating PO: ' . $e->getMessage(),
            ], 500);
        }
    }
    
}
