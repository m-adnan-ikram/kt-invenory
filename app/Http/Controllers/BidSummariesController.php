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
        ])
        ->latest()
        ->get()
        ->unique('prn_id')
        ->values(); // ensures reindexing        

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
        DB::beginTransaction();
        try {
            foreach ($request->suppliers ?? [] as $supplier) {
                $products = $supplier['products'] ?? [];
                $totalAmount = collect($products)->sum(fn($p) => ($p['rate'] ?? 0) * ($p['quantity'] ?? 0));

                $summary = BidSummary::create([
                    'prn_id'            => $request->prn_id ?? null,
                    'mr_id'             => $request->mr_id ?? null,
                    'supplier_id'       => $supplier['supplier_id'] ?? null,
                    'total_amount'      => $totalAmount,
                    'total'             => $totalAmount,
                    'tax'               => 0,
                    'advance'           => $supplier['advance_percent'] ?? 0,
                    'after_delivery'    => $supplier['after_delivery_percent'] ?? 0,
                    'credit_days'       => $supplier['credit_days'] ?? 0,
                    'discount'          => $supplier['discount_amount'] ?? 0,
                    'delivery_charges'  => $supplier['delivery_charges'] ?? 0,
                    'contact_person'    => $supplier['contact_person'] ?? '',
                    'terms_condition'   => $supplier['terms_condition'] ?? '',
                    'quotation_ref'     => $supplier['quotation_ref'] ?? '',
                    'quotation_date'    => $supplier['quotation_date'] ?? now(),
                    'status'            => 1,
                ]);

                foreach ($products as $product) {
                    $rate = $product['rate'] ?? 0;
                    $quantity = $product['quantity'] ?? 0;
                    $total = $rate * $quantity;

                    BidDetail::create([
                        'bid_id'           => $summary->id,
                        'product_id'       => $product['product_id'] ?? null,
                        'qty'              => $quantity,
                        'rate'             => $rate,
                        'total'            => $total,
                        'discount'         => 0,
                        'delivery_charges' => 0,
                        'tax'              => 0,
                        'net_amount'       => $total,
                    ]);
                }
            }

            if ($request->prn_id) {
                PurchaseRequisitionNote::find($request->prn_id)?->update(['status' => 2]);
            } 
            DB::commit();
            return response()->json(['message' => 'Bids submitted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Submission failed.',
                'details' => $e->getMessage(),
            ], 500);
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
