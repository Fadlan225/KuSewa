<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    year: { type: Number, default: new Date().getFullYear() },
    availableYears: { type: Array, default: () => [new Date().getFullYear()] },
    monthlyRevenue: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({ paidTransactions: 0, revenue: 0 }) },
    transactions: { type: Object, default: () => ({ data: [], links: [] }) },
    methods: { type: Array, default: () => [] },
});
const methods = computed(() => props.methods || []);
const methodModal = ref(null);
const methodForm = reactive({ id: null, name: '', code: '', description: '', is_active: true });
const transactionRows = computed(() => props.transactions?.data || []);
const orderedMonthlyRevenue = computed(() => [...(props.monthlyRevenue || [])].sort((a, b) => Number(a.month) - Number(b.month)));
const maxRevenue = computed(() => Math.max(...orderedMonthlyRevenue.value.map(item => Number(item.revenue)), 1));
const goToPage = (page) => router.get(props.transactions?.links?.[page]?.url || page, {}, { preserveState: true, preserveScroll: true });
const formatRupiah = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
const changeYear = (event) => router.get(route('admin.payment-system'), { year: event.target.value }, { preserveState: true });
const paymentMethods = computed(() => methods.value);
const statusLabel = (status) => ({ paid: 'Berhasil', pending: 'Menunggu', failed: 'Gagal' }[status] || status);
const review = (item, action) => {
    if (!window.confirm(action === 'approve' ? 'Setujui pembayaran dan bukti transaksi ini?' : 'Tolak pembayaran ini?')) return;
    useForm({}).patch(route(`admin.payment-system.${action}`, item.id), { preserveScroll: true });
};
const openMethodModal = (method = null) => { Object.assign(methodForm, method ? { id: method.id, name: method.name, code: method.code, description: method.description || '', is_active: method.is_active } : { id: null, name: '', code: '', description: '', is_active: true }); methodModal.value = method ? 'edit' : 'create'; };
const closeMethodModal = () => { methodModal.value = null; };
const saveMethod = () => { const form = useForm({ name: methodForm.name, code: methodForm.code, description: methodForm.description, is_active: methodForm.is_active }); const options = { preserveScroll: true, onSuccess: closeMethodModal }; methodForm.id ? form.patch(route('admin.payment-system.methods.update', methodForm.id), options) : form.post(route('admin.payment-system.methods.store'), options); };
const deleteMethod = (method) => { if (window.confirm(`Hapus metode ${method.name}?`)) useForm({}).delete(route('admin.payment-system.methods.destroy', method.id), { preserveScroll: true }); };
const prioritizeMethods = () => { const ids = methods.value.map(method => method.id); if (ids.length) useForm({ ids }).post(route('admin.payment-system.methods.prioritize'), { preserveScroll: true }); };
</script>

