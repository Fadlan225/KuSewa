<script setup>
import { MessageSquare } from 'lucide-vue-next';
defineProps({
    price: {
        type: Number,
        default: 0
    },
    durationCount: {
        type: Number,
        default: 0
    },
    durationLabel: {
        type: String,
        default: 'malam'
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
    },
    buttonText: {
        type: String,
        default: 'Pesan'
    },
    hideLeftContent: {
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
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-40 md:hidden flex flex-col gap-3 shadow-[0_-4px_10px_-2px_rgba(0,0,0,0.05)]">

        <!-- Top Row: Price & Period -->
        <div v-if="!hideLeftContent" class="flex justify-between items-end w-full">
            <div class="flex items-baseline gap-1 truncate w-full">
                <span class="text-xl font-extrabold text-[#0A2540]">
                    {{ formatRupiah(price) }}
                </span>
                <span class="text-sm text-gray-600 font-medium">
                    <template v-if="formattedDateRange && durationCount > 0">
                        {{ durationCount }} {{ durationLabel }}
                    </template>
                    <template v-else-if="durationCount > 0">
                        / {{ durationCount }} {{ durationLabel }}
                    </template>
                    <template v-else>
                        /{{ periodLabel }}
                    </template>
                </span>
            </div>
        </div>

        <!-- Bottom Row: Buttons -->
        <div class="flex justify-between items-center w-full gap-3">
            <slot name="left-content">
                <button @click="$emit('tanya-pemilik')" class="flex-1 flex items-center justify-center gap-2 border-2 border-[#FFC000] text-primary hover:bg-[#FFC000]/20 font-bold py-2.5 rounded-xl transition-colors text-sm">
                    <MessageSquare class="w-4 h-4" />
                    Tanya Pemilik
                </button>
            </slot>

            <slot name="right-content">
                <button
                    @click="$emit('submit')"
                    :disabled="disabled"
                    class="flex-1 bg-[#FFC000] hover:bg-[#e6ad00] text-[#0A2540] font-bold py-3 rounded-xl shadow-sm transition-colors text-sm tracking-wide disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ buttonText }}
                </button>
            </slot>
        </div>
    </div>
</template>
