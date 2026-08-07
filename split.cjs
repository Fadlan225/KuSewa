const fs = require('fs');
const path = require('path');

const srcFile = path.join(__dirname, 'resources/js/Pages/owner/Asset/create.vue');
const destDir = path.join(__dirname, 'resources/js/Pages/owner/Asset/Create');

const content = fs.readFileSync(srcFile, 'utf8');

const extractSection = (tag, content) => {
    const start = content.indexOf(`<${tag}`);
    const end = content.lastIndexOf(`</${tag}>`) + `</${tag}>`.length;
    return content.substring(start, end);
};

const scriptContent = extractSection('script', content);
const templateContent = extractSection('template', content);

// Split Template
const step1Start = templateContent.indexOf('<!-- STEP 1: INFORMASI UTAMA -->');
const step2Start = templateContent.indexOf('<!-- STEP 2: LOKASI & FASILITAS -->');
const step3Start = templateContent.indexOf('<!-- STEP 3: HARGA & FOTO -->');
const step3End = templateContent.indexOf('<!-- POP-UP SUCCESS MODAL -->');

const step1Html = templateContent.substring(step1Start, step2Start);
const step2Html = templateContent.substring(step2Start, step3Start);
const step3Html = templateContent.substring(step3Start, step3End);

// Find the parent div of step 1
const tplStart = templateContent.substring(0, step1Start);
const tplEnd = templateContent.substring(step3End);

// Now write Index.vue
let indexScript = scriptContent;
// Index needs to import the steps
indexScript = indexScript.replace("import 'leaflet/dist/leaflet.css';", "import 'leaflet/dist/leaflet.css';\nimport Step1 from './Step1.vue';\nimport Step2 from './Step2.vue';\nimport Step3 from './Step3.vue';");

let indexHtml = tplStart + `
                    <!-- STEP 1 -->
                    <Step1 v-show="currentStep === 1" :form="form" />

                    <!-- STEP 2 -->
                    <Step2 v-show="currentStep === 2" :form="form" :currentStep="currentStep" />

                    <!-- STEP 3 -->
                    <Step3 v-show="currentStep === 3" :form="form" />
` + tplEnd;

fs.writeFileSync(path.join(destDir, 'Index.vue'), indexScript + '\n\n' + indexHtml);

// Build Step 1
let step1Script = `<script setup>
import { computed, watch, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({ form: Object });
const form = props.form;

${scriptContent.substring(
    scriptContent.indexOf('// --- KELOMPOK KATEGORI'),
    scriptContent.indexOf('// --- STATE LOKASI LENGKAP')
)}

${scriptContent.substring(
    scriptContent.indexOf('// True jika jenis properti'),
    scriptContent.indexOf('// Navigation & Modal State')
)}

${scriptContent.substring(
    scriptContent.indexOf('// --- LOGIKA TIPE KAMAR'),
    scriptContent.indexOf('// --- LOGIKA NESTED DROPDOWN')
)}

const fasilitasKamarItems = [
    'Wi-Fi / Internet', 'AC (Pendingin)', 'Kamar Mandi Dalam', 'Furnished Lengkap',
    'Kasur & Lemari', 'Meja & Kursi Belajar', 'Kulkas', 'Water Heater / Air Panas',
    'Dispenser', 'Setrika & Meja Setrika', 'Jemuran Pakaian', 'Balkon / Rooftop',
    'Ruang Tamu Bersama', 'TV Kabel / Smart TV', 'Kitchen Set', 'Kompor / Kitchen Gas',
    'Dapur Bersama / Pribadi', 'Ruang Makan', 'Gudang Penyimpanan',
    'Carport / Garasi Mobil', 'Taman / Halaman'
];

onMounted(() => {
    window.addEventListener('click', handleClickOutsideFasilitasKamar);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutsideFasilitasKamar);
});

const handleClickOutsideFasilitasKamar = (e) => {
    if (!e.target.closest('.kamar-fasilitas-dropdown')) {
        fasilitasKamarDropdownOpen.value = null;
    }
};

</script>`;

fs.writeFileSync(path.join(destDir, 'Step1.vue'), step1Script + '\n\n<template>\n<div class="space-y-4">\n' + step1Html.replace('<div v-if="currentStep === 1" class="space-y-4">', '') + '\n</template>');

// Build Step 2
let step2Script = `<script setup>
import { computed, watch, ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import L from 'leaflet';

const props = defineProps({ form: Object, currentStep: Number });
const form = props.form;

${scriptContent.substring(
    scriptContent.indexOf('// --- STATE LOKASI LENGKAP'),
    scriptContent.indexOf('// True jika jenis properti')
)}

${scriptContent.substring(
    scriptContent.indexOf('// --- LOGIKA PETA LOKASI'),
    scriptContent.indexOf('// --- LOGIKA TIPE KAMAR')
)}

${scriptContent.substring(
    scriptContent.indexOf('// --- LOGIKA NESTED DROPDOWN'),
    scriptContent.indexOf('// --- LOGIKA FOTO KATEGORI')
)}
</script>`;

// replace watch(currentStep with watch(() => props.currentStep
step2Script = step2Script.replace('watch(currentStep,', 'watch(() => props.currentStep,');

fs.writeFileSync(path.join(destDir, 'Step2.vue'), step2Script + '\n\n<template>\n<div class="space-y-4">\n' + step2Html.replace('<div v-if="currentStep === 2" class="space-y-4">', '') + '\n</template>');

// Build Step 3
let step3Script = `<script setup>
import { computed, watch, ref } from 'vue';

const props = defineProps({ form: Object });
const form = props.form;

${scriptContent.substring(
    scriptContent.indexOf('// --- LOGIKA FOTO KATEGORI'),
    scriptContent.indexOf('// --- NAVIGASI STEPPER')
)}
</script>`;

fs.writeFileSync(path.join(destDir, 'Step3.vue'), step3Script + '\n\n<template>\n<div class="space-y-4">\n' + step3Html.replace('<div v-if="currentStep === 3" class="space-y-4">', '') + '\n</template>');
