package com.hungthinh.apartment.ui.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.hungthinh.apartment.data.model.RecentActivity
import com.hungthinh.apartment.databinding.ItemRecentActivityBinding
import java.text.SimpleDateFormat
import java.util.*

class RecentActivityAdapter(
    private val onItemClick: (RecentActivity) -> Unit
) : ListAdapter<RecentActivity, RecentActivityAdapter.ViewHolder>(DiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemRecentActivityBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    inner class ViewHolder(
        private val binding: ItemRecentActivityBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(activity: RecentActivity) {
            binding.apply {
                tvActivityIcon.text = activity.getTypeIcon()
                tvActivityTitle.text = activity.title
                tvActivityDescription.text = activity.description
                tvActivityType.text = activity.getTypeDisplayName()
                
                // Format created date
                try {
                    val inputFormat = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                    val outputFormat = SimpleDateFormat("dd/MM HH:mm", Locale.getDefault())
                    val date = inputFormat.parse(activity.createdAt)
                    tvActivityTime.text = date?.let { outputFormat.format(it) } ?: activity.createdAt
                } catch (e: Exception) {
                    tvActivityTime.text = activity.createdAt
                }
                
                // Show user info if available
                activity.userName?.let { userName ->
                    tvActivityUser.text = userName
                }
                
                activity.apartmentNumber?.let { apartmentNumber ->
                    tvActivityApartment.text = "Căn $apartmentNumber"
                }
                
                root.setOnClickListener {
                    onItemClick(activity)
                }
            }
        }
    }

    private class DiffCallback : DiffUtil.ItemCallback<RecentActivity>() {
        override fun areItemsTheSame(oldItem: RecentActivity, newItem: RecentActivity): Boolean {
            return oldItem.id == newItem.id
        }

        override fun areContentsTheSame(oldItem: RecentActivity, newItem: RecentActivity): Boolean {
            return oldItem == newItem
        }
    }
}
