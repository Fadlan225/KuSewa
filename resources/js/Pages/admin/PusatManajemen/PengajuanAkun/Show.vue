<script setup>
import { ChevronLeft, CheckCircle, XCircle, ExternalLink, ImageOff } from 'lucide-vue-next';
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import AvatarMale from '@/Components/ui/Icons/AvatarMale.vue';
import AvatarFemale from '@/Components/ui/Icons/AvatarFemale.vue';
import AvatarDefault from '@/Components/ui/Icons/AvatarDefault.vue';
import EmptyStateData from '@/Components/ui/Icons/EmptyStateData.vue';

const props = defineProps({
    applicant: Object
});

const rejectReason      = ref('');
const showRejectInput   = ref(false);
const isSubmitting      = ref(false);
const ktpImageError     = ref(false);

const handleApprove = () => {
    isSubmitting.value = true;
    router.patch(route('admin.pengajuan-akun.approve', props.applicant.id), {}, {
        preserveScroll: true,
        onSuccess: () => { isSubmitting.value = false; },
        onError:   () => { isSubmitting.value = false; },
    });
};

const openReject = () => { showRejectInput.value = true; rejectReason.value = ''; };
const cancelReject = () => { showRejectInput.value = false; rejectReason.value = ''; };
const handleReject = () => {
    if (!rejectReason.value.trim()) return;
    isSubmitting.value = true;
    router.patch(route('admin.pengajuan-akun.reject', props.applicant.id), { reason: rejectReason.value }, {
        preserveScroll: true,
        onSuccess: () => { showRejectInput.value = false; isSubmitting.value = false; },
        onError:   () => { isSubmitting.value = false; },
    });
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
const formatDateTime = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
const statusClass = (s) => ({
    pending:  'bg-amber-50 text-amber-700 border-amber-200',
    verified: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-rose-50 text-rose-700 border-rose-200',
}[s] ?? 'bg-slate-50 text-slate-700 border-slate-200');
const statusLabel = (s) => ({ pending: 'Menunggu', verified: 'Disetujui', rejected: 'Ditolak' }[s] ?? s);

const translateGender = (gender) => {
    const map = { male: 'Laki-laki', female: 'Perempuan' };
    return map[gender] || gender;
};
</script>

<template>
    <Head title="Detail Pengajuan Akun - Admin Panel" />

    <DashboardLayout 
        role="Admin" 
        title="Detail Pengajuan Akun" 
        description="Verifikasi kesesuaian dokumen pengajuan owner."
    >
        <template #leftAction>
            <Link :href="route('admin.pengajuan-akun')" class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-[#0A2540] transition-colors mr-3 group">
                <ChevronLeft class="w-4 h-4 mr-1 transition-transform group-hover:-translate-x-0.5" />
                Kembali ke Kelola Pengajuan Akun
            </Link>
        </template>

        <div class="space-y-6 mt-6 max-w-5xl mx-auto">

            <!-- HEADER PROFILE -->
            <div class="flex flex-col md:flex-row items-start gap-6 bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 relative">
                <div class="w-24 h-24 rounded-full overflow-hidden shrink-0 border border-slate-200 bg-slate-50 flex items-center justify-center">
                    <img v-if="applicant.user?.avatar" :src="applicant.user.avatar" :alt="applicant.user.name" class="w-full h-full object-cover" />
                    <AvatarMale v-else-if="applicant.user?.gender === 'male'" class="w-full h-full" />
                    <AvatarFemale v-else-if="applicant.user?.gender === 'female'" class="w-full h-full" />
                    <AvatarDefault v-else class="w-full h-full" />
                </div>
                
                <div class="flex-1 flex flex-col sm:flex-row justify-between gap-6 w-full">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-black text-[#0A2540]">{{ applicant.user?.name }}</h2>
                            <span :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold border', statusClass(applicant.status)]">
                                <span class="w-1.5 h-1.5 rounded-full" :class="{
                                    'bg-amber-500': applicant.status === 'pending',
                                    'bg-emerald-500': applicant.status === 'verified',
                                    'bg-rose-500': applicant.status === 'rejected'
                                }"></span>
                                {{ statusLabel(applicant.status) }}
                            </span>
                        </div>
                        
                        <p class="text-slate-500 text-sm mt-1 font-medium">{{ applicant.user?.email }}</p>
                        
                        <div class="mt-2.5 flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-400 bg-slate-50 border border-slate-100 rounded px-2.5 py-1 font-mono">NIK: {{ applicant.national_id ?? '-' }}</span>
                        </div>
                        
                        <div class="mt-5 flex flex-wrap items-center gap-x-8 gap-y-2 text-sm font-semibold text-slate-500">
                            <div>Tanggal Pengajuan <span class="text-slate-900">{{ formatDate(applicant.created_at) }}</span></div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:items-end justify-end gap-3 shrink-0">
                        <div v-if="applicant.status === 'pending'" class="w-full sm:w-auto max-w-sm">
                            <div v-if="showRejectInput" class="space-y-2 text-right">
                                <textarea
                                    v-model="rejectReason"
                                    rows="2"
                                    placeholder="Alasan penolakan..."
                                    class="w-full text-xs border border-slate-300 focus:ring-2 focus:ring-rose-400 outline-none rounded-lg px-3 py-2 transition resize-none"
                                ></textarea>
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="cancelReject" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">Batal</button>
                                    <button
                                        @click="handleReject"
                                        :disabled="!rejectReason.trim() || isSubmitting"
                                        class="px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 disabled:opacity-50 transition"
                                    >{{ isSubmitting ? 'Memproses...' : 'Tolak' }}</button>
                                </div>
                            </div>
                            <div v-else class="flex gap-2 w-full sm:w-auto">
                                <button
                                    @click="openReject"
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-2 rounded px-4 py-2 text-xs font-bold transition shadow-sm border bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100"
                                >
                                    <XCircle class="w-3.5 h-3.5" /> Tolak
                                </button>
                                <button
                                    @click="handleApprove"
                                    :disabled="isSubmitting"
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-2 rounded px-4 py-2 text-xs font-bold transition shadow-sm border bg-emerald-600 text-white border-emerald-700 hover:bg-emerald-700 disabled:opacity-60"
                                >
                                    <CheckCircle class="w-3.5 h-3.5" /> {{ isSubmitting ? 'Memproses...' : 'Setujui' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-COLUMNS: DATA CALON PEMILIK & LAMPIRAN KTP -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Data Calon Pemilik -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 shrink-0">
                        <h3 class="text-sm font-bold text-slate-800">Data Calon Pemilik</h3>
                    </div>
                    <div class="p-6 flex-1">
                        <div class="space-y-4">
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ applicant.user?.name }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nomor NIK</div>
                                <div class="sm:col-span-2 text-sm font-mono font-semibold text-slate-900">{{ applicant.national_id ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Email</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ applicant.user?.email ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Telepon / WA</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ applicant.user?.phone ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900 capitalize">{{ translateGender(applicant.user?.gender) ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Lahir</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ formatDate(applicant.user?.date_of_birth) }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pekerjaan</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ applicant.occupation ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Agama</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900">{{ applicant.religion ?? '-' }}</div>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] sm:grid-cols-3 pt-3 border-t border-slate-100 mt-2">
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</div>
                                <div class="sm:col-span-2 text-sm font-semibold text-slate-900 leading-relaxed">{{ applicant.address ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lampiran KTP -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
                        <h3 class="text-sm font-bold text-slate-800">Lampiran KTP</h3>
                        <a
                            v-if="applicant.ktp_url && !ktpImageError"
                            :href="applicant.ktp_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 hover:text-slate-800 transition"
                        >
                            <ExternalLink class="w-3 h-3" />
                            Buka di tab baru
                        </a>
                    </div>
                    <div class="p-6 flex-1 flex flex-col items-center justify-center bg-slate-50">
                        <div class="w-full h-64 bg-slate-200/50 rounded-2xl overflow-hidden shadow-inner relative flex items-center justify-center">
                            <img
                                v-if="applicant.ktp_url && !ktpImageError"
                                :src="applicant.ktp_url"
                                class="w-full h-full object-contain cursor-zoom-in"
                                @error="ktpImageError = true"
                                alt="Foto KTP"
                            />
                            <div v-else class="flex flex-col items-center justify-center text-slate-400 text-xs gap-3">
                                <ImageOff class="w-10 h-10 text-slate-300" />
                                <span class="font-medium">{{ ktpImageError ? 'Gagal memuat foto KTP' : 'Tidak ada foto KTP' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT VERIFIKASI -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden mt-6">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">Riwayat Verifikasi</h3>
                </div>
                <div class="p-6">
                    <div v-if="applicant.verification_logs?.length" class="space-y-4">
                        <div
                            v-for="(log, idx) in applicant.verification_logs"
                            :key="idx"
                            class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 py-4 border-b border-slate-100 last:border-0 last:pb-0 first:pt-0"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="[
                                        'font-bold text-sm capitalize',
                                        log.action === 'submitted' ? 'text-slate-700' :
                                        log.action === 'approved'  ? 'text-slate-700' : 'text-slate-700'
                                    ]">
                                        {{ log.action === 'submitted' ? 'Pengajuan Dikirim' : log.action === 'approved' ? 'Pengajuan Disetujui' : 'Pengajuan Ditolak' }}
                                    </span>
                                     <span :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                                        log.action === 'submitted' ? 'bg-slate-100 text-slate-500' :
                                        log.action === 'approved'  ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'
                                    ]">
                                         {{ log.action === 'submitted' ? 'Info' : log.action === 'approved' ? 'Sukses' : 'Ditolak' }}
                                     </span>
                                </div>
                                <p v-if="log.actor" class="text-slate-500 text-xs font-medium">
                                    Oleh: <span class="text-slate-800">{{ log.actor.name }}</span>
                                </p>
                                <p v-if="log.reason" class="mt-2 text-slate-600 text-sm italic border-l-2 border-slate-200 pl-3 py-1">
                                    "{{ log.reason }}"
                                </p>
                            </div>
                             <div class="text-slate-400 text-xs font-medium whitespace-nowrap shrink-0 sm:mt-1">
                                {{ formatDateTime(log.created_at) }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 flex flex-col items-center justify-center border border-dashed border-slate-200 rounded-lg bg-slate-50/50">
                        <h3 class="text-slate-700 font-bold text-sm mb-1">Belum Ada Riwayat</h3>
                        <p class="text-slate-500 font-medium text-xs max-w-sm mx-auto leading-relaxed">
                            Log aktivitas verifikasi untuk calon pemilik ini masih kosong.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="h-4"></div>
        </div>
    </DashboardLayout>
</template>
