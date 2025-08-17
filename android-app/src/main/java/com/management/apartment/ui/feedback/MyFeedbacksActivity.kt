package com.management.apartment.ui.feedback

import android.os.Bundle
import android.view.View
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.R
import com.management.apartment.data.SessionManager
import com.management.apartment.databinding.ActivityMyFeedbacksBinding
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class MyFeedbacksActivity: AppCompatActivity() {
    private lateinit var binding: ActivityMyFeedbacksBinding
    private lateinit var adapter: FeedbacksAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMyFeedbacksBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(R.id.toolbar))
        findViewById<MaterialToolbar?>(R.id.toolbar)?.setNavigationOnClickListener { finish() }

        adapter = FeedbacksAdapter(emptyList())
        binding.recyclerFeedbacks.layoutManager = LinearLayoutManager(this)
        binding.recyclerFeedbacks.adapter = adapter
        load()
    }

    private fun load() {
        val token = SessionManager(this).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        binding.progress.visibility = View.VISIBLE
        api.feedbacks("Bearer $token").enqueue(object: Callback<com.management.apartment.network.WrappedList<com.management.apartment.network.FeedbackDto>> {
            override fun onResponse(
                call: Call<com.management.apartment.network.WrappedList<com.management.apartment.network.FeedbackDto>>,
                response: Response<com.management.apartment.network.WrappedList<com.management.apartment.network.FeedbackDto>>
            ) {
                binding.progress.visibility = View.GONE
                adapter.update(response.body()?.data ?: emptyList())
            }
            override fun onFailure(call: Call<com.management.apartment.network.WrappedList<com.management.apartment.network.FeedbackDto>>, t: Throwable) {
                binding.progress.visibility = View.GONE
                Toast.makeText(this@MyFeedbacksActivity, t.message, Toast.LENGTH_SHORT).show()
            }
        })
    }
}

private class FeedbacksAdapter(private var items: List<com.management.apartment.network.FeedbackDto>) : androidx.recyclerview.widget.RecyclerView.Adapter<FeedbackVH>() {
    override fun onCreateViewHolder(parent: android.view.ViewGroup, viewType: Int): FeedbackVH {
        val v = android.view.LayoutInflater.from(parent.context).inflate(android.R.layout.simple_list_item_2, parent, false)
        return FeedbackVH(v)
    }
    override fun getItemCount(): Int = items.size
    override fun onBindViewHolder(holder: FeedbackVH, position: Int) = holder.bind(items[position])
    fun update(newItems: List<com.management.apartment.network.FeedbackDto>) { items = newItems; notifyDataSetChanged() }
}

private class FeedbackVH(itemView: android.view.View) : androidx.recyclerview.widget.RecyclerView.ViewHolder(itemView) {
    private val t1: TextView = itemView.findViewById(android.R.id.text1)
    private val t2: TextView = itemView.findViewById(android.R.id.text2)
    fun bind(f: com.management.apartment.network.FeedbackDto) {
        t1.text = f.subject
        t2.text = "${f.status} • ${f.description.take(50)}"
    }
}


