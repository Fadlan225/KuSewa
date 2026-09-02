<script setup>
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';

const props = defineProps({
    form: Object,
    banks: Array,
    ktpName: String,
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#0A2540]">Lengkapi data diri Anda</h2>
            <p class="text-sm text-slate-500 mt-1">Pastikan data benar agar uang pembayaran sewa kos dapat ditransfer dengan lancar.</p>
        </div>

        <div class="space-y-6 bg-white p-6 rounded-lg border border-slate-200">
            <!-- Nama KTP -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">Nama Lengkap Sesuai KTP</label>
                <div class="w-full bg-slate-50 text-slate-700 text-sm border border-slate-300 rounded-md px-4 py-3 cursor-not-allowed">
                    {{ ktpName }}
                </div>
            </div>

            <!-- Border separator -->
            <div class="border-t border-slate-100"></div>

            <!-- Bank -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">Bank <span class="text-rose-500">*</span></label>
                <SearchableSelect
                    v-model="form.bank_code"
                    :options="banks"
                    placeholder="Cari atau pilih bank"
                />
            </div>

            <!-- Nomor Rekening -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">Nomor Rekening <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    v-model="form.account_number"
                    placeholder="Contoh: 1234567890"
                    maxlength="25"
                    class="w-full text-sm border border-slate-300 focus:border-transparent focus:ring-2 focus:ring-[#FFC000] focus:outline-none rounded-md px-4 py-2.5 transition"
                    @input="form.account_number = form.account_number.replace(/\D/g, '')"
                />
            </div>

            <!-- Nama Pemilik Rekening -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">Nama Pemilik Rekening <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    v-model="form.account_holder"
                    placeholder="Contoh: Budi Santoso"
                    maxlength="100"
                    class="w-full text-sm border border-slate-300 focus:border-transparent focus:ring-2 focus:ring-[#FFC000] focus:outline-none rounded-md px-4 py-2.5 transition"
                />
            </div>

            <div class="border-t border-slate-100 pt-6">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.terms_agreed"
                            class="w-5 h-5 border border-slate-300 rounded text-[#10B981] focus:ring-[#10B981] transition peer cursor-pointer"
                        />
                    </div>
                    <span class="text-sm text-slate-600 leading-tight pt-0.5">
                        Saya menyetujui <a href="#" target="_blank" class="text-slate-800 font-semibold underline hover:text-[#10B981] transition">Syarat dan Ketentuan</a> fitur Booking Langsung Mamikos
                    </span>
                </label>
            </div>
        </div>
    </div>
</template>
