<script setup>
defineProps({
    price: {
        type: Number,
        default: 0
    },
    nightsCount: {
        type: Number,
        default: 0
    },
    formattedDateRange: {
        type: String,
        default: ''
    },
    periodLabel: {
        type: String,
        default: 'opsi'
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const formatRupiah = (value) => {
    if (!value) return 'Hubungi Pemilik';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-3 px-5 z-40 md:hidden flex justify-between items-center shadow-[0_-4px_10px_-2px_rgba(0,0,0,0.05)]">
        <div class="flex flex-col">
            <span class="text-lg font-extrabold text-[#0A2540] underline decoration-[#0A2540] underline-offset-2">
                {{ formatRupiah(price) }}
            </span>
            <span class="text-xs text-gray-500 font-medium mt-0.5">
                {{ nightsCount ? `untuk ${nightsCount} malam · ${formattedDateRange}` : `per ${periodLabel}` }}
            </span>
        </div>
        <button 
            @click="$emit('submit')" 
            :disabled="disabled" 
            class="bg-primary hover:bg-primary text-white font-bold py-3 px-8 rounded-xl shadow-md transition-colors text-sm tracking-wide disabled:opacity-50 disabled:cursor-not-allowed">
            Pesan
        </button>
    </div>
</template>
