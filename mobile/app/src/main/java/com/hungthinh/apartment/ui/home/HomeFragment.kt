package com.hungthinh.apartment.ui.home

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.hungthinh.apartment.databinding.FragmentHomeBinding
import com.hungthinh.apartment.ui.adapters.RecentActivityAdapter
import com.hungthinh.apartment.ui.viewmodel.HomeViewModel
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import java.text.NumberFormat
import java.util.*

@AndroidEntryPoint
class HomeFragment : Fragment() {

    private var _binding: FragmentHomeBinding? = null
    private val binding get() = _binding!!
    
    private val viewModel: HomeViewModel by viewModels()
    private lateinit var recentActivityAdapter: RecentActivityAdapter

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentHomeBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        
        setupUI()
        setupRecyclerView()
        observeViewModel()
    }
    
    private fun setupUI() {
        binding.apply {
            // Setup basic UI components if they exist
            try {
                // Setup click listeners for cards if they exist
            } catch (e: Exception) {
                // Handle missing views gracefully
            }
        }
    }
    
    private fun setupRecyclerView() {
        recentActivityAdapter = RecentActivityAdapter { activity ->
            // Handle activity click
        }
        
        try {
            binding.rvRecentActivities?.apply {
                layoutManager = LinearLayoutManager(requireContext())
                adapter = recentActivityAdapter
            }
        } catch (e: Exception) {
            // RecyclerView not found in layout
        }
    }
    
    private fun observeViewModel() {
        viewLifecycleOwner.lifecycleScope.launch {
            // Observe UI state
            viewModel.uiState.collect { state ->
                try {
                    binding.swipeRefreshLayout?.isRefreshing = state.isLoading
                } catch (e: Exception) {
                    // SwipeRefreshLayout not found
                }
                
                state.error?.let { error ->
                    Toast.makeText(requireContext(), error, Toast.LENGTH_LONG).show()
                    viewModel.clearError()
                }
            }
        }
        
        viewLifecycleOwner.lifecycleScope.launch {
            // Observe dashboard stats
            viewModel.dashboardStats.collect { stats ->
                stats?.let { updateDashboardStats(it) }
            }
        }
        
        viewLifecycleOwner.lifecycleScope.launch {
            // Observe recent activities
            viewModel.recentActivities.collect { activities ->
                recentActivityAdapter.submitList(activities)
            }
        }
        
        viewLifecycleOwner.lifecycleScope.launch {
            // Observe current user
            viewModel.currentUser.collect { user ->
                user?.let { updateUserInfo(it) }
            }
        }
    }
    
    private fun updateDashboardStats(stats: com.hungthinh.apartment.data.model.DashboardStats) {
        binding.apply {
            // Update apartment stats
            tvTotalApartments.text = stats.totalApartments.toString()
            tvOccupiedApartments.text = "${stats.occupiedApartments}/${stats.totalApartments}"
            
            // Update invoice stats
            tvPendingInvoices.text = stats.pendingInvoices.toString()
            tvOverdueInvoices.text = stats.overdueInvoices.toString()
            
            // Update other stats
            tvTotalResidents.text = stats.totalResidents.toString()
            tvActiveMaintenance.text = stats.activeMaintenance.toString()
            tvUnreadNotifications.text = stats.unreadNotifications.toString()
            tvPendingFeedback.text = stats.pendingFeedback.toString()
            
            // Update revenue
            val formatter = NumberFormat.getCurrencyInstance(Locale("vi", "VN"))
            try {
                val revenue = stats.monthlyRevenue.toDoubleOrNull() ?: 0.0
                tvMonthlyRevenue.text = formatter.format(revenue)
            } catch (e: Exception) {
                tvMonthlyRevenue.text = stats.monthlyRevenue
            }
            
            // Update collection rate
            tvCollectionRate.text = "${String.format("%.1f", stats.collectionRate)}%"
        }
    }
    
    private fun updateUserInfo(user: com.hungthinh.apartment.data.model.User) {
        binding.apply {
            tvWelcomeMessage.text = "Xin chào, ${user.name}"
            tvUserRole.text = user.getRoleDisplayName()
            
            user.apartmentNumber?.let {
                tvApartmentNumber.text = "Căn hộ: $it"
                tvApartmentNumber.visibility = View.VISIBLE
            } ?: run {
                tvApartmentNumber.visibility = View.GONE
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
