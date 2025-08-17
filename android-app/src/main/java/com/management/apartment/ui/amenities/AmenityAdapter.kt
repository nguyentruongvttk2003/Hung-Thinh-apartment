package com.management.apartment.ui.amenities

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.management.apartment.databinding.ItemAmenityBinding
import com.management.apartment.network.AmenityDto

class AmenityAdapter(
    private var items: List<AmenityDto>,
    private val onClick: (AmenityDto) -> Unit
): RecyclerView.Adapter<AmenityAdapter.AmenityVH>() {
    inner class AmenityVH(val b: ItemAmenityBinding): RecyclerView.ViewHolder(b.root) {
        fun bind(item: AmenityDto) {
            b.textName.text = item.name
            b.textType.text = item.type
            b.root.setOnClickListener { onClick(item) }
        }
    }
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): AmenityVH {
        val inf = LayoutInflater.from(parent.context)
        return AmenityVH(ItemAmenityBinding.inflate(inf, parent, false))
    }
    override fun getItemCount(): Int = items.size
    override fun onBindViewHolder(holder: AmenityVH, position: Int) = holder.bind(items[position])
    fun update(newItems: List<AmenityDto>) { items = newItems; notifyDataSetChanged() }
}
