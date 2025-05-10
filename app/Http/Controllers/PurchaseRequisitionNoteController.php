<?php

namespace App\Http\Controllers;

use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\PurchaseRequisitionNote;
use App\Models\Inventory\PurchaseRequisitionNoteDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseRequisitionNoteController extends Controller
{
    //
    public function index()
    {
        $mrs = MaterialRequest::with([
            'details.product',
            'requestedByUser'
        ])->where('status', 1)->get();
    
        $prns = PurchaseRequisitionNote::with([
            'mr.requestedByUser',
            'details.product'
        ])->latest()->get();
        
    
        return response()->json([
            'success' => true,
            'message' => 'MRs and PRNs fetched successfully.',
            'mrs'     => $mrs,
            'prns'    => $prns
        ]);
    }
    
    public function store(Request $request)
    { 
        $validated = $request->validate([
            'mr_id' => 'required',
            'items' => 'required',
            'items.*.product_id' => 'required',
            'items.*.qty'        => 'required',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Create PRN header
            $prn = PurchaseRequisitionNote::create([
                'mr_id'  => $validated['mr_id'],
                'status' => 1,
            ]); 
            $mr = MaterialRequest::findOrFail($validated['mr_id']);
            $mr->status = 3;
            $mr->save();

            // Create PRN item details
            foreach ($validated['items'] as $item) {
                PurchaseRequisitionNoteDetail::create([
                    'prn_id'     => $prn->id,
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'], 
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'PRN created successfully.',
                'prn_id' => $prn->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Log actual error for debugging
            Log::error('PRN Store Error: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to create PRN.',
                'error' => $e->getMessage()  // Optional for debugging
            ], 500);
        }
    }
    public function fetchPrnProducts(Request $request)
    {
        $request->validate([
            'prn_id' => 'required|integer',
        ]);
    
        // Fetch all PRN detail rows that belong to the given PRN ID, including the product relation
        $prnDetails = PurchaseRequisitionNoteDetail::with('product')
            ->where('prn_id', $request->prn_id)
            ->get();
    
        if ($prnDetails->isEmpty()) {
            return response()->json(['message' => 'No products found for this PRN.'], 404);
        }
    
        // Map and return the product data with quantity
        $products = $prnDetails->map(function ($detail) {
            return [
                'id'  => $detail->product?->id,
                'name' => $detail->product?->name,
                'qty'  => $detail->qty,
            ];
        });
    
        return response()->json([
            'products' => $products
        ]);
    }

}
