package com.management.apartment.ui.notifications

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.LinearLayoutManager
import android.widget.Toast
import androidx.fragment.app.Fragment
import com.management.apartment.data.SessionManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.databinding.FragmentNotificationsBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class NotificationsFragment: Fragment() {
    private var _binding: FragmentNotificationsBinding? = null
    private val binding get() = _binding!!
    private lateinit var adapter: NotificationAdapter

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentNotificationsBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        view.findViewById<MaterialToolbar?>(com.management.apartment.R.id.toolbar)?.setNavigationOnClickListener {
            requireActivity().onBackPressedDispatcher.onBackPressed()
        }
        binding.recyclerView.layoutManager = LinearLayoutManager(requireContext())
        adapter = NotificationAdapter(emptyList())
        binding.recyclerView.adapter = adapter
        loadNotifications()
        binding.btnMarkAllRead.setOnClickListener { markAllRead() }
    }

    private fun loadNotifications() {
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        binding.progress.visibility = View.VISIBLE
        api.notifications("Bearer $token").enqueue(object: Callback<com.management.apartment.network.WrappedList<com.management.apartment.network.NotificationDto>> {
            override fun onResponse(
                call: Call<com.management.apartment.network.WrappedList<com.management.apartment.network.NotificationDto>>,
                response: Response<com.management.apartment.network.WrappedList<com.management.apartment.network.NotificationDto>>
            ) {
                binding.progress.visibility = View.GONE
                val list = response.body()?.data ?: emptyList()
                adapter.update(list)
                binding.textEmpty.visibility = if (list.isEmpty()) View.VISIBLE else View.GONE
            }
            override fun onFailure(call: Call<com.management.apartment.network.WrappedList<com.management.apartment.network.NotificationDto>>, t: Throwable) {
                binding.progress.visibility = View.GONE
                binding.textEmpty.visibility = View.VISIBLE
                Toast.makeText(requireContext(), t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun markAllRead() {
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.markAllNotificationsRead("Bearer $token").enqueue(object: Callback<com.management.apartment.network.GenericResponse<Any>> {
            override fun onResponse(call: Call<com.management.apartment.network.GenericResponse<Any>>, response: Response<com.management.apartment.network.GenericResponse<Any>>) {
                Toast.makeText(requireContext(), "Đã đánh dấu đã đọc", Toast.LENGTH_SHORT).show()
                loadNotifications()
            }
            override fun onFailure(call: Call<com.management.apartment.network.GenericResponse<Any>>, t: Throwable) {
                Toast.makeText(requireContext(), t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}


