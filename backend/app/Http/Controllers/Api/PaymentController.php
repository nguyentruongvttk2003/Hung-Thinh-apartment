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
            'payment_method' => 'required|in:cash,bank_transfer,qr_code,credit_card,e_wallet',
            'transaction_id' => 'nullable|string',
            'payment_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        // Generate unique payment number
        $paymentNumber = 'PAY-' . date('Y') . '-' . str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT);

        $payment = Payment::create([
            'payment_number' => $paymentNumber,
            'invoice_id' => $request->invoice_id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'payment_details' => $request->payment_details,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment->load(['invoice.apartment', 'user']),
            'message' => 'Thanh toán đã được tạo thành công'
        ], 201);
    }

    public function show($id)
    {
        $payment = Payment::with(['invoice.apartment', 'user', 'processor'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $payment,
            'message' => 'Payment retrieved successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        // Only allow updating pending payments
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể cập nhật các thanh toán đang chờ xử lý'
            ], 400);
        }

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,qr_code,credit_card,e_wallet',
            'transaction_id' => 'nullable|string',
            'payment_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $payment->update([
            'invoice_id' => $request->invoice_id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'payment_details' => $request->payment_details,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment->load(['invoice.apartment', 'user']),
            'message' => 'Thanh toán đã được cập nhật thành công'
        ]);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        
        // Only allow deleting pending or failed payments
        if (!in_array($payment->status, ['pending', 'failed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa các thanh toán chờ xử lý hoặc thất bại'
            ], 400);
        }

        $payment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Thanh toán đã được xóa thành công'
        ]);
    }

    public function process(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xử lý các thanh toán đang chờ'
            ], 400);
        }

        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        // Update invoice payment status
        $invoice = $payment->invoice;
        $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        }

        return response()->json([
            'success' => true,
            'data' => $payment->load(['invoice.apartment', 'user', 'processor']),
            'message' => 'Thanh toán đã được xử lý thành công'
        ]);
    }

    public function byInvoice($invoiceId)
    {
        $payments = Payment::with(['user'])
            ->where('invoice_id', $invoiceId)
            ->latest()
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $payments,
            'message' => 'Invoice payments retrieved successfully'
        ]);
    }

    public function myPayments()
    {
        $payments = Payment::with(['invoice.apartment'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $payments,
            'message' => 'My payments retrieved successfully'
        ]);
    }

    public function stats(Request $request)
    {
        try {
            $query = Payment::query();
            
            // Apply date range filter if provided
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            $stats = [
                'total_payments' => $query->count(),
                'total_amount' => $query->where('status', 'completed')->sum('amount'),
                'pending_payments' => $query->where('status', 'pending')->count(),
                'completed_payments' => $query->where('status', 'completed')->count(),
                'failed_payments' => $query->where('status', 'failed')->count(),
                'by_method' => Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                    ->where('status', 'completed')
                    ->groupBy('payment_method')
                    ->get(),
                'by_status' => Payment::selectRaw('status, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('status')
                    ->get(),
                'monthly_stats' => Payment::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count, SUM(amount) as total')
                    ->where('status', 'completed')
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->limit(12)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Payment statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}
