<?php

namespace App\Http\Controllers;

use App\Models\Account\AccountHead;
use App\Models\Account\AccountTransaction;
use App\Models\Inventory\GoodReceiveNote;
use App\Models\Inventory\GoodReceiveNoteDetail;
use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\Product;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderDetail;
use App\Models\Inventory\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockInwardController extends Controller
{
    //
    public function index()
    {
        $pos = PurchaseOrder::with(['supplier', 'mr.requestedByUser', 'prn'])
            ->where('status', 1)
            ->latest()
            ->get();
        $inwards = GoodReceiveNote::with([
            'supplier',
            'purchaseOrder',
            'details.product'  // 💡 load inward details with product info
        ])->latest()->get();
            
        return response()->json([
            'success' => true,
            'message' => 'Data fetched successfully.',
            'pos'     => $pos,
            'inwards' => $inwards,
        ]);
    }   
    public function store(Request $request)
    { 
        $request->validate([
            'po_id'                   => 'required|exists:purchase_orders,id',
            'products'                => 'required|array|min:1',
            'products.*.product_id'   => 'required|exists:products,id',
            'products.*.received_qty' => 'required|integer|min:0',
        ]);
        DB::beginTransaction();
        try {
            $po = PurchaseOrder::with('poDetails')->findOrFail($request->po_id);
            // Step 1: Create GoodReceiveNote
            $grn = GoodReceiveNote::create([
                'po_id'        => $po->id,
                'supplier_id'  => $po->supplier_id,
                'received_by'  => auth()->user()->name ?? 'System', // replace with real user
                'added_by'     => auth()->id(),
                'company_id'   => Auth::user()->company_id,

            ]);
            $document = AccountTransaction::where(["company_id"=>Auth::user()->company_id])
            ->where("type","JV")
            ->orderBy("document_id","DESC")
            ->first();
            $document_id = $document ? $document->document_id + 1 : 1;  

            $supplierName = Supplier::where('id', $po->supplier_id)->first();
            if ($supplierName->supplier_head_id) {
                // Use existing account head
                $supplierHead = AccountHead::findOrFail($supplierName->supplier_head_id);
            } else {
                // Create new account head
                $supplierHead = $this->accountHeadCreate(
                    $supplierName->name.'-'. $supplierName->cnic. ' | LIABILITIES LEDGER',
                    2, // LIABILITIES
                    8, // CURRENT LIABILITIES
                    40, // TRADE CREDITORS
                    41, // AP-SUPPLIERS
                );
                // Save new head ID in supplier table
                $supplierName->supplier_head_id = $supplierHead->id;
                $supplierName->save();
            } 
            $total_net_amount=0;
            $sub_total=0;
            // Step 2: Iterate over products
            foreach ($request->products as $product) {
                $productId   = $product['product_id'];
                $productName = Product::where('id', $product['product_id'])->first();
                if($product['received_qty'] > 0){
                    $receivedQty = $product['received_qty'];
                    // Find matching poDetail
                    $poDetail = $po->poDetails->where('product_id', $productId)->first();
                    if (!$poDetail) {
                        continue; // Skip if product not found in PO
                    }
                    // Update PO Detail's store_received
                    $poDetail->store_received += $receivedQty;
                    $poDetail->save();
                    $fullyReceived = $po->poDetails->every(function ($detail) {
                        return $detail->qty <= $detail->store_received;
                    });
                    $po->status = $fullyReceived ? 2 : 1; // 3 = Fully Received, 2 = Partially Received
                    $po->save();
                    $sub_total = ($poDetail->rate * $receivedQty) + $poDetail->tax_amount + $poDetail->delivery - $poDetail->discount;
                    // Insert GRN Detail 
                    $total_net_amount += $sub_total;
                     GoodReceiveNoteDetail::create([
                        'good_receive_note_id' => $grn->id,
                        'product_id'           => $productId,
                        'qty'                  => $receivedQty,
                        'rate'                 => $poDetail->rate,
                        'total'                => $poDetail->rate * $receivedQty,
                        'tax'                  => $poDetail->tax_amount,
                        'delivery_charges'     => $poDetail->delivery,
                        'discount'             => $poDetail->discount,
                        'net_amount'           => $sub_total, // or calculate net_amount per unit if needed
                        'company_id'              => Auth::user()->company_id,
                    ]);   
                    if ($productName->product_head_id) {
                        $productHead = AccountHead::findOrFail($productName->product_head_id);
                    } else {
                        // Create new account head
                        $productHead = $this->accountHeadCreate(
                            $productName->name . '-|PRODUCT LEDGER',
                            1,  // ASSETS
                            6,  // CURRENT ASSETS
                            15, // STORE AND SPARES
                            18  // GENERAL PARTS
                        );
                        // Save the new account head ID to the product
                        $productName->product_head_id = $productHead->id;
                        $productName->save();
                    }
                    $this->updateSaleTransaction(
                        $productHead, // head
                        $supplierHead->id,//other head id
                        0, //credit
                        $sub_total, //debit
                        $document_id, //document id
                        "Generated GRN of ".$productName->name." Received QTY@". $receivedQty ." with Price@". $poDetail->rate .$supplierName->name." with "."Delivery Charges@". $poDetail->delivery. " Tax@".$poDetail->tax." Discount@".$poDetail->discount,
                        $grn->id //posting id
                    );
                    // Calculate total value of previous stock
                    $previousStockValue = $productName->avg_price * $productName->qty;
                    // Extract charges from PO Detail
                    $rate     = $poDetail->rate;
                    $qty      = $receivedQty;
                    $delivery = $poDetail->delivery;
                    $tax      = $poDetail->tax_amount;
                    $discount = $poDetail->discount;
                    // Calculate net amount for received quantity (already saved in poDetail)
                    $netTotal = $rate * $qty + $tax + $delivery - $discount;
                    // Calculate net unit rate (real cost per unit after all charges)
                    $netUnitRate = $qty > 0 ? $netTotal / $qty : $rate;
                    // Calculate total new quantity and updated weighted average rate
                    $totalQTY     = $productName->qty + $qty;
                    $newAvgRate   = $totalQTY > 0 ? ($previousStockValue + $netTotal) / $totalQTY : $netUnitRate;
                    // Update product stock and average price
                    $productName->qty       = $totalQTY;
                    $productName->avg_price = $newAvgRate;
                    $productName->save();
                }
                
            }
            $this->updateSaleTransaction(
                $supplierHead, // head
                $productHead->id,//other head id
                $total_net_amount, //credit
                0, //debit
                $document_id, //document id
                "Generated GRN from ".$supplierName->name." with "."Delivery Charges@". $poDetail->delivery. "  Tax@".$poDetail->tax." Discount@'".$poDetail->discount,
                $grn->id //posting id
            );
            $mr = MaterialRequest::findOrFail($po->mr_id);
                $mr->status = 6;
                $mr->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Goods received and recorded successfully.',
                'grn_id' => $grn->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the GRN.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function getInwardDetails(Request $request)
    {
        $request->validate([
            'grn' => 'required|numeric',
        ]);
    
        $inward = GoodReceiveNote::with([
            'supplier',
            'purchaseOrder',
            'details.product'
        ])->findOrFail($request->grn);
    
        return response()->json($inward);
    }
     
    function accountHeadCreate($name, $first, $second, $third, $fourth) 
    {
        $existHead = AccountHead::where(["name"=>$name,"parent_account_id"=>$first,"account_id"=>$second,"parent_group_id"=>$third,"group_id"=>$fourth])->first();
        if($existHead)
        {
            return $existHead;
        }
        $code = AccountHead::latest('id')->where('group_id', $fourth )->limit(1)->value('code') + 1;
        $code = str_pad($code, 4, '0', STR_PAD_LEFT);
        $head = AccountHead::create([
            'name' => strtoupper($name),
            'code' => $code,
            'parent_account_id' => $first,
            'account_id' => $second,
            'parent_group_id' => $third,
            'group_id' => $fourth,
            'added_by' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
        ]);

        return $head;
    }
    function updateSaleTransaction($head,$other_id,$credit,$debit,$document_id,$narration,$posting_id) 
    {
        AccountTransaction::create([
            'terminal_id' => 1,
            'account_head_id' => $head->id,
            'other_account_head_id' => $other_id,
            'credit' => $credit,
            'debit' => $debit,
            'document_id' => $document_id,
            'type' => "JV",
            'narration' => strtoupper($narration),
            'posting_type' => 'GRN',
            'posting_id' => $posting_id,
            'approved' => 1,
            'approved_by' => 0,
            'parent_account_id' => $head->parent_account_id, 
            'account_id' => $head->account_id, 
            'parent_group_id' => $head->parent_group_id, 
            'group_id' => $head->group_id, 
            'added_by' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
        ]);
    }
}
    