<template>
    <Head title="Sistem Pembayaran - Admin Panel" />

    <DashboardLayout role="Admin" title="Sistem Pembayaran" description="Kontrol metode pembayaran dan tinjau transaksi terakhir.">
        <template #header-actions>
            <button @click="openMethodModal()" class="rounded-2xl bg-[#0A2540] px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-900 transition">Tambah Metode</button>
        </template>

            <div class="p-8 space-y-6 max-w-[1400px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Metode Aktif</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">{{ paymentMethods.filter(method => method.active).length }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Jumlah metode pembayaran aktif.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Transaksi Terbaru</p>
                        <p class="mt-3 text-3xl font-extrabold text-[#0A2540]">{{ summary.paidTransactions }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Pembayaran berhasil tahun {{ year }}.</p>
                    </div>
                    <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm">
                        <p class="text-[11px] font-semibold uppercase text-slate-400">Potensi Pendapatan</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ formatRupiah(summary.revenue) }}</p>
                        <p class="text-[11px] text-slate-500 mt-2">Omzet biaya layanan website tahun {{ year }}.</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white border border-slate-100 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div><h2 class="text-sm font-bold text-slate-900">Laporan Omzet Bulanan</h2><p class="text-[11px] text-slate-400">Total biaya layanan dari pembayaran owner yang berhasil.</p></div>
                        <select :value="year" @change="changeYear" class="rounded-xl border-slate-200 text-xs font-bold"><option v-for="item in availableYears" :key="item" :value="item">{{ item }}</option></select>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
                        <div v-for="month in orderedMonthlyRevenue" :key="month.month" class="rounded-xl bg-slate-50/80 border border-slate-100 px-3 py-2.5"><p class="text-[10px] font-medium text-slate-400">{{ month.label }}</p><p class="mt-1 text-sm font-extrabold text-[#0A2540]">{{ formatRupiah(month.revenue) }}</p><p class="text-[10px] text-slate-500">{{ month.transactions }} pembayaran</p></div>
                        <p v-if="!orderedMonthlyRevenue.length" class="col-span-full text-xs text-slate-400">Belum ada pembayaran berhasil pada tahun ini.</p>
                    </div>
                    <div v-if="orderedMonthlyRevenue.length" class="mt-5 h-44 flex items-end gap-2 border-b border-slate-200 px-2">
                        <div v-for="month in orderedMonthlyRevenue" :key="`chart-${month.month}`" class="flex-1 h-full flex flex-col justify-end items-center gap-1">
                            <span class="text-[9px] text-slate-500">{{ formatRupiah(month.revenue) }}</span>
                            <div class="w-full max-w-10 rounded-t-lg bg-[#FFC000] hover:bg-[#0A2540] transition" :style="{ height: `${Math.max((month.revenue / maxRevenue) * 75, 4)}%` }" :title="`${month.label}: ${formatRupiah(month.revenue)}`"></div>
                            <span class="text-[10px] text-slate-500">{{ month.label.slice(0, 3) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[28px] border border-slate-200/70 shadow-[0_12px_35px_rgba(15,23,42,0.06)] overflow-hidden">
                    <div class="px-6 py-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center"><i class="fa-solid fa-building-columns"></i></div>
                                <div><h2 class="text-sm font-extrabold text-slate-900">Metode Pembayaran</h2><p class="text-[11px] text-slate-400 mt-0.5">Kelola kanal pembayaran yang tersedia untuk owner.</p></div>
                            </div>
                        </div>
                        <button @click="prioritizeMethods" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-[11px] font-bold text-[#0A2540] hover:bg-slate-50 transition"><i class="fa-solid fa-arrow-down-wide-short"></i> Atur Prioritas</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Metode</th>
                                    <th class="py-4 px-4">Deskripsi</th>
                                    <th class="py-4 px-4">Status</th>
                                    <th class="py-4 px-5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="method in paymentMethods" :key="method.id" class="hover:bg-[#F8FAFC] transition-colors">
                                    <td class="py-4 px-5"><div class="flex items-center gap-3"><span class="w-9 h-9 rounded-xl bg-[#0A2540] text-white flex items-center justify-center text-[10px] font-black">{{ method.name.slice(0, 2).toUpperCase() }}</span><div><p class="font-extrabold text-slate-900">{{ method.name }}</p><p class="text-[10px] text-slate-400">{{ method.code }}</p></div></div></td>
                                    <td class="py-4 px-4 text-slate-500">{{ method.description || 'Belum ada deskripsi.' }}</td>
                                    <td class="py-4 px-4">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            method.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ method.active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button @click="openMethodModal(method)" class="rounded-xl border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-50 transition"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit</button>
                                        <button @click="deleteMethod(method)" class="ml-1 rounded-xl border border-rose-100 px-3 py-1.5 text-[11px] font-bold text-rose-600 hover:bg-rose-50 transition"><i class="fa-solid fa-trash-can mr-1"></i>Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Transaksi Terbaru</h2>
                            <p class="text-[11px] text-slate-400">Daftar ringkas transaksi pembayaran terakhir.</p>
                        </div>
                        <button class="text-[11px] font-semibold text-[#0A2540] hover:underline">Lihat Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                    <th class="py-4 px-5">Transaksi</th>
                                    <th class="py-4 px-4">Jumlah</th>
                                    <th class="py-4 px-4">Biaya Layanan</th>
                                    <th class="py-4 px-4">Metode</th>
                                    <th class="py-4 px-4">Tanggal</th>
                                    <th class="py-4 px-5">Status</th>
                                    <th class="py-4 px-5">Validasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in transactionRows" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-4 px-5 font-semibold text-slate-900">{{ item.label }}</td>
                                    <td class="py-4 px-4 text-slate-700">{{ formatRupiah(item.amount) }}</td>
                                    <td class="py-4 px-4 text-emerald-700 font-semibold">{{ formatRupiah(item.service_fee) }}</td>
                                    <td class="py-4 px-4 text-slate-600">{{ item.method }}</td>
                                    <td class="py-4 px-4 text-slate-500">{{ item.date }}</td>
                                    <td class="py-4 px-5">
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold',
                                            item.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : item.status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                                        ]">
                                            {{ statusLabel(item.status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <a v-if="item.proof" :href="item.proof" target="_blank" class="mr-2 text-[#0A2540] font-bold hover:underline">Bukti</a>
                                        <template v-if="item.status === 'pending'">
                                            <button @click="review(item, 'approve')" class="mr-1 rounded-lg bg-emerald-600 px-2 py-1 text-[10px] font-bold text-white">Setujui</button>
                                            <button @click="review(item, 'reject')" class="rounded-lg bg-rose-600 px-2 py-1 text-[10px] font-bold text-white">Tolak</button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <span class="text-slate-400">{{ transactions.from || 0 }}–{{ transactions.to || 0 }} dari {{ transactions.total || 0 }} transaksi</span>
                        <nav class="flex items-center gap-1" aria-label="Pagination">
                            <template v-for="link in (transactions.links || [])" :key="link.label">
                                <button v-if="link.url || link.active" :disabled="!link.url || link.active" @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })" v-html="link.label" class="min-w-8 h-8 px-2 rounded-lg border text-[11px] font-semibold transition" :class="link.active ? 'bg-[#0A2540] text-white border-[#0A2540]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0A2540] hover:text-[#0A2540]'" />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
    </DashboardLayout>
    <div v-if="methodModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeMethodModal">
        <form class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl" @submit.prevent="saveMethod">
            <div class="flex items-start justify-between"><div><h3 class="text-lg font-extrabold text-slate-900">{{ methodModal === 'edit' ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran' }}</h3><p class="mt-1 text-xs text-slate-400">Lengkapi informasi metode pembayaran.</p></div><button type="button" @click="closeMethodModal" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button></div>
            <div class="mt-5 space-y-4"><label class="block text-xs font-bold text-slate-600">Nama metode<input v-model="methodForm.name" required class="mt-1.5 w-full rounded-xl border-slate-200 text-sm" placeholder="Contoh: BCA" /></label><label class="block text-xs font-bold text-slate-600">Kode metode<input v-model="methodForm.code" required class="mt-1.5 w-full rounded-xl border-slate-200 text-sm" placeholder="Contoh: bca" /></label><label class="block text-xs font-bold text-slate-600">Deskripsi<textarea v-model="methodForm.description" rows="2" class="mt-1.5 w-full rounded-xl border-slate-200 text-sm" placeholder="Deskripsi singkat"></textarea></label><label class="flex items-center gap-2 text-xs font-bold text-slate-600"><input v-model="methodForm.is_active" type="checkbox" class="rounded border-slate-300 text-[#0A2540]" /> Metode aktif</label></div>
            <div class="mt-6 flex justify-end gap-2"><button type="button" @click="closeMethodModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600">Batal</button><button class="rounded-xl bg-[#0A2540] px-4 py-2 text-xs font-bold text-white hover:bg-[#14385f]">Simpan</button></div>
        </form>
    </div>
</template>
