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
            $details = BidDetail::with(['product', 'bids.supplier', 'bids.prn', 'bids.mr'])
                ->whereIn('id', $validated['bid_detail_ids'])
                ->get();

            if ($details->isEmpty()) {
                return response()->json(['message' => 'No valid bid details found.'], 404);
            }

            $validDetails = $details->filter(fn($d) => $d->bids && $d->bids->supplier_id);
            if ($validDetails->isEmpty()) {
                return response()->json(['message' => 'No bid details with valid supplier data.'], 400);
            }

            $grouped = $validDetails->groupBy(fn($d) => $d->bids->supplier_id);
            $createdPOs = [];

            foreach ($grouped as $supplierId => $supplierDetails) {
                $firstDetail = $supplierDetails->first();
                $bid = $firstDetail->bids;
                $mr  = $bid->mr;
                $prn = $bid->prn;

                if (!$bid || !$mr || !$prn) {
                    throw new \Exception("Missing bid, MR or PRN for supplier ID $supplierId");
                }

                // Fetch full-order charges from the bid
                $deliveryCharges = floatval($bid->delivery_charges);
                $totalTax        = floatval($bid->tax);
                $totalDiscount   = floatval($bid->discount);

                // Calculate total sub_total for proportional distribution
                $totalSubTotal = $supplierDetails->sum(function ($detail) {
                    return floatval($detail->qty) * floatval($detail->rate);
                });

                $total = 0;
                $poDetails = [];

                foreach ($supplierDetails as $detail) {
                    $qty      = floatval($detail->qty);
                    $rate     = floatval($detail->rate);
                    $subTotal = $qty * $rate;

                    $proportion = $subTotal / ($totalSubTotal ?: 1); // Avoid divide by 0

                    $delivery  = round($deliveryCharges * $proportion);
                    $tax_amount= round($bid->tax_amount * $proportion);
                    $discount  = round($totalDiscount * $proportion);

                    $netAmount = $subTotal + $tax_amount + $delivery - $discount;
                    $total += $netAmount;

                    $poDetails[] = [
                        'product_id' => $detail->product_id,
                        'qty'        => $qty,
                        'rate'       => $rate,
                        'sub_total'  => $subTotal,
                        'tax'        => $totalTax, // Tax in percentage
                        'tax_amount' => $tax_amount, // Tax in amount
                        'delivery'   => $delivery,
                        'discount'   => $discount,
                        'net_amount' => $netAmount,
                        'po_id'      => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'company_id' => Auth::user()->company_id,
                    ];
                }

                $po = PurchaseOrder::create([
                    'bid_id'      => $bid->id,
                    'mr_id'       => $mr->id,
                    'prn_id'      => $prn->id,
                    'supplier_id' => $supplierId,
                    'total'       => $total,
                    'remaining'   => $total,
                    'status'      => '1',
                    'added_by'    => auth()->id(),
                    'company_id'  => Auth::user()->company_id,

                ]);

                foreach ($poDetails as &$detail) {
                    $detail['po_id'] = $po->id;
                }
                PurchaseOrderDetail::insert($poDetails);
                $mr->status = 5;
                $mr->save();
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
            Log::error('PO creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create purchase orders: ' . $e->getMessage(),
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