<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Invoice::with(['apartment', 'creator']);
            
            // Filter by apartment if provided
            if ($request->has('apartment_id')) {
                $query->where('apartment_id', $request->apartment_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by month/year if provided
            if ($request->has('month') && $request->has('year')) {
                $billingStart = now()->createFromDate($request->year, $request->month, 1);
                $billingEnd = $billingStart->copy()->endOfMonth();
                $query->whereBetween('billing_period_start', [$billingStart, $billingEnd]);
            }

            $invoices = $query->latest()->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $invoices->items(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'message' => 'Invoices retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải hóa đơn: ' . $e->getMessage()
            ], 500);
        }
    }

    private function mapInvoiceStatus($status)
    {
        switch ($status) {
            case 'pending':
                return 'pending';
            case 'paid':
                return 'paid';
            case 'overdue':
                return 'overdue';
            default:
                return 'pending';
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'management_fee' => 'required|numeric|min:0',
            'electricity_fee' => 'required|numeric|min:0',
            'water_fee' => 'required|numeric|min:0',
            'parking_fee' => 'required|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Generate unique invoice number
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);

        // Calculate billing period based on month and year
        $billingStart = now()->createFromDate($request->year, $request->month, 1);
        $billingEnd = $billingStart->copy()->endOfMonth();

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'apartment_id' => $request->apartment_id,
            'billing_period_start' => $billingStart,
            'billing_period_end' => $billingEnd,
            'due_date' => $request->due_date,
            'management_fee' => $request->management_fee,
            'electricity_fee' => $request->electricity_fee,
            'water_fee' => $request->water_fee,
            'parking_fee' => $request->parking_fee,
            'other_fees' => $request->other_fees ?? 0,
            'total_amount' => $request->total_amount,
            'paid_amount' => 0,
            'status' => 'pending',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $invoice->load('apartment'),
            'message' => 'Hóa đơn đã được tạo thành công'
        ], 201);
    }

    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'management_fee' => 'required|numeric|min:0',
            'electricity_fee' => 'required|numeric|min:0',
            'water_fee' => 'required|numeric|min:0',
            'parking_fee' => 'required|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($id);

        // Calculate billing period based on month and year
        $billingStart = now()->createFromDate($request->year, $request->month, 1);
        $billingEnd = $billingStart->copy()->endOfMonth();

        $invoice->update([
            'apartment_id' => $request->apartment_id,
            'billing_period_start' => $billingStart,
            'billing_period_end' => $billingEnd,
            'due_date' => $request->due_date,
            'management_fee' => $request->management_fee,
            'electricity_fee' => $request->electricity_fee,
            'water_fee' => $request->water_fee,
            'parking_fee' => $request->parking_fee,
            'other_fees' => $request->other_fees ?? 0,
            'total_amount' => $request->total_amount,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $invoice->load('apartment'),
            'message' => 'Hóa đơn đã được cập nhật thành công'
        ]);
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Hóa đơn đã được xóa thành công'
        ]);
    }

    public function byApartment($apartmentId)
    {
        $invoices = Invoice::where('apartment_id', $apartmentId)->latest()->get();
        return response()->json($invoices);
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'invoices' => 'required|array',
            'invoices.*.apartment_id' => 'required|exists:apartments,id',
            'invoices.*.description' => 'required|string',
            'invoices.*.total_amount' => 'required|numeric|min:0',
        ]);

        $invoices = [];
        foreach ($request->invoices as $invoiceData) {
            $invoices[] = Invoice::create($invoiceData);
        }

        return response()->json($invoices, 201);
    }

    public function myInvoices()
    {
        $user = auth()->user();
        $apartmentIds = $user->residences()->pluck('apartment_id');
        $invoices = Invoice::whereIn('apartment_id', $apartmentIds)->latest()->get();
        return response()->json($invoices);
    }

    /**
     * Generate QR payload for invoice payment
     */
    public function qrPayload($id)
    {
        $invoice = Invoice::with('apartment')->findOrFail($id);
        $user = auth()->user();

        // Create a pending payment record if not exists for this intent (idempotent by invoice + user + method)
        $payment = Payment::firstOrCreate([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_method' => 'qr_code',
        ], [
            'amount' => $invoice->total_amount - ($invoice->paid_amount ?? 0),
            'payment_number' => 'PAY-' . date('Y') . '-' . str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT),
        ]);

        // Simple VNPay-like payload (demo). Mobile app will render QR from this string
        $payload = [
            'merchant' => 'HT_APT_MGMT',
            'invoice' => $invoice->invoice_number,
            'amount' => (int) round($payment->amount),
            'currency' => 'VND',
            'description' => 'Thanh toan hoa don ' . $invoice->invoice_number,
            'reference' => $payment->payment_number,
            'callback' => url('/api/payments/' . $payment->id . '/process'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'qr_string' => http_build_query($payload),
                'payload' => $payload,
            ],
        ]);
    }
}
