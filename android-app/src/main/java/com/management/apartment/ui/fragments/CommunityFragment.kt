package com.management.apartment.ui.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.navigation.fragment.findNavController
import com.management.apartment.databinding.FragmentCommunityBinding

class CommunityFragment: Fragment() {
    private var _binding: FragmentCommunityBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentCommunityBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        binding.btnFeedback.setOnClickListener {
            startActivity(android.content.Intent(requireContext(), com.management.apartment.ui.feedback.FeedbackActivity::class.java))
        }
        binding.btnEvents.setOnClickListener { safeNavigate(com.management.apartment.R.id.navigation_events) }
        binding.btnAmenities.setOnClickListener { safeNavigate(com.management.apartment.R.id.navigation_amenities) }
        binding.btnVotes.setOnClickListener { safeNavigate(com.management.apartment.R.id.navigation_votes) }
    }

    private fun safeNavigate(destId: Int) {
        val navController = findNavController()
        val currentId = navController.currentDestination?.id
        // Avoid duplicate navigate causing IllegalArgumentException
        if (currentId == destId) return
        runCatching { navController.navigate(destId) }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}


