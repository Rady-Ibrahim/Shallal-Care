@extends('doctor.layout')

@section('title', 'تعديل سجل طبي')
@section('page-title', 'تعديل سجل طبي')
@section('page-description', 'تعديل بيانات السجل الطبي')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="/doctor/dashboard/records" class="text-teal-600 hover:text-teal-700 flex items-center gap-2">
        <i class="fas fa-arrow-right"></i>
        <span>العودة إلى السجلات</span>
    </a>
</div>

<!-- Record Form -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form id="recordForm" onsubmit="updateRecord(event)">
                <div class="space-y-6">
                    <!-- Patient Info (Read-only) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">المريض</label>
                        <input type="text" id="patientName" readonly
                            class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Record Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">نوع السجل *</label>
                        <select id="recordType" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                            <option value="">اختر النوع</option>
                            <option value="diagnosis">تشخيص</option>
                            <option value="treatment">علاج</option>
                            <option value="lab_test">اختبار معملي</option>
                            <option value="imaging">تصوير</option>
                            <option value="surgery">جراحة</option>
                            <option value="consultation">استشارة</option>
                        </select>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">العنوان *</label>
                        <input type="text" id="recordTitle" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">الوصف *</label>
                        <textarea id="recordDescription" rows="5" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>

                    <!-- Existing Files -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">الملفات المرفقة</label>
                        <div id="existingFiles" class="space-y-2">
                            <!-- Existing files will be listed here -->
                        </div>
                    </div>

                    <!-- Add New Files -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">إضافة ملفات جديدة</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input type="file" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="hidden" onchange="handleFileSelect(event)">
                            <label for="fileInput" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">اضغط لرفع الملفات أو اسحب الملفات هنا</p>
                            </label>
                        </div>
                        <div id="newFileList" class="mt-4 space-y-2">
                            <!-- New selected files will be listed here -->
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ملاحظات</label>
                        <textarea id="recordNotes" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                            <i class="fas fa-save ml-2"></i>حفظ التغييرات
                        </button>
                        <a href="/doctor/dashboard/records" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-center">
                            إلغاء
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Record Info -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">معلومات السجل</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">تاريخ الإنشاء</p>
                    <p class="font-semibold text-gray-800" id="createdAt">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">آخر تحديث</p>
                    <p class="font-semibold text-gray-800" id="updatedAt">-</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">إجراءات</h3>
            <div class="space-y-3">
                <button onclick="printRecord()" class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                    <i class="fas fa-print ml-2"></i>طباعة
                </button>
                <button onclick="downloadPDF()" class="block w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-center">
                    <i class="fas fa-download ml-2"></i>تحميل PDF
                </button>
                <button onclick="deleteRecord()" class="block w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center">
                    <i class="fas fa-trash ml-2"></i>حذف
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const recordId = {{ (int) $recordId }};
let selectedFiles = [];
let existingFileIds = [];
let loadedAttachments = [];

window.addEventListener('load', async function() {
    await loadRecord();
});

async function loadRecord() {
    try {
        showLoading();
        
        const data = await apiCall(`/doctor/api/records/${recordId}`);
        
        if (data.success) {
            renderRecord(data.data);
        } else {
            alert(data.error?.message || 'فشل تحميل البيانات');
        }
    } catch (error) {
        console.error('Error loading record:', error);
        alert('حدث خطأ أثناء تحميل البيانات');
    } finally {
        hideLoading();
    }
}

function renderRecord(record) {
    document.getElementById('patientName').value = record.patient_name || 'غير محدد';
    document.getElementById('recordType').value = record.type || '';
    document.getElementById('recordTitle').value = record.title || '';
    document.getElementById('recordDescription').value = record.description || '';
    document.getElementById('recordNotes').value = record.notes || '';
    document.getElementById('createdAt').textContent = formatDate(record.created_at);
    document.getElementById('updatedAt').textContent = formatDate(record.updated_at);

    // Render existing files
    const existingFilesContainer = document.getElementById('existingFiles');
    loadedAttachments = record.attachments || [];
    if (loadedAttachments.length > 0) {
        existingFileIds = loadedAttachments.map(att => String(att.id));
        renderExistingFiles();
    } else {
        existingFileIds = [];
        existingFilesContainer.innerHTML = '<p class="text-gray-500">لا توجد ملفات مرفقة</p>';
    }
}

