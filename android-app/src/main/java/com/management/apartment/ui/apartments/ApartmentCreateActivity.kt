package com.management.apartment.ui.apartments

import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.R
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityApartmentCreateBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService

class ApartmentCreateActivity: AppCompatActivity() {
    private lateinit var binding: ActivityApartmentCreateBinding
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityApartmentCreateBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(R.id.toolbar))
        findViewById<MaterialToolbar?>(R.id.toolbar)?.setNavigationOnClickListener { finish() }

        binding.btnCreate.setOnClickListener { create() }
    }

    private fun create() {
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        val body = mapOf(
            "apartment_number" to binding.inApartmentNumber.text?.toString()?.trim(),
            "block" to binding.inBlock.text?.toString()?.trim(),
            "floor" to binding.inFloor.text?.toString()?.toIntOrNull(),
            "area" to binding.inArea.text?.toString()?.trim(),
            "type" to binding.inType.text?.toString()?.trim(),
            "status" to binding.inStatus.text?.toString()?.trim()
        )
        api.createApartment("Bearer $token", body).enqueue(object: retrofit2.Callback<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>> {
            override fun onResponse(
                call: retrofit2.Call<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>>,
                response: retrofit2.Response<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>>
            ) {
                Toast.makeText(this@ApartmentCreateActivity, if (response.isSuccessful) "Đã tạo" else "Tạo thất bại", Toast.LENGTH_SHORT).show()
                if (response.isSuccessful) finish()
            }
            override fun onFailure(
                call: retrofit2.Call<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>>,
                t: Throwable
            ) {
                Toast.makeText(this@ApartmentCreateActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }
}


