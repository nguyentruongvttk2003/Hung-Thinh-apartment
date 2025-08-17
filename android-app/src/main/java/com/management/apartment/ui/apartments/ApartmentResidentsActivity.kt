package com.management.apartment.ui.apartments

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.R
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityApartmentResidentsBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ApartmentResidentsActivity : AppCompatActivity() {
    private lateinit var binding: ActivityApartmentResidentsBinding
    private var apartmentId: Long = -1
    private lateinit var adapter: ResidentsAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityApartmentResidentsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(R.id.toolbar))
        findViewById<MaterialToolbar?>(R.id.toolbar)?.setNavigationOnClickListener { finish() }

        apartmentId = intent.getLongExtra("apartment_id", -1)
        if (apartmentId <= 0) { finish(); return }

        adapter = ResidentsAdapter(emptyList(), onRemove = { residentId -> removeResident(residentId) })
        binding.recyclerResidents.layoutManager = LinearLayoutManager(this)
        binding.recyclerResidents.adapter = adapter

        binding.btnAdd.setOnClickListener { addResident() }
        loadResidents()
    }

    private fun tokenOrNull(): String? = SessionManager(this).getToken()

    private fun loadResidents() {
        val token = tokenOrNull() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        binding.progress.visibility = View.VISIBLE
        api.apartmentResidents("Bearer $token", apartmentId).enqueue(object: Callback<List<ApiEndpoints.ResidentDto>> {
            override fun onResponse(
                call: Call<List<ApiEndpoints.ResidentDto>>,
                response: Response<List<ApiEndpoints.ResidentDto>>
            ) {
                binding.progress.visibility = View.GONE
                adapter.update(response.body() ?: emptyList())
            }
            override fun onFailure(call: Call<List<ApiEndpoints.ResidentDto>>, t: Throwable) {
                binding.progress.visibility = View.GONE
                Toast.makeText(this@ApartmentResidentsActivity, t.message ?: "Lỗi tải cư dân", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun addResident() {
        val token = tokenOrNull() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        val userId = binding.inputUserId.text?.toString()?.toLongOrNull()
        val relationship = binding.inputRelationship.text?.toString()?.trim()
        if (userId == null || relationship.isNullOrBlank()) {
            Toast.makeText(this, "Nhập User ID và quan hệ", Toast.LENGTH_SHORT).show(); return
        }
        val body = mapOf(
            "user_id" to userId,
            "relationship" to relationship,
            "move_in_date" to java.time.LocalDate.now().toString(),
            "is_primary_contact" to false
        )
        binding.progress.visibility = View.VISIBLE
        api.addResident("Bearer $token", apartmentId, body).enqueue(object: Callback<com.management.apartment.network.GenericResponse<ApiEndpoints.ResidentDto>> {
            override fun onResponse(
                call: Call<com.management.apartment.network.GenericResponse<ApiEndpoints.ResidentDto>>,
                response: Response<com.management.apartment.network.GenericResponse<ApiEndpoints.ResidentDto>>
            ) {
                binding.progress.visibility = View.GONE
                if (response.isSuccessful && response.body()?.success == true) {
                    Toast.makeText(this@ApartmentResidentsActivity, "Đã thêm cư dân", Toast.LENGTH_SHORT).show()
                    binding.inputUserId.setText("")
                    binding.inputRelationship.setText("")
                    loadResidents()
                } else {
                    Toast.makeText(this@ApartmentResidentsActivity, "Thêm thất bại (${response.code()})", Toast.LENGTH_SHORT).show()
                }
            }
            override fun onFailure(
                call: Call<com.management.apartment.network.GenericResponse<ApiEndpoints.ResidentDto>>,
                t: Throwable
            ) {
                binding.progress.visibility = View.GONE
                Toast.makeText(this@ApartmentResidentsActivity, t.message ?: "Lỗi kết nối", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun removeResident(residentId: Long) {
        val token = tokenOrNull() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        binding.progress.visibility = View.VISIBLE
        api.removeResident("Bearer $token", apartmentId, residentId).enqueue(object: Callback<com.management.apartment.network.GenericResponse<Any>> {
            override fun onResponse(
                call: Call<com.management.apartment.network.GenericResponse<Any>>,
                response: Response<com.management.apartment.network.GenericResponse<Any>>
            ) {
                binding.progress.visibility = View.GONE
                if (response.isSuccessful) {
                    Toast.makeText(this@ApartmentResidentsActivity, "Đã xóa", Toast.LENGTH_SHORT).show()
                    loadResidents()
                } else {
                    Toast.makeText(this@ApartmentResidentsActivity, "Xóa thất bại (${response.code()})", Toast.LENGTH_SHORT).show()
                }
            }
            override fun onFailure(call: Call<com.management.apartment.network.GenericResponse<Any>>, t: Throwable) {
                binding.progress.visibility = View.GONE
                Toast.makeText(this@ApartmentResidentsActivity, t.message ?: "Lỗi kết nối", Toast.LENGTH_SHORT).show()
            }
        })
    }
}

private class ResidentsAdapter(
    private var items: List<ApiEndpoints.ResidentDto>,
    private val onRemove: (Long) -> Unit
) : androidx.recyclerview.widget.RecyclerView.Adapter<ResidentsViewHolder>() {
    override fun onCreateViewHolder(parent: android.view.ViewGroup, viewType: Int): ResidentsViewHolder {
        val v = android.view.LayoutInflater.from(parent.context).inflate(android.R.layout.simple_list_item_2, parent, false)
        return ResidentsViewHolder(v, onRemove)
    }
    override fun getItemCount(): Int = items.size
    override fun onBindViewHolder(holder: ResidentsViewHolder, position: Int) = holder.bind(items[position])
    fun update(newItems: List<ApiEndpoints.ResidentDto>) { items = newItems; notifyDataSetChanged() }
}

private class ResidentsViewHolder(
    itemView: android.view.View,
    private val onRemove: (Long) -> Unit
) : androidx.recyclerview.widget.RecyclerView.ViewHolder(itemView) {
    private val title = itemView.findViewById<android.widget.TextView>(android.R.id.text1)
    private val subtitle = itemView.findViewById<android.widget.TextView>(android.R.id.text2)
    init {
        itemView.setOnLongClickListener {
            val id = it.tag as? Long ?: return@setOnLongClickListener false
            onRemove(id)
            true
        }
    }
    fun bind(item: ApiEndpoints.ResidentDto) {
        itemView.tag = item.id
        title.text = item.user?.name ?: "User #${item.user_id}"
        subtitle.text = "${item.relationship} • ${item.status ?: "active"}"
    }
}


