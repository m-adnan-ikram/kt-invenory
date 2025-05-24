<?php

namespace App\Http\Controllers;

use App\Models\Account\AccountHead;
use App\Models\Account\AccountTransaction;
use App\Models\Inventory\MaterialRequest;
use App\Models\Inventory\MaterialRequestDetail;
use App\Models\Inventory\Product;
use App\Models\Inventory\StoreIssuanceNote;
use App\Models\Inventory\StoreIssuanceNoteDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOutwardController extends Controller
{
    //
    public function index()
{ 
        // Build a map of total issued quantity for each MR-product pair
        $issuedQtyMap = StoreIssuanceNoteDetail::with('storeIssuanceNote')
            ->get()
            ->groupBy(function ($detail) {
                return $detail->storeIssuanceNote->mr_id . '-' . $detail->product_id;
            })
            ->map(function ($group) {
                return $group->sum('qty');
            });

        // Fetch MRs where at least one detail has qty != store_issued_qty
        $mrs = MaterialRequest::with(['details.product', 'requestedByUser'])
            ->whereHas('details', function ($query) {
                $query->whereColumn('qty', '!=', 'store_issued_qty');
            })
            ->latest()
            ->get();

        // Inject issued_qty into each MR detail
        foreach ($mrs as $mr) {
            foreach ($mr->details as $detail) {
                $key = $mr->id . '-' . $detail->product_id;
                $detail->issued_qty = $issuedQtyMap[$key] ?? 0;
            }
        }

        // Fetch all Store Issuance Notes with related product details
        $outwards = StoreIssuanceNote::with(['details.product'])->latest()->get();

        return response()->json([
            'success'  => true,
            'message'  => 'Material Requests and Store Issuance Notes fetched successfully.',
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
                'added_by'     => auth()->id(),
                'company_id'   => Auth::user()->company_id,
            ]);
            $issuedNow = 0;
            
            $document = AccountTransaction::where(["company_id"=>Auth::user()->company_id])
            ->where("type","JV")
            ->orderBy("document_id","DESC")
            ->first();
            $document_id = $document ? $document->document_id + 1 : 1;  
            $storeHead = $this->accountHeadCreate(
                'STORE EXPENSE | EXPENSE LEDGER',
                5, // EXPENSES
                15, // OPERATING EXPENSES
                48, // OTHER EXPENSES
                60, // GENERAL EXPENSE
            );
            $total_net_amount=0;
            $sub_total=0;
            foreach ($request->details as $detail) {
                $product = Product::findOrFail($detail['product_id']);
                // Check stock
                if ($product->qty < $detail['qty']) {
                    throw new \Exception("Insufficient stock for product ID: {$product->id}");
                }
                // Reduce stock
                $product->decrement('qty', $detail['qty']);
                // Save issuance detail
                $sub_total = $detail['qty'] * $product->avg_price;
                StoreIssuanceNoteDetail::create([
                    'store_issuance_note_id' => $storeIssuance->id,
                    'product_id' => $detail['product_id'],
                    'qty'        => $detail['qty'],
                    'rate'       => $product->avg_price,
                    'total'      => $sub_total,
                    'company_id' => Auth::user()->company_id,
                ]);
                // Update issued qty in MR details
                MaterialRequestDetail::where('mr_id', $request->mr_id)
                    ->where('product_id', $detail['product_id'])
                    ->increment('store_issued_qty', $detail['qty']);
                $issuedNow += $detail['qty'];
                if ($product->product_head_id) {
                    $productHead = AccountHead::findOrFail($product->product_head_id);
                } else {
                    // Create new account head
                    $productHead = $this->accountHeadCreate(
                        $product->name . '-|PRODUCT LEDGER',
                        1,  // ASSETS
                        6,  // CURRENT ASSETS
                        15, // STORE AND SPARES
                        18  // GENERAL PARTS
                    );
                    // Save the new account head ID to the product
                    $product->product_head_id = $productHead->id;
                    $product->save();
                } 
                $total_net_amount += $sub_total;
                $this->updateSaleTransaction(
                    $productHead, // head
                    $storeHead->id,//other head id
                    $sub_total, //credit
                    0, //debit
                    $document_id, //document id
                    "Generated Issuance of ".$product->name." Issued QTY is @". $detail['qty'] ."' Rate this Issuance is @".$product->avg_price,
                    $storeIssuance->id //posting id
                ); 
            }
            $this->updateSaleTransaction(
                $storeHead, // head
                $productHead->id,//other head id
                0, //credit
                $total_net_amount, //debit
                $document_id, //document id
                "Generated Issuance of ".$product->name." Issued QTY is @". $detail['qty'] ."' Rate this Issuance is @".$product->avg_price,
                $storeIssuance->id //posting id
            );
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
            'posting_type' => 'SIN',
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

