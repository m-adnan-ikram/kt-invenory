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
                // Calculate total (subtotal) from all products
                $productTotals = collect($products)->map(function ($product) {
                    $rate = $product['rate'] ?? 0;
                    $qty  = $product['quantity'] ?? 0;
                    return $rate * $qty;
                });
                $subTotal = $productTotals->sum();
                // Supplier-level totals
                $totalTax      = (float) ($supplier['tax'] ?? 0);
                $totalDiscount = (float) ($supplier['discount'] ?? 0);
                $totalDelivery = (float) ($supplier['delivery_charges'] ?? 0);
                $grandTotal    = $subTotal + $totalTax + $totalDelivery - $totalDiscount;
                $advancePercent       = $supplier['advance_percent'] ?? 0;
                $afterDeliveryPercent = $supplier['after_delivery_percent'] ?? 0;
                $advanceAmount        = ($advancePercent / 100) * $grandTotal;
                $afterDeliveryAmount  = ($afterDeliveryPercent / 100) * $grandTotal;
                // Create Bid Summary
                $summary = BidSummary::create([
                    'prn_id'                  => $request->prn_id,
                    'mr_id'                   => $request->mr_id,
                    'supplier_id'             => $supplier['supplier_id'],
                    'total_amount'            => $subTotal,
                    'total'                   => $grandTotal,
                    'tax'                     => $totalTax,
                    'advance'                 => $advanceAmount,
                    'after_delivery'          => $afterDeliveryAmount,
                    'credit_days'             => $supplier['credit_days'],
                    'discount'                => $totalDiscount,
                    'delivery_charges'        => $totalDelivery,
                    'contact_person'          => $supplier['contact_person'],
                    'contact_person_contact'  => $supplier['contact_person_contact'],
                    'terms_condition'         => $supplier['terms_condition'],
                    'quotation_ref'           => $supplier['quotation_ref'],
                    'quotation_date'          => $supplier['quotation_date'] ?? now(),
                    'status'                  => 1,
                ]);
                // Now distribute discount, tax, and delivery proportionally per product
                foreach ($products as $product) {
                    $rate  = (float) ($product['rate'] ?? 0);
                    $qty   = (float) ($product['quantity'] ?? 0);
                    $total = $rate * $qty;
                    $ratio = $subTotal > 0 ? $total / $subTotal : 0;
                    $discountShare = $ratio * $totalDiscount;
                    $taxShare      = $ratio * $totalTax;
                    $deliveryShare = $ratio * $totalDelivery;
                    $netAmount     = $total + $taxShare + $deliveryShare - $discountShare;
                    BidDetail::create([
                        'bid_id'           => $summary->id,
                        'product_id'       => $product['product_id'],
                        'qty'              => $qty,
                        'rate'             => $rate,
                        'total'            => $total,
                        'discount'         => round($discountShare, 2),
                        'delivery_charges' => round($deliveryShare, 2),
                        'tax'              => round($taxShare, 2),
                        'net_amount'       => round($netAmount, 2),
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
