<?php

namespace App\Http\Controllers;

use App\Models\InvoiceModel;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PDFController extends Controller
{
    public function index(Request $request){


       $invoiceNumer = $this->generateInvoiceCode();

        return Inertia::render('Invoice/InvoiceTemplate',[
            'invoiceNumer' => $invoiceNumer,
        ]);
    }

public function upload(Request $request)
{
    $account = Auth::user()->branch_id;

    $request->validate([
        'pdf' => 'required|file|mimes:pdf|max:10240',
        'invoice_number' => 'required|string|unique:invoices,invoice_number',
    ]);

    $invoiceNumber = $request->input('invoice_number');
    $file = $request->file('pdf');

    // Store file
    $path = $file->storeAs(
        "invoices/branch_{$account}",
        $invoiceNumber . '.pdf',
        'public'
    );

    // Save to database
    InvoiceModel::create([
        'branch_id' => $account,
        'invoice_number' => $invoiceNumber,
        'file_path' => $path,
    ]);

    return response()->json([
        'success' => true,
        'path' => $path,
    ]);
}


    public function generateInvoiceCode(){
        do{
            $datePart = now()->format('Ymd');
            $randomPart = strtoupper(Str::random(5));
            $invoiceNumber = 'INV-'. $datePart . '-' . $randomPart;
            
        }while(InvoiceModel::where('invoice_number', $invoiceNumber)->exists());
        return $invoiceNumber;
    }
    public function getNewInvoiceNumber()
{
    $invoiceNumber = $this->generateInvoiceCode();
    return response()->json(['invoice_number' => $invoiceNumber]);
}

}
