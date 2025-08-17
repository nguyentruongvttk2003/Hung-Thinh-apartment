package com.management.apartment.ui.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.material.appbar.MaterialToolbar
import com.management.apartment.databinding.FragmentApartmentsBinding
import com.management.apartment.data.SessionManager
import com.management.apartment.network.ApiEndpoints
import com.management.apartment.network.ApiService
import com.management.apartment.network.ApartmentSimple
import com.management.apartment.ui.apartments.ApartmentAdapter

class ApartmentsFragment: Fragment() {
    private var _binding: FragmentApartmentsBinding? = null
    private val binding get() = _binding!!
    private var adapter: ApartmentAdapter? = null
    private var apartments: List<ApartmentSimple> = emptyList()

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View? {
        _binding = FragmentApartmentsBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        view.findViewById<MaterialToolbar?>(com.management.apartment.R.id.toolbar)?.setNavigationOnClickListener {
            requireActivity().onBackPressedDispatcher.onBackPressed()
        }
        binding.recyclerViewApartments.layoutManager = LinearLayoutManager(requireContext())
        adapter = ApartmentAdapter(
            emptyList(),
            onView = { apt ->
                val msg = "Căn hộ: ${apt.apartment_number ?: apt.id}\nBlock: ${apt.block ?: "-"}\nTầng: ${apt.floor ?: "-"}\nDiện tích: ${apt.area ?: "-"}\nPhòng ngủ: ${apt.bedrooms ?: "-"}\nTrạng thái: ${apt.status ?: "-"}"
                AlertDialog.Builder(requireContext())
                    .setTitle("Chi tiết căn hộ")
                    .setMessage(msg)
                    .setNegativeButton("Đóng", null)
                    .setPositiveButton("Thành viên") { _, _ ->
                        val intent = android.content.Intent(requireContext(), com.management.apartment.ui.apartments.ApartmentResidentsActivity::class.java)
                        intent.putExtra("apartment_id", apt.id)
                        startActivity(intent)
                    }
                    .show()
            },
            onEdit = { apt ->
                val intent = android.content.Intent(requireContext(), com.management.apartment.ui.apartments.ApartmentResidentsActivity::class.java)
                intent.putExtra("apartment_id", apt.id)
                startActivity(intent)
            },
            onDelete = { apt ->
                AlertDialog.Builder(requireContext())
                    .setTitle("Xóa căn hộ")
                    .setMessage("Bạn có chắc muốn xóa ${apt.apartment_number ?: apt.id}?")
                    .setNegativeButton("Hủy", null)
                    .setPositiveButton("Xóa") { _, _ ->
                        Toast.makeText(requireContext(), "Đã gửi yêu cầu xóa (demo)", Toast.LENGTH_SHORT).show()
                    }
                    .show()
            }
        )
        binding.recyclerViewApartments.adapter = adapter
        loadApartments()

        // Handle FAB add apartment if present
        view.findViewById<com.google.android.material.floatingactionbutton.FloatingActionButton?>(com.management.apartment.R.id.fabAddApartment)?.setOnClickListener {
            startActivity(android.content.Intent(requireContext(), com.management.apartment.ui.apartments.ApartmentCreateActivity::class.java))
        }
    }

    private fun loadApartments() {
        val token = SessionManager(requireContext()).getToken() ?: return
        val api = ApiService.retrofit.create(ApiEndpoints::class.java)
        api.myApartments("Bearer $token").enqueue(object: retrofit2.Callback<List<ApartmentSimple>> {
            override fun onResponse(
                call: retrofit2.Call<List<ApartmentSimple>>,
                response: retrofit2.Response<List<ApartmentSimple>>
            ) {
                if (!response.isSuccessful) {
                    Toast.makeText(requireContext(), "Lỗi tải căn hộ (${response.code()})", Toast.LENGTH_SHORT).show()
                }
                apartments = response.body() ?: emptyList()
                if (apartments.isEmpty()) {
                    // Fallback: fetch full apartments list if user has none assigned
                    api.apartments("Bearer $token").enqueue(object: retrofit2.Callback<com.management.apartment.network.WrappedList<ApartmentSimple>> {
                        override fun onResponse(
                            call: retrofit2.Call<com.management.apartment.network.WrappedList<ApartmentSimple>>,
                            response2: retrofit2.Response<com.management.apartment.network.WrappedList<ApartmentSimple>>
                        ) {
                            if (!response2.isSuccessful) {
                                Toast.makeText(requireContext(), "Lỗi tải danh sách (${response2.code()})", Toast.LENGTH_SHORT).show()
                            }
                            apartments = response2.body()?.data ?: emptyList()
                            adapter?.update(apartments)
                            updateStates()
                        }
                        override fun onFailure(
                            call: retrofit2.Call<com.management.apartment.network.WrappedList<ApartmentSimple>>,
                            t: Throwable
                        ) {
                            Toast.makeText(requireContext(), t.message ?: "Lỗi kết nối", Toast.LENGTH_SHORT).show()
                            adapter?.update(emptyList())
                            apartments = emptyList()
                            updateStates()
                        }
                    })
                } else {
                    adapter?.update(apartments)
                    updateStates()
                }
            }
            override fun onFailure(call: retrofit2.Call<List<ApartmentSimple>>, t: Throwable) {
                Toast.makeText(requireContext(), t.message ?: "Lỗi kết nối", Toast.LENGTH_SHORT).show()
                apartments = emptyList()
                updateStates()
            }
        })
    }

