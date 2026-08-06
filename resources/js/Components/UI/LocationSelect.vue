<template>
  <div class="relative w-full" ref="dropdownRef">
    <!-- Trigger input -->
    <div class="relative">
      <input
        type="text"
        :value="displayValue"
        @input="onSearch"
        @focus="openDropdown"
        :placeholder="loading ? 'Memuat data...' : placeholder"
        :disabled="disabled || loading"
        class="block w-full appearance-none bg-white border border-gray-300 rounded-xl py-3 pl-4 pr-10 text-[15px] font-semibold text-[#0A2540] focus:outline-none focus:ring-2 focus:ring-[#FFC000] focus:border-[#FFC000] transition-colors shadow-sm placeholder:text-gray-400 placeholder:font-normal"
        :class="{ 'opacity-70 cursor-not-allowed': disabled || loading }"
      />
      
      <!-- Chevron / Loading icon -->
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <div class="w-6 h-6 rounded-full flex items-center justify-center transition-colors bg-gray-100 text-gray-400">
          <i v-if="loading" class="fa-solid fa-spinner fa-spin text-[10px]"></i>
          <i v-else class="fa-solid fa-chevron-down text-[10px]"></i>
        </div>
      </div>
    </div>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      class="absolute z-[100] w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto"
    >
      <div v-if="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500">
        Tidak ditemukan data kota yang cocok.
      </div>
      
      <ul v-else class="py-1">
        <li
          v-for="option in filteredOptions"
          :key="option.code"
          @click="selectOption(option)"
          class="px-4 py-2.5 text-sm cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between"
          :class="{'bg-[#FFF9E6] text-[#FFC000] font-medium': modelValue === option.code, 'text-[#0A2540]': modelValue !== option.code}"
        >
          {{ option.name }}
          <i v-if="modelValue === option.code" class="fa-solid fa-check text-[#FFC000] text-xs"></i>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: String,
    default: null
  },
  endpoint: {
    type: String,
    required: true
  },
  placeholder: {
    type: String,
    default: 'Pilih Kota'
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const dropdownRef = ref(null);
const isOpen = ref(false);
const loading = ref(true);
const options = ref([]);
const searchQuery = ref('');

// Ambil data saat komponen di-mount
onMounted(async () => {
  try {
    const response = await axios.get(props.endpoint);
    options.value = response.data.data || [];
  } catch (error) {
    console.error(`Gagal memuat data dari ${props.endpoint}:`, error);
  } finally {
    loading.value = false;
  }
});

// Menangani klik di luar dropdown untuk menutup menu
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown();
  }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

// Logika Searchable
const filteredOptions = computed(() => {
  if (!searchQuery.value) return options.value;
  const q = searchQuery.value.toLowerCase();
  return options.value.filter(opt => opt.name.toLowerCase().includes(q));
});

// Logika Tampilan Input
const displayValue = computed(() => {
  if (isOpen.value) {
    return searchQuery.value; // Saat dropdown terbuka, tampilkan teks pencarian
  }
  
  if (props.modelValue && options.value.length > 0) {
    const selected = options.value.find(opt => opt.code === props.modelValue);
    return selected ? selected.name : '';
  }
  
  return '';
});

// Interaksi Dropdown
const openDropdown = () => {
  if (props.disabled || loading.value) return;
  searchQuery.value = ''; // Reset pencarian saat dibuka
  isOpen.value = true;
};

const closeDropdown = () => {
  isOpen.value = false;
  searchQuery.value = ''; // Kembalikan teks pencarian
};

const onSearch = (e) => {
  searchQuery.value = e.target.value;
  if (!isOpen.value) isOpen.value = true;
};

const selectOption = (option) => {
  emit('update:modelValue', option.code);
  emit('change', option);
  closeDropdown();
};

</script>
