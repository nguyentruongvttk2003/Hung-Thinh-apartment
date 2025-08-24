package com.management.apartment.ui.amenities

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.LinearLayoutManager
import android.widget.Toast
import androidx.fragment.app.Fragment
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.FragmentAmenitiesBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class AmenitiesFragment: Fragment() {
    private var _binding: FragmentAmenitiesBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentAmenitiesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        // Set up toolbar with back button
        binding.toolbar.setNavigationOnClickListener { requireActivity().onBackPressedDispatcher.onBackPressed() }

        binding.recyclerView.layoutManager = LinearLayoutManager(requireContext())
        val adapter = AmenityAdapter(emptyList()) { amenity ->
            val intent = android.content.Intent(requireContext(), AmenityAvailabilityActivity::class.java)
            intent.putExtra("amenity_id", amenity.id)
            startActivity(intent)
        }
        binding.recyclerView.adapter = adapter
        loadAmenities(adapter)
    }

    private fun loadAmenities(adapter: AmenityAdapter) {
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        binding.progress.visibility = View.VISIBLE
        api.amenities("Bearer $token").enqueue(object: Callback<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.AmenityDto>>> {
            override fun onResponse(call: Call<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.AmenityDto>>>, response: Response<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.AmenityDto>>>) {
                binding.progress.visibility = View.GONE
                val list = response.body()?.data ?: emptyList()
                adapter.update(list)
                binding.textEmpty.visibility = if (list.isEmpty()) View.VISIBLE else View.GONE
            }
            override fun onFailure(call: Call<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.AmenityDto>>>, t: Throwable) {
                binding.progress.visibility = View.GONE
                binding.textEmpty.visibility = View.VISIBLE
                Toast.makeText(requireContext(), t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}