function renderExistingFiles() {
    const existingFilesContainer = document.getElementById('existingFiles');
    const visible = loadedAttachments.filter(att => existingFileIds.includes(String(att.id)));

    if (visible.length === 0) {
        existingFilesContainer.innerHTML = '<p class="text-gray-500">لا توجد ملفات مرفقة</p>';
        return;
    }

    existingFilesContainer.innerHTML = visible.map(att => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-3">
                <i class="fas fa-file text-gray-500"></i>
                <div>
                    <p class="text-sm font-semibold text-gray-800">${att.file_name || 'ملف'}</p>
                    <p class="text-xs text-gray-500">${formatFileSize(att.file_size)}</p>
                </div>
            </div>
            <button type="button" onclick="removeExistingFile('${att.id}')" class="text-red-600 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

function handleFileSelect(event) {
    const files = event.target.files;
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        
        if (file.size > 10 * 1024 * 1024) {
            alert(`الملف ${file.name} يتجاوز الحد الأقصى المسموح (10MB)`);
            continue;
        }
        
        selectedFiles.push(file);
    }
    
    renderNewFileList();
}

function renderNewFileList() {
    const container = document.getElementById('newFileList');
    
    if (selectedFiles.length === 0) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = selectedFiles.map((file, index) => `
        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
            <div class="flex items-center gap-3">
                <i class="fas fa-file text-blue-500"></i>
                <div>
                    <p class="text-sm font-semibold text-gray-800">${file.name}</p>
                    <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                </div>
            </div>
            <button type="button" onclick="removeNewFile(${index})" class="text-red-600 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

function removeNewFile(index) {
    selectedFiles.splice(index, 1);
    renderNewFileList();
}

function removeExistingFile(fileId) {
    existingFileIds = existingFileIds.filter(id => String(id) !== String(fileId));
    renderExistingFiles();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

async function updateRecord(event) {
    event.preventDefault();
    
    try {
        showLoading();
        
        const formData = new FormData();
        formData.append('type', document.getElementById('recordType').value);
        formData.append('title', document.getElementById('recordTitle').value);
        formData.append('description', document.getElementById('recordDescription').value);
        formData.append('notes', document.getElementById('recordNotes').value);
        
        // Add existing file IDs to keep
        existingFileIds.forEach(id => {
            formData.append('existing_files[]', id);
        });
        
        // Add new files
        for (let i = 0; i < selectedFiles.length; i++) {
            formData.append('files[]', selectedFiles[i]);
        }

        formData.append('_method', 'PUT');
        const data = await apiUpload(`/doctor/api/records/${recordId}`, formData, 'POST');

        if (data && data.success) {
            alert('تم تحديث السجل بنجاح');
            window.location.href = '/doctor/dashboard/records';
        } else {
            alert(data.error?.message || 'فشل تحديث السجل');
        }
    } catch (error) {
        console.error('Error updating record:', error);
        alert('حدث خطأ أثناء تحديث السجل');
    } finally {
        hideLoading();
    }
}

function printRecord() {
    window.print();
}

function downloadPDF() {
    window.open(`/doctor/dashboard/records/${recordId}?print=1`, '_blank');
}

async function deleteRecord() {
    if (!await confirmAction('هل أنت متأكد من حذف هذا السجل؟')) return;

    try {
        const data = await apiCall(`/doctor/api/records/${recordId}`, {
            method: 'DELETE'
        });

        if (data.success) {
            alert('تم الحذف بنجاح');
            window.location.href = '/doctor/dashboard/records';
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
