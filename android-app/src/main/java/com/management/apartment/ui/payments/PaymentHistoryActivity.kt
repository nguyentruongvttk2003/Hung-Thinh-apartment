package com.management.apartment.ui.payments

import android.os.Bundle
import android.view.View
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.R
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityPaymentHistoryBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class PaymentHistoryActivity: AppCompatActivity() {
    private lateinit var binding: ActivityPaymentHistoryBinding
    private lateinit var adapter: PaymentsAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPaymentHistoryBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(R.id.toolbar))
        findViewById<MaterialToolbar?>(R.id.toolbar)?.setNavigationOnClickListener { finish() }

        adapter = PaymentsAdapter(emptyList())
        binding.recyclerPayments.layoutManager = LinearLayoutManager(this)
        binding.recyclerPayments.adapter = adapter
        load()
    }

    private fun load() {
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        binding.progress.visibility = View.VISIBLE
        api.myPayments("Bearer $token").enqueue(object: Callback<List<ApiEndpoints.PaymentDto>> {
            override fun onResponse(call: Call<List<ApiEndpoints.PaymentDto>>, response: Response<List<ApiEndpoints.PaymentDto>>) {
                binding.progress.visibility = View.GONE
                adapter.update(response.body() ?: emptyList())
            }
            override fun onFailure(call: Call<List<ApiEndpoints.PaymentDto>>, t: Throwable) {
                binding.progress.visibility = View.GONE
                Toast.makeText(this@PaymentHistoryActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }
}

private class PaymentsAdapter(private var items: List<ApiEndpoints.PaymentDto>) : androidx.recyclerview.widget.RecyclerView.Adapter<PaymentsVH>() {
    override fun onCreateViewHolder(parent: android.view.ViewGroup, viewType: Int): PaymentsVH {
        val v = android.view.LayoutInflater.from(parent.context).inflate(android.R.layout.simple_list_item_2, parent, false)
        return PaymentsVH(v)
    }
    override fun getItemCount(): Int = items.size
    override fun onBindViewHolder(holder: PaymentsVH, position: Int) = holder.bind(items[position])
    fun update(newItems: List<ApiEndpoints.PaymentDto>) { items = newItems; notifyDataSetChanged() }
}

private class PaymentsVH(itemView: android.view.View) : androidx.recyclerview.widget.RecyclerView.ViewHolder(itemView) {
    private val t1: TextView = itemView.findViewById(android.R.id.text1)
    private val t2: TextView = itemView.findViewById(android.R.id.text2)
    fun bind(p: ApiEndpoints.PaymentDto) {
        t1.text = "#${p.id} - ${p.status.uppercase()} - ${String.format("%,.0f", p.amount)} VNĐ"
        t2.text = "Invoice: ${p.invoice_id ?: "-"} • ${p.payment_method ?: "--"} • ${p.created_at ?: "--"}"
    }
}


