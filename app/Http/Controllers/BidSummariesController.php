<?php

namespace App\Http\Controllers;

use App\Models\Inventory\BidDetail;
use App\Models\Inventory\BidSummary;
use App\Models\Inventory\Product;
use App\Models\Inventory\PurchaseRequisitionNote;
use App\Models\Inventory\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidSummariesController extends Controller
{
    //
    public function index()
    {
        $prns = PurchaseRequisitionNote::with([
            'mr.requestedByUser',
            'details.product'
        ])->where('status', 1)->latest()->get();
    
         $bids = BidSummary::with([
            'supplier',
            'prn.details.product',
            'prn.mr.requestedByUser', 
        ])->latest()->get();
    
        $suppliers = Supplier::all();
        $products  = Product::all();
    
        return response()->json([
            'success'   => true,
            'message'   => 'MRs, PRNs, Bids, Suppliers, and Products fetched successfully.',
            'prns'      => $prns,
            'bids'      => $bids,
            'suppliers' => $suppliers,
            'products'  => $products,
        ]);
    }
    
    public function store(Request $request)
    { 
        $request->validate([
            'prn_id'    => 'required|exists:purchase_requisition_notes,id',
            'mr_id'     => 'required|exists:material_requests,id',
            'suppliers' => 'required|array|min:1',
            'suppliers.*.supplier_id'          => 'required|exists:suppliers,id',
            'suppliers.*.quotation_ref'        => 'required|string',
            'suppliers.*.quotation_date'       => 'required|date',
            'suppliers.*.trade_classification' => 'nullable|string',
            'suppliers.*.advance_percent'      => 'required|numeric',
            'suppliers.*.after_delivery_percent' => 'required|numeric',
            'suppliers.*.credit_days'          => 'required|integer',
            'suppliers.*.discount_amount'      => 'required|numeric',
            'suppliers.*.delivery_charges'     => 'required|numeric',
            'suppliers.*.contact_person'       => 'required|string',
            'suppliers.*.terms_condition'      => 'required|string',
            'suppliers.*.products'             => 'required|array|min:1',
            'suppliers.*.products.*.product_id'=> 'required|exists:products,id',
            'suppliers.*.products.*.rate'      => 'required|numeric',
            'suppliers.*.products.*.quantity'  => 'required|numeric',
        ]);
        DB::beginTransaction();
        try {
            foreach ($request->suppliers as $supplier) {
                $summary = BidSummary::create([
                    'prn_id' => $request->prn_id,
                    'mr_id'  => $request->mr_id,
                    'supplier_id'  => $supplier['supplier_id'],
                    'total_amount' => collect($supplier['products'])->sum(fn($p) => $p['rate'] * $p['quantity']),
                    'total'        => collect($supplier['products'])->sum(fn($p) => $p['rate'] * $p['quantity']),
                    'tax'          => 0, // compute if needed
                    'advance'      => $supplier['advance_percent'],
                    'after_delivery' => $supplier['after_delivery_percent'],
                    'credit_days'  => $supplier['credit_days'],
                    'discount'     => $supplier['discount_amount'],
                    'delivery_charges' => $supplier['delivery_charges'],
                    'contact_person'   => $supplier['contact_person'],
                    'terms_condition'  => $supplier['terms_condition'],
                    'quotation_ref'    => $supplier['quotation_ref'],
                    'quotation_date'   => $supplier['quotation_date'],
                    'status'           => 1,
                ]);
    
                foreach ($supplier['products'] as $product) {
                    BidDetail::create([
                        'bid_id'     => $summary->id,
                        'product_id' => $product['product_id'],
                        'qty'        => $product['quantity'],
                        'rate'       => $product['rate'],
                        'total'      => $product['rate'] * $product['quantity'],
                        'discount'   => 0,
                        'delivery_charges' => 0,
                        'tax'        => 0,
                        'net_amount' => $product['rate'] * $product['quantity'],
                    ]);
                }
            }
    
            DB::commit();
            $detail = PurchaseRequisitionNote::findOrFail($request->prn_id);
            $detail->update([
                'status'    => 2, 
            ]);
            return response()->json(['message' => 'Bids submitted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Submission failed.', 'details' => $e->getMessage()], 500);
        }
    }

 // Compare bids function
public function compareBid(Request $request)
{
    $prnId = $request->prn_id;

    $bids = BidSummary::with([
        'supplier',
        'prn.mr.requestedByUser',
        'details.product',
        'details.supplier'
    ])
    ->where('prn_id', $prnId)
    ->get();

    return response()->json([
        'success' => true,
        'bids'    => $bids
    ]);
}

// Show bids for PRN
public function show(Request $request)
{
    $prnId = $request->prn_id;

    $bids = BidSummary::with([
        'supplier',
        'prn.mr.requestedByUser',
        'details.product',
        'details.supplier'
    ])
    ->where('prn_id', $prnId)
    ->get();

    $grouped = [];
    foreach ($bids as $bid) {
        $grouped[] = $bid;
    }

    return response()->json([
        'success'     => true,
        'bids_by_prn' => $grouped
    ]);
}

        
    
}