    private fun updateStates() {
        val b = _binding ?: return
        val empty = apartments.isEmpty()
        b.layoutEmptyState.visibility = if (empty) View.VISIBLE else View.GONE
        b.recyclerViewApartments.visibility = if (empty) View.GONE else View.VISIBLE
        b.textTotalApartments.text = apartments.size.toString()
        val occupied = apartments.count { (it.status ?: "").equals("occupied", true) }
        val vacant = apartments.count { (it.status ?: "").equals("vacant", true) }
        b.textOccupiedApartments.text = occupied.toString()
        b.textVacantApartments.text = vacant.toString()
    }

    private fun showCreateApartmentDialog() {
        val ctx = requireContext()
        val container = android.widget.LinearLayout(ctx).apply {
            orientation = android.widget.LinearLayout.VERTICAL
            setPadding(32, 16, 32, 0)
        }
        val inputNumber = android.widget.EditText(ctx).apply { hint = "Mã căn hộ (vd: A1-101)" }
        val inputBlock = android.widget.EditText(ctx).apply { hint = "Block" }
        val inputFloor = android.widget.EditText(ctx).apply { hint = "Tầng"; inputType = android.text.InputType.TYPE_CLASS_NUMBER }
        val inputArea = android.widget.EditText(ctx).apply { hint = "Diện tích (m²)"; inputType = android.text.InputType.TYPE_NUMBER_FLAG_DECIMAL or android.text.InputType.TYPE_CLASS_NUMBER }
        val inputType = android.widget.EditText(ctx).apply { hint = "Loại (studio/1br/2br/3br/4br)" }
        val inputStatus = android.widget.EditText(ctx).apply { hint = "Trạng thái (occupied/vacant/maintenance/reserved)" }
        container.addView(inputNumber)
        container.addView(inputBlock)
        container.addView(inputFloor)
        container.addView(inputArea)
        container.addView(inputType)
        container.addView(inputStatus)
        androidx.appcompat.app.AlertDialog.Builder(ctx)
            .setTitle("Thêm căn hộ")
            .setView(container)
            .setNegativeButton("Hủy", null)
            .setPositiveButton("Thêm") { _, _ ->
                createApartment(
                    number = inputNumber.text.toString().trim(),
                    block = inputBlock.text.toString().trim(),
                    floor = inputFloor.text.toString().toIntOrNull(),
                    area = inputArea.text.toString().trim(),
                    type = inputType.text.toString().trim(),
                    status = inputStatus.text.toString().trim()
                )
            }
            .show()
    }

    private fun createApartment(number: String, block: String?, floor: Int?, area: String?, type: String?, status: String?) {
        if (number.isEmpty() || floor == null || area.isNullOrEmpty() || type.isNullOrEmpty() || status.isNullOrEmpty()) {
            android.widget.Toast.makeText(requireContext(), "Điền đủ thông tin bắt buộc", android.widget.Toast.LENGTH_SHORT).show(); return
        }
        val token = com.management.apartment.data.SessionManager(requireContext()).getToken() ?: return
        val api = com.management.apartment.network.ApiService.retrofit.create(com.management.apartment.network.ApiEndpoints::class.java)
        val body = mutableMapOf<String, Any?>(
            "apartment_number" to number,
            "floor" to floor,
            "area" to area,
            "type" to type,
            "status" to status
        )
        if (!block.isNullOrEmpty()) body["block"] = block
        api.createApartment("Bearer $token", body).enqueue(object: retrofit2.Callback<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>> {
            override fun onResponse(
                call: retrofit2.Call<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>>,
                response: retrofit2.Response<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>>
            ) {
                android.widget.Toast.makeText(requireContext(), if (response.isSuccessful) "Đã thêm căn hộ" else "Thêm thất bại", android.widget.Toast.LENGTH_SHORT).show()
                loadApartments()
            }
            override fun onFailure(
                call: retrofit2.Call<com.management.apartment.network.GenericResponse<com.management.apartment.network.ApartmentDetail>>,
                t: Throwable
            ) {
                android.widget.Toast.makeText(requireContext(), t.message, android.widget.Toast.LENGTH_SHORT).show()
            }
        })
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onResume() {
        super.onResume()
        // Reload to reflect newly created/updated apartments when returning from forms
        loadApartments()
    }
}


