@extends('doctor.layout')

@section('title', 'تعديل وصفة طبية')
@section('page-title', 'تعديل وصفة طبية')
@section('page-description', 'تعديل بيانات الوصفة الطبية')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="/doctor/dashboard/prescriptions" class="text-teal-600 hover:text-teal-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى الوصفات</span>
    </a>
</div>

<!-- Prescription Form -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form id="prescriptionForm" onsubmit="updatePrescription(event)">
                <div class="space-y-6">
                    <!-- Patient Info (Read-only) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">المريض</label>
                        <input type="text" id="patientName" readonly
                            class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Diagnosis -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">التشخيص *</label>
                        <textarea id="diagnosis" rows="3" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>

                    <!-- Medicines -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700">الأدوية *</label>
                            <button type="button" onclick="addMedicine()" class="text-teal-600 hover:text-teal-700 text-sm">
                                <i class="fas fa-plus ml-1"></i>إضافة دواء
                            </button>
                        </div>
                        <div id="medicinesList" class="space-y-4">
                            <!-- Medicine items will be added here -->
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ملاحظات</label>
                        <textarea id="notes" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save ml-2"></i>حفظ التغييرات
                        </button>
                        <a href="/doctor/dashboard/prescriptions" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center">
                            إلغاء
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Prescription Info -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">معلومات الوصفة</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">تاريخ الإنشاء</p>
                    <p class="font-semibold text-gray-800" id="createdAt">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">الحالة</p>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        نشط
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">إجراءات</h3>
            <div class="space-y-3">
                <button onclick="printPrescription()" class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                    <i class="fas fa-print ml-2"></i>طباعة
                </button>
                <button onclick="downloadPDF()" class="block w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center">
                    <i class="fas fa-download ml-2"></i>تحميل PDF
                </button>
                <button onclick="deletePrescription()" class="block w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center">
                    <i class="fas fa-trash ml-2"></i>حذف
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const prescriptionId = {{ (int) $prescriptionId }};
let medicineCounter = 0;

window.addEventListener('load', async function() {
    await loadPrescription();
});

async function loadPrescription() {
    try {
        showLoading();
        
        const data = await apiCall(`/doctor/api/prescriptions/${prescriptionId}`);
        
        if (data.success) {
            renderPrescription(data.data);
        } else {
            alert(data.error?.message || 'فشل تحميل البيانات');
        }
    } catch (error) {
        console.error('Error loading prescription:', error);
        alert('حدث خطأ أثناء تحميل البيانات');
    } finally {
        hideLoading();
    }
}

function renderPrescription(prescription) {
    document.getElementById('patientName').value = prescription.patient_name || 'غير محدد';
    document.getElementById('diagnosis').value = prescription.diagnosis || '';
    document.getElementById('notes').value = prescription.notes || '';
    document.getElementById('createdAt').textContent = formatDate(prescription.created_at);

    // Render medicines
    const medicinesList = document.getElementById('medicinesList');
    medicinesList.innerHTML = '';
    
    if (prescription.medicines && prescription.medicines.length > 0) {
        prescription.medicines.forEach(medicine => {
            addMedicine(medicine);
        });
    } else {
        addMedicine();
    }
}

function addMedicine(medicine = null) {
    medicineCounter++;
    const container = document.getElementById('medicinesList');
    
    const medicineDiv = document.createElement('div');
    medicineDiv.className = 'p-4 bg-gray-50 rounded-lg';
    medicineDiv.id = `medicine-${medicineCounter}`;
    
    medicineDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">اسم الدواء *</label>
                <input type="text" name="medicines[${medicineCounter}][name]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                    value="${medicine?.name || ''}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">الجرعة *</label>
                <input type="text" name="medicines[${medicineCounter}][dosage]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                    value="${medicine?.dosage || ''}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">عدد المرات *</label>
                <input type="number" name="medicines[${medicineCounter}][frequency]" required min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                    value="${medicine?.frequency || ''}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">المدة *</label>
                <input type="text" name="medicines[${medicineCounter}][duration]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                    value="${medicine?.duration || ''}">
            </div>
        </div>
        <button type="button" onclick="removeMedicine(${medicineCounter})" class="mt-2 text-red-600 hover:text-red-700 text-sm">
            <i class="fas fa-trash ml-1"></i>حذف الدواء
        </button>
    `;
    
    container.appendChild(medicineDiv);
}

function removeMedicine(id) {
    const medicineDiv = document.getElementById(`medicine-${id}`);
    if (medicineDiv) {
        medicineDiv.remove();
    }
}

async function updatePrescription(event) {
    event.preventDefault();
    
    try {
        showLoading();
        
        // Collect medicines
        const medicines = [];
        const medicineItems = document.querySelectorAll('[id^="medicine-"]');
        
        medicineItems.forEach(item => {
            const name = item.querySelector('[name*="[name]"]').value;
            const dosage = item.querySelector('[name*="[dosage]"]').value;
            const frequency = item.querySelector('[name*="[frequency]"]').value;
            const duration = item.querySelector('[name*="[duration]"]').value;
            
            if (name && dosage && frequency && duration) {
                medicines.push({ name, dosage, frequency, duration });
            }
        });

        if (medicines.length === 0) {
            alert('يجب إضافة دواء واحد على الأقل');
            hideLoading();
            return;
        }

        const data = await apiCall(`/doctor/api/prescriptions/${prescriptionId}`, {
            method: 'PUT',
            body: JSON.stringify({
                diagnosis: document.getElementById('diagnosis').value,
                medicines: medicines,
                notes: document.getElementById('notes').value,
            })
        });

        if (data.success) {
            alert('تم تحديث الوصفة بنجاح');
            window.location.href = '/doctor/dashboard/prescriptions';
        } else {
            alert(data.error?.message || 'فشل تحديث الوصفة');
        }
    } catch (error) {
        console.error('Error updating prescription:', error);
        alert('حدث خطأ أثناء تحديث الوصفة');
    } finally {
        hideLoading();
    }
}

function printPrescription() {
    window.print();
}

function downloadPDF() {
    alert('سيتم تحميل الوصفة بصيغة PDF');
}

async function deletePrescription() {
    if (!await confirmAction('هل أنت متأكد من حذف هذه الوصفة؟')) return;

    try {
        const data = await apiCall(`/doctor/api/prescriptions/${prescriptionId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            window.location.href = '/doctor/dashboard/prescriptions';
        } else {
            alert(data.error?.message || 'فشلت العملية');
        }
    } catch (error) {
        alert('حدث خطأ أثناء الحذف');
    }
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-EG');
}
</script>
@endsection
