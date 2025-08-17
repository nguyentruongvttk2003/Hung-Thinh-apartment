package com.management.apartment.ui.amenities

import android.os.Bundle
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityAmenityAvailabilityBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class AmenityAvailabilityActivity: AppCompatActivity() {
    private lateinit var binding: ActivityAmenityAvailabilityBinding
    private var amenityId: Long = -1

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAmenityAvailabilityBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(com.management.apartment.R.id.toolbar))
        findViewById<MaterialToolbar?>(com.management.apartment.R.id.toolbar)?.setNavigationOnClickListener { finish() }

        amenityId = intent.getLongExtra("amenity_id", -1)
        if (amenityId <= 0) { finish(); return }

        binding.btnLoad.setOnClickListener { loadAvailability() }
        binding.btnBook.setOnClickListener { bookSelected() }
    }

    private fun loadAvailability() {
        val date = binding.inputDate.text.toString().ifEmpty { java.time.LocalDate.now().toString() }
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.amenityAvailability("Bearer $token", amenityId, date).enqueue(object: Callback<com.management.apartment.network.AmenityAvailability> {
            override fun onResponse(call: Call<com.management.apartment.network.AmenityAvailability>, response: Response<com.management.apartment.network.AmenityAvailability>) {
                val slots = response.body()?.data?.slots ?: emptyList()
                val items = slots.map { "${it.start} - ${it.end} ${if (it.available) "(Trống)" else "(Đã đặt)"}" }
                binding.listSlots.adapter = ArrayAdapter(this@AmenityAvailabilityActivity, android.R.layout.simple_list_item_single_choice, items)
                binding.listSlots.choiceMode = android.widget.ListView.CHOICE_MODE_SINGLE
            }
            override fun onFailure(call: Call<com.management.apartment.network.AmenityAvailability>, t: Throwable) {
                Toast.makeText(this@AmenityAvailabilityActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun bookSelected() {
        val pos = binding.listSlots.checkedItemPosition
        if (pos == android.widget.AdapterView.INVALID_POSITION) {
            Toast.makeText(this, "Chọn 1 khung giờ", Toast.LENGTH_SHORT).show(); return
        }
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        val selected = (binding.listSlots.adapter.getItem(pos) as String)
        val parts = selected.split(" ")
        val start = parts[0]
        val end = parts[2]
        val body = com.management.apartment.network.BookingRequest(amenityId, start, end, null)
        api.bookAmenity("Bearer $token", body).enqueue(object: Callback<com.management.apartment.network.BookingResponse> {
            override fun onResponse(call: Call<com.management.apartment.network.BookingResponse>, response: Response<com.management.apartment.network.BookingResponse>) {
                Toast.makeText(this@AmenityAvailabilityActivity, "Đặt chỗ thành công", Toast.LENGTH_SHORT).show()
            }
            override fun onFailure(call: Call<com.management.apartment.network.BookingResponse>, t: Throwable) {
                Toast.makeText(this@AmenityAvailabilityActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }
}


