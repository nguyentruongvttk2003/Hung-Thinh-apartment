package com.management.apartment.ui.events

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.management.apartment.databinding.ItemEventBinding
import com.management.apartment.network.EventDto

class EventAdapter(private var items: List<EventDto>): RecyclerView.Adapter<EventAdapter.EventVH>() {
    inner class EventVH(val b: ItemEventBinding): RecyclerView.ViewHolder(b.root) {
        fun bind(item: EventDto) {
            b.textTitle.text = item.title
            b.textTime.text = item.start_time ?: "--"
            b.root.setOnClickListener {
                val ctx = b.root.context
                val i = android.content.Intent(ctx, EventDetailActivity::class.java)
                i.putExtra("title", item.title)
                i.putExtra("time", item.start_time ?: "--")
                i.putExtra("desc", item.description ?: "")
                ctx.startActivity(i)
            }
        }
    }
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): EventVH {
        val inf = LayoutInflater.from(parent.context)
        return EventVH(ItemEventBinding.inflate(inf, parent, false))
    }
    override fun getItemCount(): Int = items.size
    override fun onBindViewHolder(holder: EventVH, position: Int) = holder.bind(items[position])
    fun update(newItems: List<EventDto>) { items = newItems; notifyDataSetChanged() }
}
