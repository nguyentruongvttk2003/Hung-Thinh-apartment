package com.management.apartment.ui.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import android.widget.ArrayAdapter
import androidx.navigation.fragment.findNavController
import java.text.NumberFormat
import java.util.Locale
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.FragmentDashboardBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import com.management.apartment.network.WrappedList
import com.management.apartment.network.NotificationDto

class DashboardFragment: Fragment() {
    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentDashboardBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        loadDashboard()
        binding.textSeeAllNews.setOnClickListener {
            runCatching { findNavController().navigate(com.management.apartment.R.id.navigation_notifications) }
        }
        view.findViewById<android.widget.TextView?>(com.management.apartment.R.id.textSeeAllEvents)?.setOnClickListener {
            runCatching { findNavController().navigate(com.management.apartment.R.id.navigation_events) }
        }

        // Quick actions
        binding.actionApartments.setOnClickListener {
            runCatching { findNavController().navigate(com.management.apartment.R.id.navigation_apartments) }
        }
        binding.actionInvoices.setOnClickListener {
            runCatching { findNavController().navigate(com.management.apartment.R.id.navigation_invoices) }
        }
        binding.actionFeedback.setOnClickListener {
            startActivity(android.content.Intent(requireContext(), com.management.apartment.ui.feedback.FeedbackActivity::class.java))
        }
    }

    private fun loadDashboard() {
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        // Reuse notifications and invoices as simple demo for dashboard
        api.notifications("Bearer $token").enqueue(object: retrofit2.Callback<WrappedList<NotificationDto>> {
            override fun onResponse(call: retrofit2.Call<WrappedList<NotificationDto>>, response: retrofit2.Response<WrappedList<NotificationDto>>) {
                val news = response.body()?.data?.take(3)?.map { it.title } ?: emptyList()
                binding.textNews.text = if (news.isEmpty()) "Chưa có tin mới" else "Tin mới: ${news.joinToString(", ")}"
            }
            override fun onFailure(call: retrofit2.Call<WrappedList<NotificationDto>>, t: Throwable) {}
        })
        api.invoices("Bearer $token").enqueue(object: retrofit2.Callback<com.management.apartment.network.WrappedList<com.management.apartment.network.InvoiceDto>> {
            override fun onResponse(call: retrofit2.Call<com.management.apartment.network.WrappedList<com.management.apartment.network.InvoiceDto>>, response: retrofit2.Response<com.management.apartment.network.WrappedList<com.management.apartment.network.InvoiceDto>>) {
                val invoices = response.body()?.data ?: emptyList()
                val pending = invoices.count { it.status.equals("pending", true) || it.status.equals("partial", true) }
                val totalDue = invoices.filter { it.status != "paid" }.sumOf { it.total_amount }
                binding.textPendingInvoices.text = pending.toString()
                val currency = NumberFormat.getNumberInstance(Locale("vi", "VN")).format(totalDue)
                // Reuse textNews to show quick amount summary below, or extend layout if needed
                // Here we keep textNews for news and show amount in it if empty
                if (binding.textNews.text.isNullOrBlank()) {
                    binding.textNews.text = "Cần thanh toán: ${currency} VNĐ"
                }
            }
            override fun onFailure(call: retrofit2.Call<com.management.apartment.network.WrappedList<com.management.apartment.network.InvoiceDto>>, t: Throwable) {}
        })
    }
    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}


