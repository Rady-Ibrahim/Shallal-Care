@extends('doctor.layout')

@section('title', 'وصفة طبية جديدة')
@section('page-title', 'وصفة طبية جديدة')
@section('page-description', 'إضافة وصفة طبية جديدة للمريض')

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
            <form id="prescriptionForm" onsubmit="createPrescription(event)">
                <div class="space-y-6">
                    <!-- Patient Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">المريض *</label>
                        <select id="patientId" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">اختر المريض</option>
                        </select>
                    </div>

                    <!-- Diagnosis -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">التشخيص *</label>
                        <textarea id="diagnosis" rows="3" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                            placeholder="اكتب التشخيص"></textarea>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                            placeholder="ملاحظات إضافية"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save ml-2"></i>حفظ الوصفة
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
        <!-- Instructions -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">تعليمات</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p>• اختر المريض من القائمة</p>
                <p>• اكتب التشخيص بدقة</p>
                <p>• أضف الأدوية المطلوبة</p>
                <p>• حدد الجرعة لكل دواء</p>
                <p>• أضف ملاحظات إذا لزم الأمر</p>
            </div>
        </div>

        <!-- Recent Prescriptions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">الوصفات الأخيرة</h3>
            <div id="recentPrescriptions" class="space-y-3">
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let medicineCounter = 0;

window.addEventListener('load', async function() {
    await loadPatients();
    await loadRecentPrescriptions();
    addMedicine(); // Add first medicine field
});

async function loadPatients() {
    try {
        const data = await apiCall('/doctor/api/patients?limit=100');
        
        if (data.success) {
            const select = document.getElementById('patientId');
            data.data.forEach(patient => {
                const option = document.createElement('option');
                option.value = patient.id;
                option.textContent = patient.name || 'غير محدد';
                select.appendChild(option);
            });

            // Pre-select patient if provided in URL
            const urlParams = new URLSearchParams(window.location.search);
            const patientId = urlParams.get('patient_id');
            if (patientId) {
                select.value = patientId;
            }
            const diagnosis = urlParams.get('diagnosis');
            if (diagnosis) {
                document.getElementById('diagnosis').value = decodeURIComponent(diagnosis);
            }
        }
    } catch (error) {
        console.error('Error loading patients:', error);
    }
}

async function loadRecentPrescriptions() {
    try {
        const data = await apiCall('/doctor/api/prescriptions?limit=5');
        
        if (data.success) {
            renderRecentPrescriptions(data.data);
        }
    } catch (error) {
        console.error('Error loading recent prescriptions:', error);
    }
}

function renderRecentPrescriptions(prescriptions) {
    const container = document.getElementById('recentPrescriptions');
    
    if (prescriptions.length === 0) {
        container.innerHTML = '<p class="text-gray-500">لا توجد وصفات</p>';
        return;
    }

    container.innerHTML = prescriptions.map(prescription => `
        <div class="p-3 bg-gray-50 rounded-lg">
            <p class="font-semibold text-gray-800">${prescription.patient_name || 'غير محدد'}</p>
            <p class="text-sm text-gray-600">${prescription.medicines_count || 0} دواء</p>
            <p class="text-xs text-gray-500">${formatDate(prescription.created_at)}</p>
        </div>
    `).join('');
}

function addMedicine() {
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
                    placeholder="اسم الدواء">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">الجرعة *</label>
                <input type="text" name="medicines[${medicineCounter}][dosage]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                    placeholder="مثال: 500mg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">عدد المرات *</label>
                <input type="number" name="medicines[${medicineCounter}][frequency]" required min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                    placeholder="مثال: 3">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">المدة *</label>
                <input type="text" name="medicines[${medicineCounter}][duration]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                    placeholder="مثال: 7 أيام">
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

async function createPrescription(event) {
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

        const data = await apiCall('/doctor/api/prescriptions', {
            method: 'POST',
            body: JSON.stringify({
                patient_id: document.getElementById('patientId').value,
                diagnosis: document.getElementById('diagnosis').value,
                medicines: medicines,
                notes: document.getElementById('notes').value,
            })
        });

        if (data.success) {
            alert('تم إنشاء الوصفة بنجاح');
            window.location.href = '/doctor/dashboard/prescriptions';
        } else {
            alert(data.error?.message || 'فشل إنشاء الوصفة');
        }
    } catch (error) {
        console.error('Error creating prescription:', error);
        alert('حدث خطأ أثناء إنشاء الوصفة');
    } finally {
        hideLoading();
    }
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('ar-IQ');
}
</script>
@endsection
