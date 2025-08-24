package com.management.apartment.ui.feedback

import android.os.Bundle
import android.view.View
import android.widget.ArrayAdapter
import android.widget.Toast
import android.util.Log
import androidx.appcompat.app.AppCompatActivity
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityFeedbackBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class FeedbackActivity: AppCompatActivity() {
    private lateinit var binding: ActivityFeedbackBinding
    private var apartments: List<com.management.apartment.network.ApartmentSimple> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityFeedbackBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Set up toolbar with back button
        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        binding.toolbar.setNavigationOnClickListener { finish() }

        loadApartments()
        binding.btnSubmit.setOnClickListener { submitFeedback() }
    }

    private fun loadApartments() {
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.myApartments("Bearer $token").enqueue(object: Callback<List<com.management.apartment.network.ApartmentSimple>> {
            override fun onResponse(call: Call<List<com.management.apartment.network.ApartmentSimple>>, response: Response<List<com.management.apartment.network.ApartmentSimple>>) {
                apartments = response.body() ?: emptyList()
                val items = if (apartments.isEmpty()) listOf("(Không có căn hộ)") else apartments.map { it.apartment_number ?: it.id.toString() }
                binding.spinnerApartment.adapter = ArrayAdapter(this@FeedbackActivity, android.R.layout.simple_spinner_dropdown_item, items)
            }
            override fun onFailure(call: Call<List<com.management.apartment.network.ApartmentSimple>>, t: Throwable) {
                Toast.makeText(this@FeedbackActivity, "Lỗi tải danh sách căn hộ", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun submitFeedback() {
        val subject = binding.inputSubject.text.toString().trim()
        val description = binding.inputDescription.text.toString().trim()
        if (subject.isEmpty() || description.isEmpty()) {
            Toast.makeText(this, "Nhập tiêu đề và nội dung", Toast.LENGTH_SHORT).show(); return
        }
        val token = SessionManager(this).getToken() ?: run {
            Toast.makeText(this, "Chưa đăng nhập", Toast.LENGTH_SHORT).show(); return
        }
        val pos = binding.spinnerApartment.selectedItemPosition
        val apartmentId = apartments.getOrNull(pos)?.id ?: apartments.firstOrNull()?.id ?: 1L
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        val body = mapOf(
            "subject" to subject,
            "description" to description,
            "apartment_id" to apartmentId
        )
        Log.d("FeedbackActivity", "Submitting feedback subject=$subject apt=$apartmentId")
        setLoading(true)
        api.createFeedback("Bearer $token", body).enqueue(object: Callback<com.management.apartment.network.GenericResponse<com.management.apartment.network.FeedbackDto>> {
            override fun onResponse(call: Call<com.management.apartment.network.GenericResponse<com.management.apartment.network.FeedbackDto>>, response: Response<com.management.apartment.network.GenericResponse<com.management.apartment.network.FeedbackDto>>) {
                try {
                    setLoading(false)
                    Log.d("FeedbackActivity", "Response code=${response.code()} body=${response.body()}")
                    if (!response.isSuccessful) {
                        Toast.makeText(this@FeedbackActivity, "Lỗi gửi (${response.code()})", Toast.LENGTH_SHORT).show(); return
                    }
                    val success = response.body()?.success == true
                    if (success) {
                        Toast.makeText(this@FeedbackActivity, "Đã gửi phản ánh", Toast.LENGTH_SHORT).show()
                        finish()
                    } else {
                        Toast.makeText(this@FeedbackActivity, "Gửi thất bại", Toast.LENGTH_SHORT).show()
                    }
                } catch (e: Exception) {
                    Log.e("FeedbackActivity", "Crash during onResponse", e)
                    Toast.makeText(this@FeedbackActivity, "Lỗi xử lý phản hồi", Toast.LENGTH_SHORT).show()
                }
            }
            override fun onFailure(call: Call<com.management.apartment.network.GenericResponse<com.management.apartment.network.FeedbackDto>>, t: Throwable) {
                setLoading(false)
                Log.e("FeedbackActivity", "Submit failed", t)
                Toast.makeText(this@FeedbackActivity, t.message ?: "Lỗi kết nối", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun setLoading(loading: Boolean) {
        binding.btnSubmit.isEnabled = !loading
        binding.progress.visibility = if (loading) View.VISIBLE else View.GONE
    }
}


