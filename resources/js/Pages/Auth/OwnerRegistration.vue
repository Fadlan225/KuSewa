<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/UI/DetailNavbar.vue';
import DetailBottomBar from '@/Components/UI/DetailBottomBar.vue';
import SearchableSelect from '@/Components/UI/SearchableSelect.vue';

const props = defineProps({
    initialUser: Object,
    initialProfile: Object,
});

const currentStep = ref(1);

const formStep1 = useForm({
    name: props.initialUser?.name || '',
    email: props.initialUser?.email || '',
    phone: props.initialUser?.phone || '',
    gender: props.initialUser?.gender || '',
    place_of_birth_code: props.initialUser?.place_of_birth_code || '',
    date_of_birth: props.initialUser?.date_of_birth || '',
    national_id: props.initialProfile?.national_id || '',
});

const formStep2 = useForm({
    country: 'Indonesia',
    province_code: props.initialProfile?.province_code || '',
    city_code: props.initialProfile?.city_code || '',
    district_code: props.initialProfile?.district_code || '',
    village_code: props.initialProfile?.village_code || '',
    postal_code: props.initialProfile?.postal_code || '',
    address: props.initialProfile?.address || '',
});

const formStep3 = useForm({
    ktp_photo: null,
});

const ktpPreview = ref(null);

const provinces = ref([]);
const cities = ref([]);
const districts = ref([]);
const villages = ref([]);
const allCities = ref([]);

// Fetch Initial Data for Locations
const fetchAllCities = async () => {
    try {
        const res = await axios.get('/api/cities');
        allCities.value = res.data.data;
    } catch (e) {
        console.error("Failed to fetch all cities");
    }
};
const fetchProvinces = async () => {
    try {
        const res = await axios.get('/api/provinces');
        provinces.value = res.data.data;
    } catch (e) {
        console.error("Failed to fetch provinces");
    }
};

const fetchCities = async (provinceCode) => {
    try {
        const res = await axios.get(`/api/cities?province_code=${provinceCode}`);
        cities.value = res.data.data;
    } catch (e) {
        console.error("Failed to fetch cities");
    }
};

const fetchDistricts = async (cityCode) => {
    try {
        const res = await axios.get(`/api/districts?city_code=${cityCode}`);
        districts.value = res.data.data;
    } catch (e) {
        console.error("Failed to fetch districts");
    }
};

const fetchVillages = async (districtCode) => {
    try {
        const res = await axios.get(`/api/villages?district_code=${districtCode}`);
        villages.value = res.data.data;
    } catch (e) {
        console.error("Failed to fetch villages");
    }
};

// Cascading watchers
watch(() => formStep2.province_code, (newVal, oldVal) => {
    if (newVal) fetchCities(newVal);
    if (oldVal && newVal !== oldVal) {
        formStep2.city_code = '';
        formStep2.district_code = '';
        formStep2.village_code = '';
        cities.value = [];
        districts.value = [];
        villages.value = [];
    }
});

watch(() => formStep2.city_code, (newVal, oldVal) => {
    if (newVal) fetchDistricts(newVal);
    if (oldVal && newVal !== oldVal) {
        formStep2.district_code = '';
        formStep2.village_code = '';
        districts.value = [];
        villages.value = [];
    }
});

watch(() => formStep2.district_code, (newVal, oldVal) => {
    if (newVal) fetchVillages(newVal);
    if (oldVal && newVal !== oldVal) {
        formStep2.village_code = '';
        villages.value = [];
    }
});

onMounted(() => {
    fetchAllCities();
    fetchProvinces();
    if (formStep2.province_code) fetchCities(formStep2.province_code);
    if (formStep2.city_code) fetchDistricts(formStep2.city_code);
    if (formStep2.district_code) fetchVillages(formStep2.district_code);
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        formStep3.ktp_photo = file;
        ktpPreview.value = URL.createObjectURL(file);
    }
};

