package com.management.apartment.ui.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.FragmentInvoicesBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import com.management.apartment.network.InvoiceDto
import com.management.apartment.network.WrappedList
import com.management.apartment.ui.invoices.InvoiceAdapter

class InvoicesFragment: Fragment() {
    private var _binding: FragmentInvoicesBinding? = null
    private val binding get() = _binding!!
    private var invoices: List<InvoiceDto> = emptyList()
    private var adapter: InvoiceAdapter? = null

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentInvoicesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        view.findViewById<MaterialToolbar?>(com.management.apartment.R.id.toolbar)?.setNavigationOnClickListener {
            requireActivity().onBackPressedDispatcher.onBackPressed()
        }
        binding.recyclerViewInvoices.layoutManager = LinearLayoutManager(requireContext())
        adapter = InvoiceAdapter(emptyList()) { invoice ->
            val intent = android.content.Intent(requireContext(), com.management.apartment.ui.invoices.InvoiceDetailActivity::class.java)
            intent.putExtra("invoice_id", invoice.id)
            startActivity(intent)
        }
        binding.recyclerViewInvoices.adapter = adapter
        loadInvoices()

        // Handle create invoice (only as demo for now)
        view.findViewById<com.google.android.material.floatingactionbutton.FloatingActionButton?>(com.management.apartment.R.id.fabCreateInvoice)?.setOnClickListener {
            startActivity(android.content.Intent(requireContext(), com.management.apartment.ui.invoices.InvoiceCreateActivity::class.java))
        }
    }

    private fun loadInvoices() {
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        showLoading(true)
        api.invoices("Bearer $token").enqueue(object: retrofit2.Callback<WrappedList<InvoiceDto>> {
            override fun onResponse(
                call: retrofit2.Call<WrappedList<InvoiceDto>>,
                response: retrofit2.Response<WrappedList<InvoiceDto>>
            ) {
                invoices = response.body()?.data ?: emptyList()
                showLoading(false)
                adapter?.update(invoices)
                updateStates()
                updateSummary()
            }
            override fun onFailure(call: retrofit2.Call<WrappedList<InvoiceDto>>, t: Throwable) {
                showLoading(false)
                invoices = emptyList()
                updateStates()
            }
        })
    }

    private fun showLoading(loading: Boolean) {
        binding.layoutLoadingInvoices.visibility = if (loading) View.VISIBLE else View.GONE
    }

    private fun updateStates() {
        val empty = invoices.isEmpty()
        binding.layoutEmptyStateInvoices.visibility = if (empty) View.VISIBLE else View.GONE
        binding.recyclerViewInvoices.visibility = if (empty) View.GONE else View.VISIBLE
    }

    private fun updateSummary() {
        val totalAmount = invoices.sumOf { it.total_amount }
        val outstanding = invoices.filter { it.status != "paid" }.sumOf { it.total_amount }
        binding.textTotalAmount.text = String.format("%,.0f VNĐ", totalAmount)
        binding.textOutstandingAmount.text = String.format("%,.0f VNĐ", outstanding)

        // Filter Tab interactions: update list based on selected tab
        binding.tabLayoutInvoices.addOnTabSelectedListener(object: com.google.android.material.tabs.TabLayout.OnTabSelectedListener {
            override fun onTabSelected(tab: com.google.android.material.tabs.TabLayout.Tab?) {
                val filtered = when (tab?.position) {
                    1 -> invoices.filter { it.status.equals("pending", true) || it.status.equals("partial", true) }
                    2 -> invoices.filter { it.status.equals("paid", true) }
                    3 -> invoices.filter { it.status.equals("overdue", true) }
                    else -> invoices
                }
                adapter?.update(filtered)
            }
            override fun onTabUnselected(tab: com.google.android.material.tabs.TabLayout.Tab?) {}
            override fun onTabReselected(tab: com.google.android.material.tabs.TabLayout.Tab?) { onTabSelected(tab) }
        })
    }

    private fun showCreateInvoiceDialog() {
        val ctx = requireContext()
        val container = android.widget.LinearLayout(ctx).apply {
            orientation = android.widget.LinearLayout.VERTICAL
            setPadding(32, 16, 32, 0)
        }
        val inputApartmentId = android.widget.EditText(ctx).apply { hint = "Apartment ID"; inputType = android.text.InputType.TYPE_CLASS_NUMBER }
        val inputMonth = android.widget.EditText(ctx).apply { hint = "Tháng"; inputType = android.text.InputType.TYPE_CLASS_NUMBER }
        val inputYear = android.widget.EditText(ctx).apply { hint = "Năm"; inputType = android.text.InputType.TYPE_CLASS_NUMBER }
        val inputTotal = android.widget.EditText(ctx).apply { hint = "Tổng tiền"; inputType = android.text.InputType.TYPE_CLASS_NUMBER }
        val inputDue = android.widget.EditText(ctx).apply { hint = "Hạn thanh toán (YYYY-MM-DD)" }
        container.addView(inputApartmentId)
        container.addView(inputMonth)
        container.addView(inputYear)
        container.addView(inputTotal)
        container.addView(inputDue)
        androidx.appcompat.app.AlertDialog.Builder(ctx)
            .setTitle("Tạo hóa đơn nhanh")
            .setView(container)
            .setNegativeButton("Hủy", null)
            .setPositiveButton("Tạo") { _, _ ->
                val aptId = inputApartmentId.text.toString().toLongOrNull() ?: return@setPositiveButton
                val month = inputMonth.text.toString().toIntOrNull() ?: return@setPositiveButton
                val year = inputYear.text.toString().toIntOrNull() ?: return@setPositiveButton
                val total = inputTotal.text.toString().toDoubleOrNull() ?: return@setPositiveButton
                val due = inputDue.text.toString().ifEmpty { java.time.LocalDate.now().plusDays(7).toString() }
                createInvoice(aptId, month, year, total, due)
            }
            .show()
    }

    private fun createInvoice(apartmentId: Long, month: Int, year: Int, total: Double, due: String) {
        val token = com.management.apartment.data.SessionManager(requireContext()).getToken() ?: return
        val api = com.management.apartment.network.ApiService.retrofit.create(com.management.apartment.network.ApiEndpoints::class.java)
        val body = com.management.apartment.network.ApiEndpoints.CreateInvoiceRequest(
            apartment_id = apartmentId,
            month = month,
            year = year,
            management_fee = 0.0,
            electricity_fee = 0.0,
            water_fee = 0.0,
            parking_fee = 0.0,
            other_fees = 0.0,
            total_amount = total,
            due_date = due,
            notes = null
        )
        api.createInvoice("Bearer $token", body).enqueue(object: retrofit2.Callback<com.management.apartment.network.GenericResponse<Any>> {
            override fun onResponse(
                call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>,
                response: retrofit2.Response<com.management.apartment.network.GenericResponse<Any>>
            ) {
                android.widget.Toast.makeText(requireContext(), if (response.isSuccessful) "Đã tạo hóa đơn" else "Tạo thất bại", android.widget.Toast.LENGTH_SHORT).show()
                loadInvoices()
            }
            override fun onFailure(call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>, t: Throwable) {
                android.widget.Toast.makeText(requireContext(), t.message, android.widget.Toast.LENGTH_SHORT).show()
            }
        })
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}


