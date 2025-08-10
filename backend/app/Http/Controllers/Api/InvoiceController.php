<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Invoice::with(['apartment']);
            
            // Filter by apartment if provided
            if ($request->has('apartment_id')) {
                $query->where('apartment_id', $request->apartment_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by month/year if provided
            if ($request->has('month')) {
                $query->where('month', $request->month);
            }
            if ($request->has('year')) {
                $query->where('year', $request->year);
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
            'description' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::create([
            'apartment_id' => $request->apartment_id,
            'description' => $request->description,
            'total_amount' => $request->total_amount,
            'paid_amount' => 0,
            'status' => 'pending',
            'due_date' => $request->due_date ?? now()->addDays(30),
        ]);

        return response()->json($invoice, 201);
    }

    public function show($id)
    {
        $invoice = Invoice::findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update($request->all());
        return response()->json($invoice);
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted successfully']);
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
}