const nextStep = () => {
    if (currentStep.value === 1) {
        formStep1.post(route('owner.register.step1'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                currentStep.value = 2;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    } else if (currentStep.value === 2) {
        formStep2.post(route('owner.register.step2'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                currentStep.value = 3;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        currentStep.value--;
    }
};

const submit = () => {
    formStep3.post(route('owner.register.step3'));
};

const steps = [
    {
        number: 1,
        title: 'Data Diri',
        subtitle: 'Informasi Pribadi',
        icon: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>`
    },
    {
        number: 2,
        title: 'Alamat',
        subtitle: 'Alamat Domisili',
        icon: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`
    },
    {
        number: 3,
        title: 'Verifikasi',
        subtitle: 'Upload KTP',
        icon: `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>`
    }
];

// Combine errors to easily display them
const currentErrors = () => {
    if (currentStep.value === 1) return formStep1.errors;
    if (currentStep.value === 2) return formStep2.errors;
    if (currentStep.value === 3) return formStep3.errors;
    return {};
};
</script>

<template>
    <Head title="Pendaftaran Owner - kusewa.id" />

    <AppLayout hideNavbar hideBottombar>
        <DetailNavbar :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" forceBackUrl backUrl="/" />

        <!-- Container Utama -->
        <div class="min-h-screen bg-background font-sans text-text pb-32">

            <div class="max-w-6xl mx-auto px-6 lg:px-8 py-10 lg:py-12">
                <div class="flex flex-col lg:flex-row gap-12 lg:gap-16 items-start">

                    <!-- SIDEBAR NAV (Sticky) -->
                    <div class="w-full lg:w-[260px] shrink-0 lg:sticky lg:top-32 hidden md:block">
                        <div class="flex flex-col relative before:absolute before:left-5 before:top-4 before:bottom-4 before:w-[2px] before:bg-muted/10 before:-z-10">

                            <div v-for="(step, index) in steps" :key="step.number" class="flex items-start gap-4 mb-8 last:mb-0 relative z-10 transition-colors">

                                <!-- Icon Indicator -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300 ring-4 ring-background"
                                     :class="[
                                        currentStep === step.number ? 'bg-primary text-secondary shadow-md shadow-primary/20' :
                                        currentStep > step.number ? 'bg-secondary text-white' : 'bg-white border-2 border-muted/30 text-muted'
                                     ]">
                                    <svg v-if="currentStep > step.number" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span v-else v-html="step.icon"></span>
                                </div>

                                <!-- Text Content -->
                                <div class="mt-0.5">
                                    <h3 class="text-[15px] transition-colors duration-300"
                                        :class="currentStep === step.number ? 'font-bold text-secondary' : 'font-semibold text-secondary/80'">
                                        {{ step.title }}
                                    </h3>
                                    <p class="text-[13px] mt-0.5 transition-colors duration-300"
                                       :class="currentStep === step.number ? 'text-secondary/70' : 'text-muted'">
                                        {{ step.subtitle }}
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Horizontal Stepper for Mobile -->
                    <div class="w-full md:hidden mb-6 bg-white p-4 rounded-2xl shadow-sm border border-muted/10 flex justify-between items-center relative">
                        <div class="absolute top-1/2 left-8 right-8 h-[2px] bg-muted/10 -translate-y-1/2 -z-0"></div>
                        <div v-for="step in steps" :key="step.number" class="relative z-10 bg-white px-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm ring-4 ring-white"
                                 :class="currentStep === step.number ? 'bg-primary text-secondary' : currentStep > step.number ? 'bg-secondary text-white' : 'bg-background border-2 border-muted/20 text-muted'">
                                <svg v-if="currentStep > step.number" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span v-else>{{ step.number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENT AREA -->
                    <div class="flex-grow w-full max-w-[720px]">

                        <!-- TAHAP 1: DATA DIRI -->
                        <div v-show="currentStep === 1" class="space-y-12 animate-fade-in">

                            <section class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-muted/10 p-8 sm:p-10">
                                <div class="mb-8">
                                    <h2 class="text-[22px] font-semibold text-secondary">Informasi Pribadi</h2>
                                    <p class="text-[14px] text-muted mt-1.5 leading-relaxed">Lengkapi identitas pribadi Anda sesuai dengan kartu identitas (KTP) yang sah.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                                    <!-- Nama Lengkap -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="formStep1.name" placeholder="Sesuai KTP" class="w-full h-[48px] border border-muted/20 rounded-[12px] px-4 text-[14px] text-text placeholder-muted focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white" :class="{ 'border-red-500': formStep1.errors.name }" />
                                        <p v-if="formStep1.errors.name" class="text-red-500 text-xs mt-1">{{ formStep1.errors.name }}</p>
                                    </div>

                                    <!-- NIK -->
                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">NIK <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="formStep1.national_id" maxlength="16" placeholder="16 Digit NIK" class="w-full h-[48px] border border-muted/20 rounded-[12px] px-4 text-[14px] text-text placeholder-muted focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white" :class="{ 'border-red-500': formStep1.errors.national_id }" />
                                        <div class="flex justify-between items-center mt-1.5">
                                            <p v-if="formStep1.errors.national_id" class="text-red-500 text-xs">{{ formStep1.errors.national_id }}</p>
                                            <p class="text-[13px] text-muted ml-auto">{{ formStep1.national_id.length }}/16 karakter</p>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Alamat Email <span class="text-red-500">*</span></label>
                                        <input type="email" v-model="formStep1.email" placeholder="contoh@email.com" class="w-full h-[48px] border border-muted/20 rounded-[12px] px-4 text-[14px] text-text placeholder-muted focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white" :class="{ 'border-red-500': formStep1.errors.email }" />
                                        <p v-if="formStep1.errors.email" class="text-red-500 text-xs mt-1">{{ formStep1.errors.email }}</p>
                                    </div>

                                    <!-- No Telepon -->
                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="formStep1.phone" placeholder="0812xxxxxxxx" class="w-full h-[48px] border border-muted/20 rounded-[12px] px-4 text-[14px] text-text placeholder-muted focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white" :class="{ 'border-red-500': formStep1.errors.phone }" />
                                        <p v-if="formStep1.errors.phone" class="text-red-500 text-xs mt-1">{{ formStep1.errors.phone }}</p>
                                    </div>

                                    <!-- Jenis Kelamin -->
                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <select v-model="formStep1.gender" class="w-full h-[48px] appearance-none border border-muted/20 rounded-[12px] px-4 text-[14px] text-text focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white cursor-pointer" :class="{ 'border-red-500': formStep1.errors.gender }">
                                                <option value="" disabled selected>Pilih salah satu</option>
                                                <option value="male">Laki-laki</option>
                                                <option value="female">Perempuan</option>
                                            </select>
                                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                        <p v-if="formStep1.errors.gender" class="text-red-500 text-xs mt-1">{{ formStep1.errors.gender }}</p>
                                    </div>

                                    <!-- Tempat Lahir -->
                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                                        <SearchableSelect
                                            v-model="formStep1.place_of_birth_code"
                                            :options="allCities"
                                            placeholder="Pilih kota lahir"
                                            :error="!!formStep1.errors.place_of_birth_code"
                                        />
                                        <p v-if="formStep1.errors.place_of_birth_code" class="text-red-500 text-xs mt-1">{{ formStep1.errors.place_of_birth_code }}</p>
                                    </div>

                                    <!-- Tanggal Lahir -->
                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="date" v-model="formStep1.date_of_birth" class="w-full h-[48px] appearance-none border border-muted/20 rounded-[12px] px-4 pr-10 text-[14px] text-text focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white" :class="{ 'border-red-500': formStep1.errors.date_of_birth }" />
                                        </div>
                                        <p v-if="formStep1.errors.date_of_birth" class="text-red-500 text-xs mt-1">{{ formStep1.errors.date_of_birth }}</p>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- TAHAP 2: ALAMAT -->
                        <div v-show="currentStep === 2" class="space-y-12 animate-fade-in">
                            <section class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-muted/10 p-8 sm:p-10">
                                <div class="mb-8">
                                    <h2 class="text-[22px] font-semibold text-secondary">Alamat Domisili</h2>
                                    <p class="text-[14px] text-muted mt-1.5 leading-relaxed">Masukkan alamat domisili tempat Anda tinggal saat ini.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                                    <!-- Provinsi -->
                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Provinsi <span class="text-red-500">*</span></label>
                                        <SearchableSelect
                                            v-model="formStep2.province_code"
                                            :options="provinces"
                                            placeholder="Pilih Provinsi"
                                            :error="!!formStep2.errors.province_code"
                                        />
                                        <p v-if="formStep2.errors.province_code" class="text-red-500 text-xs mt-1">{{ formStep2.errors.province_code }}</p>
                                    </div>

                                    <!-- Kota -->
                                    <div v-if="formStep2.province_code" class="animate-fade-in">
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Kota / Kabupaten <span class="text-red-500">*</span></label>
                                        <SearchableSelect
                                            v-model="formStep2.city_code"
                                            :options="cities"
                                            placeholder="Pilih Kota"
                                            :error="!!formStep2.errors.city_code"
                                        />
                                        <p v-if="formStep2.errors.city_code" class="text-red-500 text-xs mt-1">{{ formStep2.errors.city_code }}</p>
                                    </div>

                                    <!-- Kecamatan -->
                                    <div v-if="formStep2.province_code" class="animate-fade-in">
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Kecamatan <span class="text-red-500">*</span></label>
                                        <SearchableSelect
                                            v-model="formStep2.district_code"
                                            :options="districts"
                                            placeholder="Pilih Kecamatan"
                                            :error="!!formStep2.errors.district_code"
                                        />
                                        <p v-if="formStep2.errors.district_code" class="text-red-500 text-xs mt-1">{{ formStep2.errors.district_code }}</p>
                                    </div>

                                    <!-- Desa / Kelurahan -->
                                    <div v-if="formStep2.province_code" class="animate-fade-in">
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Desa / Kelurahan <span class="text-red-500">*</span></label>
                                        <SearchableSelect
                                            v-model="formStep2.village_code"
                                            :options="villages"
                                            placeholder="Pilih Desa"
                                            :error="!!formStep2.errors.village_code"
                                        />
                                        <p v-if="formStep2.errors.village_code" class="text-red-500 text-xs mt-1">{{ formStep2.errors.village_code }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Kode Pos <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="formStep2.postal_code" maxlength="5" placeholder="Misal: 40111" class="w-full h-[48px] border border-muted/20 rounded-[12px] px-4 text-[14px] text-text placeholder-muted focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white" :class="{ 'border-red-500': formStep2.errors.postal_code }" />
                                        <p v-if="formStep2.errors.postal_code" class="text-red-500 text-xs mt-1">{{ formStep2.errors.postal_code }}</p>
                                    </div>

                                    <!-- Alamat Lengkap -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[14px] font-medium text-secondary mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                        <textarea v-model="formStep2.address" rows="3" placeholder="Nama Jalan, Blok, No. Rumah, RT/RW..." class="w-full border border-muted/20 rounded-[12px] px-4 py-3 text-[14px] text-text placeholder-muted focus:border-primary focus:ring-4 focus:ring-primary/20 shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all outline-none bg-white resize-none" :class="{ 'border-red-500': formStep2.errors.address }"></textarea>
                                        <p v-if="formStep2.errors.address" class="text-red-500 text-xs mt-1">{{ formStep2.errors.address }}</p>
                                        <p v-else class="text-[13px] text-muted mt-1.5">Tambahkan patokan jika perlu untuk memudahkan navigasi.</p>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- TAHAP 3: VERIFIKASI IDENTITAS -->
                        <div v-show="currentStep === 3" class="space-y-12 animate-fade-in">

                            <section class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-muted/10 p-8 sm:p-10">
                                <div class="mb-8">
                                    <h2 class="text-[22px] font-semibold text-secondary">Upload Dokumen</h2>
                                    <p class="text-[14px] text-muted mt-1.5 leading-relaxed">Keamanan data Anda terjamin. Kami menggunakan enkripsi kelas enterprise untuk melindungi dokumen Anda.</p>
                                </div>

                                <!-- Upload Area -->
                                <div>
                                    <label class="block text-[14px] font-medium text-secondary mb-3">Foto KTP Asli <span class="text-red-500">*</span></label>
                                    <div class="border-2 border-dashed border-muted/20 rounded-[16px] p-10 flex flex-col items-center justify-center relative hover:bg-background hover:border-primary/50 transition-all cursor-pointer group min-h-[240px] bg-white" :class="{ 'border-red-500 bg-red-50': formStep3.errors.ktp_photo }">
                                        <input type="file" accept="image/*" @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />

                                        <div v-if="!ktpPreview" class="flex flex-col items-center text-center pointer-events-none">
                                            <div class="w-14 h-14 rounded-full bg-background border border-muted/10 text-secondary flex items-center justify-center mb-5 group-hover:scale-105 transition-transform duration-300 shadow-sm">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                            </div>
                                            <h4 class="text-[15px] font-semibold text-secondary mb-1.5">Pilih file atau seret ke sini</h4>
                                            <p class="text-[13px] text-muted">Mendukung format JPG, JPEG, PNG (Maks. 5MB)</p>
                                        </div>
                                        <div v-else class="flex flex-col items-center gap-4 relative z-0">
                                            <img :src="ktpPreview" class="h-32 object-contain rounded-xl border border-muted/20 shadow-sm" />
                                            <div class="flex items-center gap-2 text-[13px] font-semibold text-emerald-600 bg-emerald-50 px-4 py-1.5 rounded-full border border-emerald-100">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                Dokumen Siap Diupload
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="formStep3.errors.ktp_photo" class="text-red-500 text-xs mt-2">{{ formStep3.errors.ktp_photo }}</p>
                                </div>
                            </section>

                        </div>

                    </div>
                </div>
            </div>

            <!-- STICKY BOTTOM ACTION BAR (Custom for Desktop, but DetailBottomBar can be for mobile) -->
            <DetailBottomBar class="md:hidden"
                :buttonText="currentStep < 3 ? 'Selanjutnya' : 'Selesaikan'"
                @submit="currentStep < 3 ? nextStep() : submit()"
                :disabled="currentStep === 1 ? formStep1.processing : (currentStep === 2 ? formStep2.processing : formStep3.processing)">

                <template #left-content>
                    <button type="button" @click="prevStep" class="h-[48px] px-6 rounded-[12px] border border-muted/30 text-secondary font-semibold text-[14px] hover:bg-background transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </button>
                </template>
            </DetailBottomBar>

            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-muted/20 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] z-40 hidden md:block">
                <div class="max-w-6xl mx-auto px-6 lg:px-8 h-20 flex items-center justify-between">
                    <div>
                        <button type="button" @click="prevStep" class="h-[48px] px-6 rounded-[12px] border border-muted/30 text-secondary font-semibold text-[14px] hover:bg-background transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Kembali
                        </button>
                    </div>

                    <div>
                        <button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="currentStep === 1 ? formStep1.processing : formStep2.processing" class="h-[48px] px-8 rounded-[12px] bg-primary text-secondary font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            Selanjutnya
                            <svg v-if="currentStep === 1 ? formStep1.processing : formStep2.processing" class="animate-spin h-4 w-4 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                        <button v-else type="button" @click="submit" :disabled="formStep3.processing" class="h-[48px] px-8 rounded-[12px] bg-primary text-secondary font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <svg v-if="formStep3.processing" class="animate-spin -ml-1 mr-1 h-4 w-4 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            {{ formStep3.processing ? 'Memproses...' : 'Selesaikan Pendaftaran' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
