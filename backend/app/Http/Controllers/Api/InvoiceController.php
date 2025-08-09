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
            $user = auth()->user();
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            
            $invoices = Invoice::where('apartment_id', $user->apartment_id)
                ->latest()
                ->paginate($limit, ['*'], 'page', $page);

            // Format invoices for mobile app
            $invoicesData = $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'title' => $invoice->description,
                    'amount' => $invoice->total_amount,
                    'status' => $this->mapInvoiceStatus($invoice->status),
                    'dueDate' => $invoice->due_date,
                    'description' => $invoice->description,
                    'createdAt' => $invoice->created_at->toISOString(),
                    'updatedAt' => $invoice->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $invoicesData->values(),
                    'currentPage' => $invoices->currentPage(),
                    'totalPages' => $invoices->lastPage(),
                    'total' => $invoices->total(),
                ],
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
