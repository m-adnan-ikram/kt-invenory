<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    //
  // ✅ SupplierController@index
    public function index()
    {
        $suppliers = Supplier::withCount('bidDetails')
            ->get()
            ->map(function ($supplier) {
                $supplier->is_deletable = $supplier->bid_details_count == 0;
                return $supplier;
            });
    
        return response()->json([
            'suppliers' => $suppliers
        ]);
    }
    

    // ✅ Store a new supplier
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'contact' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'cnic'    => 'required|string|max:25',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'Supplier created successfully.',
            'supplier' => $supplier
        ], 201); // 201 = Created
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:suppliers,id',
            'name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'cnic' => 'nullable|string|max:25',
        ]);
    
        $supplier = Supplier::findOrFail($validated['id']);
    
        // ✅ Update only fields that are provided
        $supplier->update(array_filter([
            'name' => $validated['name'] ?? $supplier->name,
            'contact' => $validated['contact'] ?? $supplier->contact,
            'address' => $validated['address'] ?? $supplier->address,
            'cnic' => $validated['cnic'] ?? $supplier->cnic,
        ]));
    
        return response()->json([
            'message' => 'Supplier updated successfully.',
            'supplier' => $supplier
        ]);
    }
    
     public function delete(Request $request)
     {
         $request->validate([
             'id' => 'required',
         ]);
         
         $deleted = Supplier::destroy($request->input('id'));
         
         if (! $deleted) {
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to delete unit.',
             ], 500);
         }
         
         return response()->json([
             'success' => true,
             'message' => 'Supplier deleted successfully.',
         ], 200);
         
     }
}
