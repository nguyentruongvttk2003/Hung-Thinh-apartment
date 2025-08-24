package com.management.apartment.ui.auth

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.management.apartment.MainActivity
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityLoginBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import com.management.apartment.network.LoginRequest
import com.management.apartment.network.LoginResponse
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class LoginActivity : AppCompatActivity() {
    private lateinit var binding: ActivityLoginBinding
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        android.util.Log.d("LoginActivity", "onCreate called")
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)

        // If token exists, go straight to main
        sessionManager.getToken()?.let {
            goToMain()
            return
        }

        binding.buttonLogin.setOnClickListener {
            val email = binding.inputEmail.text.toString().trim()
            val password = binding.inputPassword.text.toString()

            if (email.isEmpty() || password.isEmpty()) {
                Toast.makeText(this, "Nhập email và mật khẩu", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            setLoading(true)
            val api = ApiService.retrofit.create(ApiEndpoints::class.java)
            api.login(LoginRequest(email, password)).enqueue(object: Callback<LoginResponse> {
                override fun onResponse(
                    call: Call<LoginResponse>,
                    response: Response<LoginResponse>
                ) {
                    setLoading(false)
                    val body = response.body()
                    if (response.isSuccessful && body?.success == true && body.data?.token != null) {
                        sessionManager.saveToken(body.data.token)
                        Toast.makeText(this@LoginActivity, "Đăng nhập thành công", Toast.LENGTH_SHORT).show()
                        goToMain()
                    } else {
                        Toast.makeText(this@LoginActivity, "Đăng nhập thất bại", Toast.LENGTH_SHORT).show()
                    }
                }

                override fun onFailure(call: Call<LoginResponse>, t: Throwable) {
                    setLoading(false)
                    Toast.makeText(this@LoginActivity, "Lỗi mạng: ${t.message}", Toast.LENGTH_SHORT).show()
                }
            })
        }
    }

    private fun goToMain() {
        startActivity(Intent(this, MainActivity::class.java))
        finish()
    }

    private fun setLoading(loading: Boolean) {
        binding.buttonLogin.isEnabled = !loading
        binding.progressBar.visibility = if (loading) android.view.View.VISIBLE else android.view.View.GONE
    }
}


