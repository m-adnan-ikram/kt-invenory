<?php

namespace App\Http\Controllers;

use App\Models\Inventory\MaterialRequest;
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
         $mrs = MaterialRequest::with([
            'details.product',
            'requestedByUser'
        ])
        ->where('status', 6)->latest()->get();
    
        $outwards = StoreIssuanceNote::with(['details.product'])
        ->latest()
        ->get();
    
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
            'requested_by' => 'required|string|max:255',
            'reason'       => 'nullable|string',
            'details'      => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty'   => 'required|integer|min:0',
            'details.*.rate'  => 'required|numeric|min:0',
            'details.*.total' => 'required|numeric|min:0',
        ]);
        try {
            DB::beginTransaction();
            $storeIssuance = StoreIssuanceNote::create([
                'mr_id' => $request->mr_id,
                'requested_by' => $request->requested_by,
                'reason' => $request->reason ?? 'NA',
            ]);
            foreach ($request->details as $detail) {
                if($detail['qty'] > 0){
                    // Create detail record
                    // Update product stock
                    $product = Product::find($detail['product_id']);
                    if ($product) {
                        // Check to prevent negative stock
                        if ($product->qty < $detail['qty'] )  {
                            throw new \Exception("Insufficient stock for product ID: {$product->id}");
                        }
                        $product->decrement('qty', $detail['qty']);
                        StoreIssuanceNoteDetail::create([
                            'store_issuance_note_id' => $storeIssuance->id,
                            'product_id' => $detail['product_id'],
                            'qty'        => $detail['qty'],
                            'rate'       => $product->avg_price,
                            'total'      => $detail['total'],
                        ]);
                    }
                }
                
            }
            $mr = MaterialRequest::findOrFail($request->mr_id);
            $mr->status = 2;
            $mr->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Store Issuance Note created and stock updated successfully',
                'data' => $storeIssuance->load('details')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create issuance note',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}

