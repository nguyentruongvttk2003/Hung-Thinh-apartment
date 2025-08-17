package com.management.apartment.ui.fragments

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import com.management.apartment.data.SessionManager
import androidx.navigation.fragment.findNavController
import com.management.apartment.databinding.FragmentAccountBinding
import com.management.apartment.ui.auth.LoginActivity
import com.management.apartment.network.ApiService
import com.management.apartment.network.ApiEndpoints

class AccountFragment : Fragment() {
    private var _binding: FragmentAccountBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View? {
        _binding = FragmentAccountBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        // logout handled via rowLogout below

        // Load current profile basic fields if available
        val token = SessionManager(requireContext()).getToken()
        if (token != null) {
            ApiService.retrofit.create(ApiEndpoints::class.java).me("Bearer $token").enqueue(object: retrofit2.Callback<com.management.apartment.network.MeResponse> {
                override fun onResponse(
                    call: retrofit2.Call<com.management.apartment.network.MeResponse>,
                    response: retrofit2.Response<com.management.apartment.network.MeResponse>
                ) {
                    val user = response.body()?.data ?: return
                    binding.textDisplayName.text = user.name ?: user.email ?: "Người dùng"
                }
                override fun onFailure(call: retrofit2.Call<com.management.apartment.network.MeResponse>, t: Throwable) {}
            })
        }

        view.findViewById<android.view.View?>(com.management.apartment.R.id.rowEditProfile)?.setOnClickListener {
            val ctx = requireContext()
            val container = android.widget.LinearLayout(ctx).apply {
                orientation = android.widget.LinearLayout.VERTICAL
                setPadding(32, 16, 32, 0)
            }
            val nameInput = android.widget.EditText(ctx).apply { hint = "Họ tên" }
            val phoneInput = android.widget.EditText(ctx).apply { hint = "Số điện thoại"; inputType = android.text.InputType.TYPE_CLASS_PHONE }
            container.addView(nameInput); container.addView(phoneInput)
            androidx.appcompat.app.AlertDialog.Builder(ctx)
                .setTitle("Chỉnh sửa hồ sơ")
                .setView(container)
                .setNegativeButton("Hủy", null)
                .setPositiveButton("Lưu") { _, _ ->
                    val tkn = SessionManager(requireContext()).getToken() ?: return@setPositiveButton
                    val body = mutableMapOf<String, Any?>()
                    nameInput.text?.toString()?.trim()?.takeIf { it.isNotEmpty() }?.let { body["name"] = it }
                    phoneInput.text?.toString()?.trim()?.takeIf { it.isNotEmpty() }?.let { body["phone"] = it }
                    ApiService.retrofit.create(ApiEndpoints::class.java).updateProfile("Bearer $tkn", body).enqueue(object: retrofit2.Callback<com.management.apartment.network.GenericResponse<Any>> {
                        override fun onResponse(call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>, response: retrofit2.Response<com.management.apartment.network.GenericResponse<Any>>) {
                            android.widget.Toast.makeText(requireContext(), if (response.isSuccessful) "Đã lưu thông tin" else "Lưu thất bại", android.widget.Toast.LENGTH_SHORT).show()
                            if (response.isSuccessful) loadMe()
                        }
                        override fun onFailure(call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>, t: Throwable) {
                            android.widget.Toast.makeText(requireContext(), t.message, android.widget.Toast.LENGTH_SHORT).show()
                        }
                    })
                }
                .show()
        }

        view.findViewById<android.view.View?>(com.management.apartment.R.id.rowPrivacy)?.setOnClickListener {
            val ctx = requireContext()
            val container = android.widget.LinearLayout(ctx).apply {
                orientation = android.widget.LinearLayout.VERTICAL
                setPadding(32, 16, 32, 0)
            }
            val current = android.widget.EditText(ctx).apply { hint = "Mật khẩu hiện tại"; inputType = android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD or android.text.InputType.TYPE_CLASS_TEXT }
            val newPass = android.widget.EditText(ctx).apply { hint = "Mật khẩu mới"; inputType = android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD or android.text.InputType.TYPE_CLASS_TEXT }
            val confirm = android.widget.EditText(ctx).apply { hint = "Nhập lại mật khẩu mới"; inputType = android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD or android.text.InputType.TYPE_CLASS_TEXT }
            container.addView(current); container.addView(newPass); container.addView(confirm)
            androidx.appcompat.app.AlertDialog.Builder(ctx)
                .setTitle("Đổi mật khẩu")
                .setView(container)
                .setNegativeButton("Hủy", null)
                .setPositiveButton("Đổi") { _, _ ->
                    val tkn = SessionManager(requireContext()).getToken() ?: return@setPositiveButton
                    val body = ApiEndpoints.ChangePasswordRequest(
                        current.text?.toString() ?: "",
                        newPass.text?.toString() ?: "",
                        confirm.text?.toString() ?: ""
                    )
                    ApiService.retrofit.create(ApiEndpoints::class.java).changePassword("Bearer $tkn", body).enqueue(object: retrofit2.Callback<com.management.apartment.network.GenericResponse<Any>> {
                        override fun onResponse(call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>, response: retrofit2.Response<com.management.apartment.network.GenericResponse<Any>>) {
                            android.widget.Toast.makeText(requireContext(), if (response.isSuccessful) "Đổi mật khẩu thành công" else "Đổi mật khẩu thất bại", android.widget.Toast.LENGTH_SHORT).show()
                        }
                        override fun onFailure(call: retrofit2.Call<com.management.apartment.network.GenericResponse<Any>>, t: Throwable) {
                            android.widget.Toast.makeText(requireContext(), t.message, android.widget.Toast.LENGTH_SHORT).show()
                        }
                    })
                }
                .show()
        }

        view.findViewById<android.view.View?>(com.management.apartment.R.id.rowBookmarks)?.setOnClickListener {
            startActivity(android.content.Intent(requireContext(), com.management.apartment.ui.payments.PaymentHistoryActivity::class.java))
        }

        view.findViewById<android.view.View?>(com.management.apartment.R.id.rowLogout)?.setOnClickListener {
            SessionManager(requireContext()).clear()
            val intent = Intent(requireContext(), LoginActivity::class.java)
            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            startActivity(intent)
        }
    }

    private fun loadMe() {
        val token = SessionManager(requireContext()).getToken() ?: return
        ApiService.retrofit.create(ApiEndpoints::class.java).me("Bearer $token").enqueue(object: retrofit2.Callback<com.management.apartment.network.MeResponse> {
            override fun onResponse(
                call: retrofit2.Call<com.management.apartment.network.MeResponse>,
                response: retrofit2.Response<com.management.apartment.network.MeResponse>
            ) {
                val user = response.body()?.data ?: return
                binding.textDisplayName.text = user.name ?: user.email ?: "Người dùng"
            }
            override fun onFailure(call: retrofit2.Call<com.management.apartment.network.MeResponse>, t: Throwable) {}
        })
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
