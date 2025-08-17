package com.management.apartment.ui.votes

import android.os.Bundle
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.R
import com.management.apartment.databinding.ActivityVoteResultsBinding

class VoteResultsActivity: AppCompatActivity() {
    private lateinit var binding: ActivityVoteResultsBinding
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityVoteResultsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(findViewById(R.id.toolbar))
        findViewById<MaterialToolbar?>(R.id.toolbar)?.setNavigationOnClickListener { finish() }

        val options = intent.getStringArrayListExtra("options") ?: arrayListOf()
        val counts = intent.getIntegerArrayListExtra("counts") ?: arrayListOf()
        binding.recyclerResults.layoutManager = LinearLayoutManager(this)
        binding.recyclerResults.adapter = object: androidx.recyclerview.widget.RecyclerView.Adapter<ResVH>() {
            override fun onCreateViewHolder(parent: android.view.ViewGroup, viewType: Int): ResVH {
                val v = android.view.LayoutInflater.from(parent.context).inflate(android.R.layout.simple_list_item_2, parent, false)
                return ResVH(v)
            }
            override fun getItemCount(): Int = options.size
            override fun onBindViewHolder(holder: ResVH, position: Int) { holder.bind(options[position], counts.getOrNull(position) ?: 0) }
        }
    }
}

private class ResVH(itemView: android.view.View) : androidx.recyclerview.widget.RecyclerView.ViewHolder(itemView) {
    private val t1: TextView = itemView.findViewById(android.R.id.text1)
    private val t2: TextView = itemView.findViewById(android.R.id.text2)
    fun bind(option: String, count: Int) {
        t1.text = option
        t2.text = "Số phiếu: $count"
    }
}


