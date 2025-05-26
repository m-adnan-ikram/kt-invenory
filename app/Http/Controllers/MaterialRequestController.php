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
use TCPDF;

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
    
    public function mrPDF(Request $request)
    {   
        return 'submit';
        die;
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('GA');
            $pdf->SetTitle('Jornal Entries');
    
            // set default header data
            // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 005', PDF_HEADER_STRING);
    
            // set header and footer fonts
            $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
            // set margins
            $pdf->SetMargins(4, PDF_MARGIN_TOP, 5);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
                require_once(dirname(__FILE__) . '/lang/eng.php');
                $pdf->setLanguageArray($l);
            }
            // ---------------------------------------------------------

            // set font
            $pdf->setPrintFooter(false);
            // add a page
            $pdf->AddPage('P', 'A4');
            // set color for background
    
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set color for background
            $pdf->SetFillColor(255, 255, 0);
            $pdf->SetFont('times', 'B', 16);
            $pdf->Ln(-18);
            $pdf->Cell(0, 0, strtoupper($company_name), 0, 1, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->SetFont('times', 'B', 13);
            $pdf->Cell(0, 0,strtoupper($transactions[0]->terminal->name??"General"), 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->SetFont('times', '', 12);
            $pdf->Ln(8);
            $pdf->Cell(0, 0, $heading, 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Ln();
            $pdf->Ln();
    
            $pdf->SetFont('times', '', 11);
            // ---------------------------------------------------------
            
            $pdf->MultiCell(130, 0, 'Particular  : ' . strtoupper( $particular ), 'B', 'L', 0, 0, '', '', true, 0, false, true, 13, 'T');
            $pdf->MultiCell(10, 5, '', 0, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
            $pdf->MultiCell(60, 5, 'Amount : ' . number_format($amount, 2, ".", ','), 'B', 'L', 0, 1, '', '', true, 0, false, true, 5, 'T');
            $pdf->MultiCell(130, 7, 'Voucher # : ' . $request->type.'-'.$request->id, 'B', 'L', 0, 0, '', '', true, 0, false, true, 7, 'B');
            $pdf->MultiCell(10, 5, '', 0, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
            $pdf->MultiCell(60, 7, 'Dated :  ' . date("d-m-Y", strtotime($dateTime)), 'B', 'L', 0, 1, '', '', true, 0, false, true, 7, 'B');
            $reference =  $transactions[0]->posting_type . '-' . $transactions[0]->posting_id;
            $pdf->MultiCell(130, 0, 'Reference # : ' . strtoupper($reference), 'B', 'L', 0, $request->type == 'BP' || $request->type == 'BR' ? 0 : 1, '', '', true, 0, false, true, 7, 'B');
            $pdf->MultiCell(10, 5, '', 0, 'R', 0, 0, '', '', true, 0, false, true, 5, 'M');
            if ($request->type == 'BP' || $request->type == 'BR' ) { 
                $pdf->MultiCell(60, 0, 'Cheque # '. $transactions[0]->cheque . '', 'B', 'L', 0, 1, '', '', true, 0, false, true, 7, 'B');   
            }
            $pdf->Ln(5);
            $pdf->SetFont('times', 'B', 10);
            // MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
            $pdf->MultiCell(8, 10, 'Sr No', 'TLR', 'L', 0, 0, '', '', true, 0, false, true, 10, 'M');
            $pdf->MultiCell(20, 10, 'Code', 'TR', 'L', 0, 0, '', '', true, 0, false, true, 10, 'M');
            $pdf->MultiCell(70, 10, 'Account Title', 'TR', 'L', 0, 0, '', '', true, 0, false, true, 10, 'M');
            //$pdf->MultiCell(15, 10, 'Account Code', 'TR', 'L', 0, 0, '', '', true, 0, false, true, 10, 'M');
            $pdf->MultiCell(70, 10, 'Narration', 'TR', 'L', 0, 0, '', '', true, 0, false, true, 10, 'M');
            $pdf->MultiCell(16, 10, 'Debit', 'TR', 'R', 0, 0, '', '', true, 0, false, true, 10, 'M');
            $pdf->MultiCell(16, 10, 'Credit', 'TR', 'R', 0, 1, '', '', true, 0, false, true, 10, 'M');
    
            $narration_length = strlen($transactions[0]->narration);
            if ($narration_length > 50) {
                $narration_length = 18;
            } else {
                $narration_length = 12;
            }
           
            foreach ($transactions as $i => $transaction) {
                // try{
                    $pdf->SetFont('times', '', 9);
                    $pdf->MultiCell(8, $narration_length, $i + 1, 1, 'L', 0, 0, '', '', true, 0, false, true, $narration_length, 'M');
                    
                    $head_name = strtoupper($transaction->account_head->name);
                    $group = $transaction->account_head->level_three->name;
                    $code = $transaction->account_head->level_one->code.'-'.$transaction->account_head->level_two->code.'-'.$transaction->account_head->level_three->code.'-'.$transaction->account_head->level_four->code.'-'.$transaction->account_head->code;
                    $pdf->MultiCell(20, $narration_length,  $code, 'TBR', 'L', 0, 0, '', '', true, 0, false, true, $narration_length, 'M');
        
                    $pdf->MultiCell(70, $narration_length, $head_name . ' - ( ' . $group . ' )', 'TBR', 'L', 0, 0, '', '', true, 0, false, true, $narration_length, 'M');
                    //$pdf->MultiCell(15, $narration_length, $transaction->head_name->code, 'TBR', 'L', 0, 0, '', '', true, 0, false, true, $narration_length, 'M');
                    $pdf->MultiCell(70, $narration_length,strtoupper( $transaction->narration ), 'TBR', 'L', 0, 0, '', '', true, 0, false, true, $narration_length, 'M');
                    $pdf->MultiCell(16, $narration_length, number_format($transaction->debit, 2), 'TBR', 'R', 0, 0, '', '', true, 0, false, true, $narration_length, 'M');
                    $pdf->MultiCell(16, $narration_length, number_format($transaction->credit, 2 ), 'TBR', 'R', 0, 1, '', '', true, 0, false, true, $narration_length, 'M');
                // }catch( Exception $e){
                    // return $transaction;
                // }
            }
            $pdf->MultiCell(8, 12, '', 1, 'L', 0, 0, '', '', true, 0, false, true, 12, 'M');
            $pdf->MultiCell(20, 12, '', 'TBR', 'L', 0, 0, '', '', true, 0, false, true, 0, 'M');
            $pdf->MultiCell(70, 12, '', 'TBR', 'L', 0, 0, '', '', true, 0, false, true, 12, 'M');
            $pdf->MultiCell(70, 12, 'Total : ', 'TBR', 'R', 0, 0, '', '', true, 0, false, true, 12, 'M');
            $pdf->MultiCell(16, 12, number_format($transactions->sum('debit') , 2), 'TBR', 'R', 0, 0, '', '', true, 0, false, true, 12, 'M');
            $pdf->MultiCell(16, 12, number_format($transactions->sum('credit'), 2), 'TBR', 'R', 0, 1, '', '', true, 0, false, true, 12, 'M');
            $pdf->Ln(5);
            $pdf->SetFont('times', '', 12);
            $Amount = $transactions->sum('debit');
            // $f = new NumberFormatter("PKR", NumberFormatter::SPELLOUT);
            $date_now = date('d-M-Y h:i A', strtotime(now()));
            $pdf->Ln(15);
            $pdf->SetFont('times', '', 11);
            $pdf->Cell(30, 0, '__________________', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(15, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, $transactions[0]->added_by_name->name, 'B', 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(15, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, ' __________________', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(10, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, '__________________', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(10, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, $transactions[0]->approved_by_name->name ?? '', 'B', 1, 'C', 0, '', 0, false, 'T', 'M');
    
            $pdf->Cell(30, 0, 'Received By ', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(15, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, 'Prepared By', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(15, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, 'Checked By', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(10, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, 'Finance Manager', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(10, 0, '', 0, 0, 'L', 0, '', 0, false, 'T', 'M');
            $pdf->Cell(30, 0, 'Approved By', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
    
            $pdf->Ln(10);
            $pdf->SetFont('times', 'B', 9);
            $status = $transactions[0]->approved ? "Approved" : "Pending";
            $pdf->Cell(0, 0, 'Printed By : ' . Auth::user()->name . ' || Dated : ' . $date_now . ' || Status : ' . $status, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
            $pdf->SetFont('times', '', 8);
            $pdf->Ln();
            $pdf->Cell(0, 0,'"Errors and omissions excepted" (E&OE)', 0, 0, 'C', 0, '', 0, false, 'T', 'M');
    
            return $pdf->Output('voucher.pdf', 'I');
    }
}
include(public_path().'/assets/tcpdf/tcpdf.php');
class MYPDF extends TCPDF
{
    public $heading, $terminal;

    public function Header()
    {
    }
    public function Footer()
    {
    }
}
