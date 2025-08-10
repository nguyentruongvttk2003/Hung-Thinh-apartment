<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            \Log::info('PaymentController index called', [
                'params' => $request->all(),
                'user' => auth()->user()?->id
            ]);

            $query = Payment::query();
            
            \Log::info('Initial payment count', ['count' => $query->count()]);

            // Filter by invoice if provided - only if not empty
            if ($request->has('invoice_id') && !empty($request->invoice_id)) {
                $query->where('invoice_id', $request->invoice_id);
                \Log::info('Filtered by invoice_id', ['invoice_id' => $request->invoice_id, 'count' => $query->count()]);
            }

            // Filter by payment method if provided - only if not empty
            if ($request->has('payment_method') && !empty($request->payment_method)) {
                $query->where('payment_method', $request->payment_method);
                \Log::info('Filtered by payment_method', ['payment_method' => $request->payment_method, 'count' => $query->count()]);
            }

            // Filter by status if provided - only if not empty
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
                \Log::info('Filtered by status', ['status' => $request->status, 'count' => $query->count()]);
            }

            // Filter by date range if provided - only if not empty
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('created_at', '>=', $request->from_date);
                \Log::info('Filtered by from_date', ['from_date' => $request->from_date, 'count' => $query->count()]);
            }
            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('created_at', '<=', $request->to_date);
                \Log::info('Filtered by to_date', ['to_date' => $request->to_date, 'count' => $query->count()]);
            }

            \Log::info('Final query count before pagination', ['count' => $query->count()]);

            // Add relationships
            $query->with(['invoice.apartment', 'user']);

            $payments = $query->latest()->paginate($request->get('per_page', 15));

            \Log::info('Paginated result', [
                'items_count' => $payments->count(),
                'total' => $payments->total(),
                'current_page' => $payments->currentPage()
            ]);

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'message' => 'Payments retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách thanh toán: ' . $e->getMessage()
            ], 500);
        }
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
