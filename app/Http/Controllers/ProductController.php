<?php

namespace App\Http\Controllers;

use App\Models\Inventory\BidSummary;
use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductCategory;
use App\Models\Inventory\ProductUnit;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseRequisitionNote;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 👉 List products
    public function index(Request $request)
    {
        $products   = Product::with(['unit', 'category'])->get();
        $categories = ProductCategory::all();
        $units      = ProductUnit::all();

        return response()->json([
            'products'   => $products,
            'categories' => $categories,
            'units'      => $units,
        ]);
    }

    // 👉 Create a new product
    public function store(Request $request)
    { 
        $request->validate([ 
            'name'        => 'required|string|max:255',
            'unit_id'     => 'required|integer|exists:product_units,id',
            'category_id' => 'required|integer|exists:product_categories,id',
            'qty'         => 'required',
            'avg_price'   => 'required',
        ]);


        $product = Product::create([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'unit_id'     => $request->unit_id,
            'qty'         => 0,
            'avg_price'   => 0,
        ]);

        return response()->json([
            'message' => 'Product created successfully!',
            'product' => $product->load(['unit', 'category']),
        ]);
    }

    // 👉 Update an existing product
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id'          => 'required|exists:products,id',
            'name'        => 'required|string|max:255',
            'unit_id'     => 'required|exists:product_units,id',
            'category_id' => 'required|exists:product_categories,id', 
        ]);

        $product = Product::findOrFail($validated['id']);

        // Update only necessary fields
        $product->name        = $validated['name'];
        $product->unit_id     = $validated['unit_id'];
        $product->category_id = $validated['category_id']; 
        $product->save();

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('unit', 'category')
        ]);
    }


 // 👉 Delete a product
public function delete(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:products,id',
    ]);

    $product = Product::findOrFail($validated['id']);
    $product->delete();

    return response()->json([
        'message' => 'Product deleted successfully'
    ]);
}

public function allRequests(Request $request){
    
    $mrs  = MaterialRequest::where('status', 1)->count();
    $pos  = PurchaseOrder::where('status', 1)->count();
    $prns = PurchaseRequisitionNote::where('status', 1)->count();
    $bids = BidSummary::where('status', 1)->count();
    return response()->json([ 
        'pos'    => $pos,
        'mrs'    => $mrs,
        'prns'   => $prns,
        'bids'   => $bids,
    ], 200);
}
}
