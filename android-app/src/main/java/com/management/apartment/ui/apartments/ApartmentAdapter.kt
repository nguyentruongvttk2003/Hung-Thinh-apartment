package com.management.apartment.ui.apartments

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.management.apartment.databinding.ItemApartmentBinding
import com.management.apartment.network.ApartmentSimple

class ApartmentAdapter(
    private var items: List<ApartmentSimple>,
    private val onView: (ApartmentSimple) -> Unit = {},
    private val onEdit: (ApartmentSimple) -> Unit = {},
    private val onDelete: (ApartmentSimple) -> Unit = {}
): RecyclerView.Adapter<ApartmentAdapter.ApartmentVH>() {
    inner class ApartmentVH(val b: ItemApartmentBinding): RecyclerView.ViewHolder(b.root) {
        fun bind(item: ApartmentSimple) {
            b.textApartmentNumber.text = item.apartment_number ?: "--"
            b.textBlock.text = item.block ?: "-"
            b.textFloor.text = (item.floor ?: 0).toString()
            b.textArea.text = item.area?.let { "$it m²" } ?: "--"
            b.textBedrooms.text = item.bedrooms?.let { "$it phòng" } ?: "--"
            b.textOwnerName.text = item.owner_name ?: "--"
            val status = (item.status ?: "").lowercase()
            b.chipApartmentStatus.text = when (status) {
                "occupied" -> b.root.context.getString(com.management.apartment.R.string.status_occupied)
                "vacant" -> b.root.context.getString(com.management.apartment.R.string.status_vacant)
                "maintenance" -> b.root.context.getString(com.management.apartment.R.string.status_maintenance)
                "reserved" -> b.root.context.getString(com.management.apartment.R.string.status_reserved)
                else -> b.root.context.getString(com.management.apartment.R.string.status_vacant)
            }
            
            // Xử lý click vào card để hiện detail
            b.cardApartment.setOnClickListener {
                b.cardApartment.isEnabled = false
                onView(item)
                b.cardApartment.postDelayed({ b.cardApartment.isEnabled = true }, 600)
            }
            
            b.btnEdit.setOnClickListener {
                b.btnEdit.isEnabled = false
                onEdit(item)
                b.btnEdit.postDelayed({ b.btnEdit.isEnabled = true }, 600)
            }
            b.btnDelete.setOnClickListener {
                b.btnDelete.isEnabled = false
                onDelete(item)
                b.btnDelete.postDelayed({ b.btnDelete.isEnabled = true }, 600)
            }
        }
    }
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ApartmentVH {
        val inf = LayoutInflater.from(parent.context)
        return ApartmentVH(ItemApartmentBinding.inflate(inf, parent, false))
    }
    override fun getItemCount(): Int = items.size
    override fun onBindViewHolder(holder: ApartmentVH, position: Int) = holder.bind(items[position])
    fun update(newItems: List<ApartmentSimple>) { items = newItems; notifyDataSetChanged() }
}
