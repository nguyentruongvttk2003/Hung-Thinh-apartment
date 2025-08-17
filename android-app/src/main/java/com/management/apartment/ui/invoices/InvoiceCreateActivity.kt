package com.management.apartment.ui.invoices

import android.os.Bundle
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.R
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityInvoiceCreateBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService

class InvoiceCreateActivity: AppCompatActivity() {
    private lateinit var binding: ActivityInvoiceCreateBinding
    private var apartments: List<com.management.apartment.network.ApartmentSimple> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityInvoiceCreateBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(R.id.toolbar))
        findViewById<MaterialToolbar?>(R.id.toolbar)?.setNavigationOnClickListener { finish() }

        loadApartments()
        binding.btnSave.setOnClickListener { createInvoice() }
        val watcher = object: android.text.TextWatcher {
            override fun afterTextChanged(s: android.text.Editable?) { updateTotal() }
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
        }
        binding.inManagementFee.addTextChangedListener(watcher)
        binding.inElectricity.addTextChangedListener(watcher)
        binding.inWater.addTextChangedListener(watcher)
        binding.inParking.addTextChangedListener(watcher)
        binding.inOther.addTextChangedListener(watcher)
    }

    private fun updateTotal() {
        fun num(v: String?) = v?.toDoubleOrNull() ?: 0.0
        val total = num(binding.inManagementFee.text?.toString()) + num(binding.inElectricity.text?.toString()) + num(binding.inWater.text?.toString()) + num(binding.inParking.text?.toString()) + num(binding.inOther.text?.toString())
        binding.textTotal.text = "Tổng: %,.0f VNĐ".format(total)
    }

    private fun loadApartments() {
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.apartments("Bearer $token").enqueue(object: retrofit2.Callback<com.management.apartment.network.WrappedList<com.management.apartment.network.ApartmentSimple>> {
            override fun onResponse(
                call: retrofit2.Call<com.management.apartment.network.WrappedList<com.management.apartment.network.ApartmentSimple>>,
                response: retrofit2.Response<com.management.apartment.network.WrappedList<com.management.apartment.network.ApartmentSimple>>
            ) {
                apartments = response.body()?.data ?: emptyList()
                val names = apartments.map { it.apartment_number ?: "#${it.id}" }
                binding.spinnerApartment.adapter = ArrayAdapter(this@InvoiceCreateActivity, android.R.layout.simple_spinner_dropdown_item, names)
            }
            override fun onFailure(call: retrofit2.Call<com.management.apartment.network.WrappedList<com.management.apartment.network.ApartmentSimple>>, t: Throwable) {
                Toast.makeText(this@InvoiceCreateActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun createInvoice() {
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        val apt = apartments.getOrNull(binding.spinnerApartment.selectedItemPosition) ?: run { Toast.makeText(this, "Chọn căn hộ", Toast.LENGTH_SHORT).show(); return }
        val month = binding.inMonth.text?.toString()?.toIntOrNull() ?: run { Toast.makeText(this, "Nhập tháng", Toast.LENGTH_SHORT).show(); return }
        val year = binding.inYear.text?.toString()?.toIntOrNull() ?: run { Toast.makeText(this, "Nhập năm", Toast.LENGTH_SHORT).show(); return }
        val due = binding.inDueDate.text?.toString()?.ifEmpty { java.time.LocalDate.now().plusDays(7).toString() } ?: java.time.LocalDate.now().plusDays(7).toString()
        fun num(v: String?) = v?.toDoubleOrNull() ?: 0.0
        val body = ApiEndpoints.CreateInvoiceRequest(
            apartment_id = apt.id,
            month = month,
            year = year,
            management_fee = num(binding.inManagementFee.text?.toString()),
            electricity_fee = num(binding.inElectricity.text?.toString()),
            water_fee = num(binding.inWater.text?.toString()),
            parking_fee = num(binding.inParking.text?.toString()),
            other_fees = num(binding.inOther.text?.toString()),
            total_amount = 0.0, // server có thể tính lại; hoặc set = tổng phí
            due_date = due,
            notes = null
        )
        api.createInvoice("Bearer $token", body).enqueue(object: retrofit2.Callback<com.management.apartment.network.GenericResponse<Any>> {
            override fun onResponse(
                call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>,
                response: retrofit2.Response<com.management.apartment.network.GenericResponse<Any>>
            ) {
                Toast.makeText(this@InvoiceCreateActivity, if (response.isSuccessful) "Đã tạo hóa đơn" else "Tạo thất bại (${response.code()})", Toast.LENGTH_SHORT).show()
                if (response.isSuccessful) finish()
            }
            override fun onFailure(call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>, t: Throwable) {
                Toast.makeText(this@InvoiceCreateActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }
}


