<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::latest()->paginate(15);
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        $payment = Payment::create([
            'invoice_id' => $request->invoice_id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);

        return response()->json($payment, 201);
    }

    public function show($id)
    {
        $payment = Payment::findOrFail($id);
        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update($request->all());
        return response()->json($payment);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully']);
    }

    public function process(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);
        return response()->json($payment);
    }

    public function byInvoice($invoiceId)
    {
        $payments = Payment::where('invoice_id', $invoiceId)->get();
        return response()->json($payments);
    }

    public function myPayments()
    {
        $payments = Payment::where('user_id', auth()->id())->latest()->get();
        return response()->json($payments);
    }
}
