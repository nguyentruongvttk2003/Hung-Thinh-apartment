package com.management.apartment.ui.events

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.fragment.app.Fragment
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.FragmentEventsBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class EventsFragment: Fragment() {
    private var _binding: FragmentEventsBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentEventsBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        // Set up toolbar with back button
        binding.toolbar.setNavigationOnClickListener { requireActivity().onBackPressedDispatcher.onBackPressed() }

        binding.recyclerView.layoutManager = LinearLayoutManager(requireContext())
        val adapter = EventAdapter(emptyList())
        binding.recyclerView.adapter = adapter
        binding.progress.visibility = View.VISIBLE
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.events("Bearer $token").enqueue(object: Callback<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.EventDto>>> {
            override fun onResponse(call: Call<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.EventDto>>>, response: Response<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.EventDto>>>) {
                binding.progress.visibility = View.GONE
                val data = response.body()?.data ?: emptyList()
                adapter.update(data)
                // Click to detail
                binding.recyclerView.addOnItemTouchListener(object: androidx.recyclerview.widget.RecyclerView.SimpleOnItemTouchListener() {})
                binding.textEmpty.visibility = if (data.isEmpty()) View.VISIBLE else View.GONE
            }
            override fun onFailure(call: Call<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.EventDto>>>, t: Throwable) {
                binding.progress.visibility = View.GONE
                binding.textEmpty.visibility = View.VISIBLE
            }
        })
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}


