<?php

namespace App\Http\Controllers;

use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\MaterialRequestDetail;
use App\Models\Inventory\Product;
use App\Models\Inventory\StoreIssuanceNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialRequestController extends Controller
{
    
    public function index()
    {
        $mrs = MaterialRequest::with(['details.product', 'requestedByUser', 'storeIssuance.details'])->latest()->get();
        $products = Product::all();
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
                'company_id'   => Auth::user()->company_id,
                'added_by'     => auth()->id()
            ]);
    
            foreach ($request->details as $detail) {
                $mr->details()->create([
                    'product_id'      => $detail['product_id'],
                    'qty'             => $detail['qty'],
                    'store_Issued_qty' => 0,
                    'reason'          => $detail['reason'],
                    'company_id'      => Auth::user()->company_id,
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
            'id'     => 'required',
            'qty'    => 'required',
            'reason' => 'nullable',
        ]);
        $mr     = MaterialRequestDetail::findOrFail($validated['id']); 
        $mr->update([
            'qty'    => $validated['qty'],
            'reason' => $validated['reason'],
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Material Request Detail updated successfully.',
            'data'    => $mr,
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
