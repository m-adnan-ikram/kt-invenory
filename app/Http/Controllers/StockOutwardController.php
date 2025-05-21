<?php

namespace App\Http\Controllers;

use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\MaterialRequestDetail;
use App\Models\Inventory\Product;
use App\Models\Inventory\StoreIssuanceNote;
use App\Models\Inventory\StoreIssuanceNoteDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutwardController extends Controller
{
    //
    public function index()
    {
        // Get issued qty map
        $issuedQtyMap = StoreIssuanceNoteDetail::with('storeIssuanceNote')
            ->get()
            ->groupBy(fn ($detail) => $detail->storeIssuanceNote->mr_id . '-' . $detail->product_id)
            ->map(fn ($group) => $group->sum('qty'));

             $mrs = MaterialRequest::with(['details.product', 'requestedByUser'])
            ->whereHas('details', function ($query) {
                $query->whereColumn('qty', '!=', 'store_issued_qty');
            })
            ->latest()
            ->get();
        
            // Inject issued_qty (optional step if needed)
            foreach ($mrs as $mr) {
                foreach ($mr->details as $detail) {
                    $key = $mr->id . '-' . $detail->product_id;
                    $detail->issued_qty = $issuedQtyMap[$key] ?? 0; // Assuming issuedQtyMap is defined
                }
            }
        

        // Get outwards
        $outwards = StoreIssuanceNote::with(['details.product'])->latest()->get();

        return response()->json([
            'success'  => true,
            'message'  => 'MRs and Outwards fetched successfully.',
            'mrs'      => $mrs,
            'outwards' => $outwards
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mr_id'        => 'required|exists:material_requests,id',
            'requested_by' => 'required|string',
            'details'      => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty'   => 'required|numeric|min:1',
            'details.*.rate'  => 'required|numeric',
            'details.*.total' => 'required|numeric',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Create new issuance
            $storeIssuance = StoreIssuanceNote::create([
                'mr_id'        => $request->mr_id,
                'requested_by' => $request->requested_by,
                'status'       => 1, // Partial
                'added_by'     => auth()->id()
            ]);
    
            $issuedNow = 0;
    
            foreach ($request->details as $detail) {
                $product = Product::findOrFail($detail['product_id']);
    
                // Check stock
                if ($product->qty < $detail['qty']) {
                    throw new \Exception("Insufficient stock for product ID: {$product->id}");
                }
    
                // Reduce stock
                $product->decrement('qty', $detail['qty']);
    
                // Save issuance detail
                StoreIssuanceNoteDetail::create([
                    'store_issuance_note_id' => $storeIssuance->id,
                    'product_id' => $detail['product_id'],
                    'qty'        => $detail['qty'],
                    'rate'       => $detail['rate'],
                    'total'      => $detail['total'],
                ]);
    
                // Update issued qty in MR details
                MaterialRequestDetail::where('mr_id', $request->mr_id)
                    ->where('product_id', $detail['product_id'])
                    ->increment('store_issued_qty', $detail['qty']);
    
                $issuedNow += $detail['qty'];
            }
    
            // Load MR and all its details
            $mr = MaterialRequest::with('details')->findOrFail($request->mr_id);
            // Calculate total requested and issued
            $requestedTotal = $mr->details->sum('qty');
            $issuedTotal    = $mr->details->sum('store_issued_qty');
    
            // Update status
            if ($issuedTotal >= $requestedTotal) {
                $mr->status = 2; // Completed
                $storeIssuance->status = 2; // Completed
            } else {
                $mr->status = 7; // Partial
                $storeIssuance->status = 1; // Partial
            }
    
            $mr->save();
            $storeIssuance->save();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Store Issuance Note created successfully',
                'data'    => $storeIssuance->load('details')
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create issuance note',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
}

