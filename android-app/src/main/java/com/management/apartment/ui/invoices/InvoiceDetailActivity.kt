package com.management.apartment.ui.invoices

import android.graphics.Bitmap
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.appbar.MaterialToolbar
import com.google.zxing.BarcodeFormat
import com.google.zxing.MultiFormatWriter
import com.google.zxing.common.BitMatrix
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityInvoiceDetailBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import com.management.apartment.network.QrPayloadResponse
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class InvoiceDetailActivity: AppCompatActivity() {
    private lateinit var binding: ActivityInvoiceDetailBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityInvoiceDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(com.management.apartment.R.id.toolbar))
        findViewById<MaterialToolbar?>(com.management.apartment.R.id.toolbar)?.setNavigationOnClickListener { finish() }

    val invoiceId = intent.getLongExtra("invoice_id", -1)
    if (invoiceId <= 0) { finish(); return }
    binding.btnRefresh.setOnClickListener { loadQr(invoiceId) }
    loadQr(invoiceId)
    }

    private fun loadQr(id: Long) {
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.invoiceQr("Bearer $token", id).enqueue(object: Callback<QrPayloadResponse> {
            override fun onResponse(
                call: Call<QrPayloadResponse>,
                response: Response<QrPayloadResponse>
            ) {
                val qr = response.body()?.data?.qr_string
                if (!qr.isNullOrEmpty()) {
                    binding.imageQr.setImageBitmap(createQrBitmap(qr))
                } else {
                    Toast.makeText(this@InvoiceDetailActivity, "Không có dữ liệu QR", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(call: Call<QrPayloadResponse>, t: Throwable) {
                Toast.makeText(this@InvoiceDetailActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun createQrBitmap(content: String, size: Int = 600): Bitmap {
        val bitMatrix: BitMatrix = MultiFormatWriter().encode(content, BarcodeFormat.QR_CODE, size, size)
        val bmp = Bitmap.createBitmap(size, size, Bitmap.Config.RGB_565)
        for (x in 0 until size) {
            for (y in 0 until size) {
                bmp.setPixel(x, y, if (bitMatrix.get(x, y)) 0xFF000000.toInt() else 0xFFFFFFFF.toInt())
            }
        }
        return bmp
    }
}


