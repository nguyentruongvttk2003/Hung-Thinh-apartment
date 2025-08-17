package com.management.apartment.ui.notifications

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.management.apartment.databinding.ItemNotificationBinding
import com.management.apartment.network.NotificationDto

class NotificationAdapter(private var items: List<NotificationDto>): RecyclerView.Adapter<NotificationAdapter.NotificationVH>() {
    inner class NotificationVH(val b: ItemNotificationBinding): RecyclerView.ViewHolder(b.root) {
        fun bind(item: NotificationDto) {
            b.textTitle.text = item.title
            b.textContent.text = item.content
        }
    }
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): NotificationVH {
        val inf = LayoutInflater.from(parent.context)
        return NotificationVH(ItemNotificationBinding.inflate(inf, parent, false))
    }
    override fun getItemCount(): Int = items.size
    override fun onBindViewHolder(holder: NotificationVH, position: Int) = holder.bind(items[position])
    fun update(newItems: List<NotificationDto>) { items = newItems; notifyDataSetChanged() }
}
