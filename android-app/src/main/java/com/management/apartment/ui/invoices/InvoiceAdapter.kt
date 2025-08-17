package com.management.apartment.ui.invoices

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.management.apartment.databinding.ItemInvoiceBinding
import com.management.apartment.network.InvoiceDto

class InvoiceAdapter(
    private var items: List<InvoiceDto>,
    private val onClick: (InvoiceDto) -> Unit
): RecyclerView.Adapter<InvoiceAdapter.InvoiceVH>() {

    inner class InvoiceVH(val binding: ItemInvoiceBinding): RecyclerView.ViewHolder(binding.root) {
        fun bind(item: InvoiceDto) {
            binding.textInvoiceNumber.text = item.invoice_number
            binding.textTotalAmount.text = String.format("%,.0f VNĐ", item.total_amount)
            // Simplified: outstanding = total if status != paid else 0
            val outstanding = if (item.status != "paid") item.total_amount else 0.0
            binding.textOutstandingAmount.text = String.format("%,.0f VNĐ", outstanding)
            binding.chipInvoiceStatus.text = when(item.status) {
                "paid" -> "Đã TT"
                "overdue" -> "Quá hạn"
                else -> "Chờ"
            }
            binding.root.setOnClickListener { onClick(item) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): InvoiceVH {
        val inflater = LayoutInflater.from(parent.context)
        val binding = ItemInvoiceBinding.inflate(inflater, parent, false)
        return InvoiceVH(binding)
    }

    override fun getItemCount(): Int = items.size

    override fun onBindViewHolder(holder: InvoiceVH, position: Int) = holder.bind(items[position])

    fun update(newItems: List<InvoiceDto>) {
        items = newItems
        notifyDataSetChanged()
    }
}
