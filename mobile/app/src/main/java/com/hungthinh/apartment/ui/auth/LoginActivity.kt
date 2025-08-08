package com.hungthinh.apartment.ui.auth

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.hungthinh.apartment.R
import com.hungthinh.apartment.ui.main.MainActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class LoginActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        // For now, automatically navigate to MainActivity
        // Later you can add proper login logic here
        Toast.makeText(this, "Welcome to HungThinh Apartment", Toast.LENGTH_SHORT).show()
        
        // Navigate to MainActivity after a short delay
        android.os.Handler(android.os.Looper.getMainLooper()).postDelayed({
            startActivity(Intent(this, MainActivity::class.java))
            finish()
        }, 1000)
    }
}
