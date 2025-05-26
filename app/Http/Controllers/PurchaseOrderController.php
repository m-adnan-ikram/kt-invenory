<?php

namespace App\Http\Controllers;

use App\Models\Inventory\BidDetail;
use App\Models\Inventory\BidSummary;
use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\Product;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderDetail;
use App\Models\Inventory\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
          ])  
          ->latest()
          ->get();        
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
            $details = BidDetail::with(['product', 'bid.supplier', 'bid.prn', 'bid.mr'])
                ->whereIn('id', $validated['bid_detail_ids'])
                ->get();
    
            if ($details->isEmpty()) {
                return response()->json(['message' => 'No valid bid details found.'], 404);
            }
    
            // Group by bid ID (create PO per bid)
            $groupedByBid = $details->groupBy(fn($d) => $d->bid->id);
            $createdPOs = [];
    
            foreach ($groupedByBid as $bidId => $bidDetails) {
                $firstDetail = $bidDetails->first();
                $bid = $firstDetail->bid;
                $mr  = $bid->mr;
                $prn = $bid->prn;
                $supplierId = $bid->supplier_id;
    
                if (!$bid || !$mr || !$prn) {
                    throw new \Exception("Missing bid, MR or PRN for bid ID $bidId");
                }
    
                // Charges from bid summary
                $deliveryCharges = floatval($bid->delivery_charges);
                $totalTax        = floatval($bid->tax);
                $taxAmount       = floatval($bid->tax_amount);
                $totalDiscount   = floatval($bid->discount);
    
                $subTotalSum = $bidDetails->sum(fn($d) => $d->qty * $d->rate);
                $poDetails = [];
                $poTotal = 0;
    
                foreach ($bidDetails as $detail) {
                    $qty      = floatval($detail->qty);
                    $rate     = floatval($detail->rate);
                    $subTotal = $qty * $rate;
    
                    $proportion = $subTotal / ($subTotalSum ?: 1);
    
                    $delivery  = round($deliveryCharges * $proportion, 2);
                    $tax_amt   = round($taxAmount * $proportion, 2);
                    $discount  = round($totalDiscount * $proportion, 2);
    
                    $netAmount = $subTotal + $tax_amt + $delivery - $discount;
                    $poTotal  += $netAmount;
    
                    $poDetails[] = [
                        'product_id' => $detail->product_id,
                        'qty'        => $qty,
                        'rate'       => $rate,
                        'sub_total'  => $subTotal,
                        'tax'        => $totalTax,
                        'tax_amount' => $tax_amt,
                        'delivery'   => $delivery,
                        'discount'   => $discount,
                        'net_amount' => $netAmount,
                        'company_id' => auth()->user()->company_id,
                        'po_id'      => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
    
                $po = PurchaseOrder::create([
                    'bid_id'      => $bid->id,
                    'mr_id'       => $mr->id,
                    'prn_id'      => $prn->id,
                    'supplier_id' => $supplierId,
                    'total'       => $poTotal,
                    'remaining'   => $poTotal,
                    'status'      => 1,
                    'added_by'    => auth()->id(),
                    'company_id'  => auth()->user()->company_id,
                ]);
    
                foreach ($poDetails as &$d) {
                    $d['po_id'] = $po->id;
                }
    
                PurchaseOrderDetail::insert($poDetails);
    
                $mr->update(['status' => 5]);
                $bid->update(['status' => 2]);
    
                $createdPOs[] = $po->id;
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Purchase Orders created successfully.',
                'po_ids'  => $createdPOs,
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO creation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create purchase orders.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    

    
 
    public function show(Request $request)
    {
        $po = PurchaseOrder::with(['supplier', 'poDetails.product', 'goodReceiveNotes.details.product'])->where('id', $request->po_id)->get();
        return response()->json([
            'success' => true,
            'pos' => $po
        ]);
    }
    public function getSingle(Request $request)
    {
        $po = PurchaseOrder::with('poDetails.product')
            ->where('id', $request->po_id)
            ->first(); 
    
        if (!$po) {
            return response()->json([
                'success' => false,
                'message' => 'PO not found.',
            ], 404);
        }
    
        // Format the product list for Vue
        $products = $po->poDetails->map(function ($item) {
            return [
                'product_id'            => $item->product_id,
                'product_name'          => $item->product->name ?? 'N/A',
                'qty'                   => $item->qty, // Receivable Quantity
                'already_received_qty'  => $item->store_received ?? 0, // Already Received
            ];
        });
    
        return response()->json([
            'success' => true,
            'po' => [
                'id'       => $po->id,
                'products' => $products
            ]
        ]);
    }
}