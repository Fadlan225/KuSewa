<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { ChevronDown, Check } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: null,
    },
    categories: {
        type: Array,
        required: true,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Cari atau pilih...'
    },
    groupItemsKey: {
        type: String,
        default: 'types'
    },
    itemValueKey: {
        type: String,
        default: 'id'
    },
    itemLabelKey: {
        type: String,
        default: 'name'
    }
});

const emit = defineEmits(['update:modelValue', 'select']);

const searchQuery = ref('');
const isOpen = ref(false);
const dropdownRef = ref(null);

// Menemukan label dari item yang dipilih untuk ditampilkan di input
const getSelectedLabel = () => {
    if (props.modelValue && props.categories) {
        for (const cat of props.categories) {
            const items = cat[props.groupItemsKey] || [];
            const item = items.find(t => t[props.itemValueKey] === props.modelValue);
            if (item) {
                return item[props.itemLabelKey];
            }
        }
    }
    return '';
};

// Mengubah isi pencarian ketika value berubah dari luar
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        searchQuery.value = getSelectedLabel();
    } else {
        searchQuery.value = '';
    }
}, { immediate: true });

const filteredCategories = computed(() => {
    if (!props.categories) return [];
    if (!searchQuery.value) return props.categories;
    
    // Jika searchQuery sama dengan label yang dipilih, jangan filter (tampilkan semua saat dropdown dibuka)
    const selectedLabel = getSelectedLabel();
    if (searchQuery.value === selectedLabel) {
        return props.categories;
    }

    const q = searchQuery.value.toLowerCase();
    return props.categories.map(cat => {
        return {
            ...cat,
            [props.groupItemsKey]: (cat[props.groupItemsKey] || []).filter(t => 
                String(t[props.itemLabelKey]).toLowerCase().includes(q)
            )
        };
    }).filter(cat => cat[props.groupItemsKey].length > 0);
});

const handleSelect = (categoryId, item) => {
    isOpen.value = false;
    searchQuery.value = item[props.itemLabelKey];
    emit('update:modelValue', item[props.itemValueKey]);
    emit('select', categoryId, item[props.itemValueKey]);
};

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
        searchQuery.value = getSelectedLabel();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative w-full" ref="dropdownRef">
        <div class="relative">
            <input type="text"
                :value="searchQuery"
                @input="searchQuery = $event.target.value; isOpen = true"
                @focus="isOpen = true; searchQuery = ''"
                :placeholder="placeholder"
                class="w-full pl-4 pr-10 py-3 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#FFC000] text-sm font-semibold transition cursor-pointer"
            />
            <ChevronDown class="absolute right-3 top-3.5 text-slate-400 w-5 h-5 pointer-events-none" />
        </div>

        <div v-if="isOpen" class="absolute z-[100] w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl max-h-80 overflow-y-auto overflow-x-hidden">
            <template v-for="category in filteredCategories" :key="category.id">
                <div class="px-4 py-2 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider sticky top-0 shadow-sm border-b border-slate-100">
                    {{ category.name }}
                </div>
                <div
                    v-for="item in category[groupItemsKey]"
                    :key="item[itemValueKey]"
                    @click="handleSelect(category.id, item)"
                    class="px-4 py-3 text-sm cursor-pointer transition-colors flex justify-between items-center"
                    :class="modelValue === item[itemValueKey] ? 'bg-[#FFF9E6] text-[#0A2540] font-bold border-l-4 border-[#FFC000]' : 'text-slate-600 hover:bg-slate-50 border-l-4 border-transparent'"
                >
                    {{ item[itemLabelKey] }}
                    <Check v-if="modelValue === item[itemValueKey]" class="text-[#FFC000] w-4 h-4" />
                </div>
            </template>
            <div v-if="filteredCategories.length === 0" class="p-4 text-sm text-slate-500 text-center">
                Data tidak ditemukan.
            </div>
        </div>
    </div>
</template>
