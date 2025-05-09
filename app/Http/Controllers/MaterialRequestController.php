<?php

namespace App\Http\Controllers;

use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\MaterialRequestDetail;
use App\Models\Inventory\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialRequestController extends Controller
{
    //
    // MaterialRequestController.php
    public function index()
{
    $mrs = MaterialRequest::with(['details.product', 'requestedByUser'])->get(); // eager load 'requestedByUser' relation
    $products = Product::get();

    return response()->json([
        'success'  => true,
        'message'  => 'Material Requests fetched successfully.',
        'data'     => $mrs,
        'products' => $products,
    ], 200);
}

    public function store(Request $request)
    {
        $request->validate([ 
            'details'              => 'required',
            'details.*.product_id' => 'required',
            'details.*.qty'        => 'required',
            'details.*.reason'     => 'required',
        ]);
    
        DB::beginTransaction();
        try {
            $mr = MaterialRequest::create([
                'requested_by' => auth()->id(), 
                'status'       => 1, 
            ]);
    
            foreach ($request->details as $detail) {
                $mr->details()->create([
                    'product_id' => $detail['product_id'],
                    'qty'        => $detail['qty'],
                    'reason'     => $detail['reason'],
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Material Request created successfully.',
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id'     => 'required|exists:material_request_details,id',
            'qty'    => 'required|numeric|min:1',
            'reason' => 'nullable|string',
        ]);
        $detail = MaterialRequestDetail::findOrFail($validated['id']);
        $detail->update([
            'qty'    => $validated['qty'],
            'reason' => $validated['reason'],
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Material Request Detail updated successfully.',
            'data'    => $detail,
        ], 200); // <== make sure to set status 200
    }    
    public function mr_destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:material_requests,id',
        ]);
    
        $materialRequest = MaterialRequest::findOrFail($request->id);
        $materialRequest->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Material Request deleted successfully.',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:material_request_details,id',
        ]);
    
        $detail = MaterialRequestDetail::findOrFail($request->id);
        $detail->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Material Request Detail deleted successfully.',
        ]);
    }
    
}
