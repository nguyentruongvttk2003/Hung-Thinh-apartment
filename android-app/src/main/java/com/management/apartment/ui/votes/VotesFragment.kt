package com.management.apartment.ui.votes

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.fragment.app.Fragment
import com.management.apartment.data.SessionManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.databinding.FragmentVotesBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class VotesFragment: Fragment() {
    private var _binding: FragmentVotesBinding? = null
    private val binding get() = _binding!!

    private var activeVotes: List<com.management.apartment.network.VoteDto> = emptyList()

    private fun votedPrefs(): android.content.SharedPreferences = requireContext().getSharedPreferences("votes_prefs", android.content.Context.MODE_PRIVATE)
    private fun hasVoted(voteId: Long): Boolean = votedPrefs().getStringSet("voted_ids", emptySet())?.contains(voteId.toString()) == true
    private fun markVoted(voteId: Long) {
        val set = java.util.HashSet(votedPrefs().getStringSet("voted_ids", emptySet()))
        set.add(voteId.toString())
        votedPrefs().edit().putStringSet("voted_ids", set).apply()
    }

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentVotesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        view.findViewById<MaterialToolbar?>(com.management.apartment.R.id.toolbar)?.setNavigationOnClickListener {
            requireActivity().onBackPressedDispatcher.onBackPressed()
        }
        loadVotes()
        binding.btnSubmit.setOnClickListener { submitVote() }
    }

    private fun loadVotes() {
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.activeVotes("Bearer $token").enqueue(object: Callback<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.VoteDto>>> {
            override fun onResponse(call: Call<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.VoteDto>>>, response: Response<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.VoteDto>>>) {
                if (!response.isSuccessful) {
                    Toast.makeText(requireContext(), "Lỗi tải danh sách (${response.code()})", Toast.LENGTH_SHORT).show()
                    return
                }
                activeVotes = response.body()?.data ?: emptyList()
                if (activeVotes.isEmpty()) {
                    binding.spinnerVotes.visibility = View.GONE
                    binding.spinnerOptions.visibility = View.GONE
                    binding.btnSubmit.isEnabled = false
                    val emptyViewId = resources.getIdentifier("textEmpty", "id", requireContext().packageName)
                    val emptyView = view?.findViewById<View>(emptyViewId)
                    emptyView?.visibility = View.VISIBLE
                } else {
                    binding.spinnerVotes.visibility = View.VISIBLE
                    binding.spinnerOptions.visibility = View.VISIBLE
                    binding.btnSubmit.isEnabled = true
                    val titles = activeVotes.map { it.title }
                    binding.spinnerVotes.adapter = ArrayAdapter(requireContext(), android.R.layout.simple_spinner_dropdown_item, titles)
                    updateOptions(0)
                    binding.spinnerVotes.setOnItemSelectedListener(object: android.widget.AdapterView.OnItemSelectedListener {
                        override fun onItemSelected(parent: android.widget.AdapterView<*>, view: View?, position: Int, id: Long) {
                            updateOptions(position)
                            val voteId = activeVotes.getOrNull(position)?.id ?: return
                            val voted = hasVoted(voteId)
                            binding.btnSubmit.isEnabled = !voted
                            view?.findViewById<android.widget.EditText>(com.management.apartment.R.id.inputComment)?.isEnabled = !voted
                            // Show results button for convenience
                            view?.post {
                                val ctx = requireContext()
                                val options = activeVotes.getOrNull(position)?.options?.map { it.option_text } ?: emptyList()
                                val counts = java.util.ArrayList(options.map { 0 })
                                val intent = android.content.Intent(ctx, VoteResultsActivity::class.java)
                                intent.putStringArrayListExtra("options", java.util.ArrayList(options))
                                intent.putIntegerArrayListExtra("counts", counts)
                                // You might add a toolbar button later to open this
                            }
                        }
                        override fun onNothingSelected(parent: android.widget.AdapterView<*>) {}
                    })
                }
            }
            override fun onFailure(call: Call<com.management.apartment.network.GenericResponse<List<com.management.apartment.network.VoteDto>>>, t: Throwable) {
                Toast.makeText(requireContext(), t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun updateOptions(index: Int) {
        val opts = activeVotes.getOrNull(index)?.options ?: emptyList()
        binding.spinnerOptions.adapter = ArrayAdapter(requireContext(), android.R.layout.simple_spinner_dropdown_item, opts.map { it.option_text })
    }

    private fun submitVote() {
        val vote = activeVotes.getOrNull(binding.spinnerVotes.selectedItemPosition) ?: return
        val option = vote.options?.getOrNull(binding.spinnerOptions.selectedItemPosition) ?: return
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        val comment = view?.findViewById<android.widget.EditText>(com.management.apartment.R.id.inputComment)?.text?.toString()?.trim()
        val body = if (comment.isNullOrEmpty()) mapOf("vote_option_id" to option.id) else mapOf("vote_option_id" to option.id, "comment" to comment)
        api.submitVote("Bearer $token", vote.id, body).enqueue(object: Callback<com.management.apartment.network.GenericResponse<Any>> {
            override fun onResponse(call: Call<com.management.apartment.network.GenericResponse<Any>>, response: Response<com.management.apartment.network.GenericResponse<Any>>) {
                if (response.isSuccessful) {
                    Toast.makeText(requireContext(), "Đã gửi phiếu", Toast.LENGTH_SHORT).show()
                    // Disable further submit to avoid duplicate
                    binding.btnSubmit.isEnabled = false
                    binding.spinnerVotes.isEnabled = false
                    binding.spinnerOptions.isEnabled = false
                    markVoted(vote.id)
                } else {
                    val errMsg = try { response.errorBody()?.string() } catch (_: Exception) { null }
                    val msg = if (!errMsg.isNullOrEmpty() && errMsg.contains("Bạn đã bỏ phiếu")) "Bạn đã bỏ phiếu" else "Lỗi gửi phiếu (${response.code()})"
                    Toast.makeText(requireContext(), msg, Toast.LENGTH_SHORT).show()
                    if (msg.contains("Bạn đã bỏ phiếu")) {
                        binding.btnSubmit.isEnabled = false
                        markVoted(vote.id)
                    }
                }
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


