package com.management.apartment.ui.events

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.R
import com.management.apartment.databinding.ActivityEventDetailBinding

class EventDetailActivity: AppCompatActivity() {
    private lateinit var binding: ActivityEventDetailBinding
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityEventDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(R.id.toolbar))
        findViewById<MaterialToolbar?>(R.id.toolbar)?.setNavigationOnClickListener { finish() }

        val title = intent.getStringExtra("title") ?: "Sự kiện"
        val time = intent.getStringExtra("time") ?: "--"
        val desc = intent.getStringExtra("desc") ?: ""
        binding.textTitle.text = title
        binding.textTime.text = time
        binding.textDescription.text = desc
        binding.btnRegister.setOnClickListener {
            android.widget.Toast.makeText(this, "Đã đăng ký (demo)", android.widget.Toast.LENGTH_SHORT).show()
        }
    }
}


