<template>
  <div class="relative w-full" ref="dropdownRef">
    <!-- Trigger input -->
    <div class="relative">
      <input
        type="text"
        :value="displayValue"
        @input="onSearch"
        @focus="openDropdown"
        :placeholder="placeholder"
        :disabled="disabled"
        class="block w-full appearance-none bg-white border border-slate-200 rounded-xl py-2.5 pl-3 pr-10 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-[#0A2540] focus:border-[#0A2540] transition-colors shadow-sm placeholder:text-slate-400 placeholder:font-normal"
        :class="{ 'opacity-70 cursor-not-allowed': disabled }"
      />
      
      <!-- Chevron / Loading icon -->
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <div class="w-6 h-6 rounded-full flex items-center justify-center transition-colors bg-gray-100 text-gray-400">
          <ChevronDown class="text-[10px]" />
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
          @click.stop="selectOption(option)"
          class="px-4 py-2.5 text-sm cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between"
          :class="{'bg-[#FFF9E6] text-[#FFC000] font-medium': isSelected(option), 'text-[#0A2540]': !isSelected(option)}"
        >
          {{ option.name }}
          <Check v-if="isSelected(option)" class="text-[#FFC000] text-xs" />
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ChevronDown, Check } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, Array],
    default: null
  },
  options: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Pilih'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const dropdownRef = ref(null);
const isOpen = ref(false);
const searchQuery = ref('');

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
  if (!searchQuery.value) return props.options;
  const q = searchQuery.value.toLowerCase();
  return props.options.filter(opt => opt.name.toLowerCase().includes(q));
});

// Logika Tampilan Input
const displayValue = computed(() => {
  if (isOpen.value) {
    return searchQuery.value; // Saat dropdown terbuka, tampilkan teks pencarian
  }
  
  if (props.multiple) {
      if (Array.isArray(props.modelValue) && props.modelValue.length > 0) {
          // Hanya hitung item yang ada dalam options dropdown ini
          const selectedInThisDropdown = props.modelValue.filter(val => 
              props.options.some(opt => opt.code === val)
          );
          if (selectedInThisDropdown.length > 0) {
              return `${selectedInThisDropdown.length} Dipilih`;
          }
      }
      return '';
  }

  if (props.modelValue && props.options.length > 0) {
    const selected = props.options.find(opt => opt.code == props.modelValue);
    return selected ? selected.name : '';
  }
  
  return '';
});

const isSelected = (option) => {
    if (props.multiple) {
        return Array.isArray(props.modelValue) && props.modelValue.includes(option.code);
    }
    return props.modelValue == option.code;
};

// Interaksi Dropdown
const openDropdown = () => {
  if (props.disabled) return;
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
  if (props.multiple) {
      let val = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
      const idx = val.indexOf(option.code);
      if (idx > -1) {
          val.splice(idx, 1);
      } else {
          val.push(option.code);
      }
      emit('update:modelValue', val);
      emit('change', option);
      // biarkan dropdown tetap terbuka saat multiple select
  } else {
      emit('update:modelValue', option.code);
      emit('change', option);
      closeDropdown();
  }
};

</script>
