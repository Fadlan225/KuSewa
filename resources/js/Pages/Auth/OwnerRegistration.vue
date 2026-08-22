<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import DetailBottomBar from '@/Components/ui/DetailBottomBar.vue';
import SearchableSelect from '@/Components/ui/SearchableSelect.vue';

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

const ktpPreview = ref(props.initialProfile?.ktp_photo_url || null);

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

const mainSteps = computed(() => {
    return [
        { id: 1, title: 'Data Diri', internalSteps: [1] },
        { id: 2, title: 'Alamat', internalSteps: [2] },
        { id: 3, title: 'Verifikasi', internalSteps: [3] }
    ];
});

const currentMainStep = computed(() => {
    return mainSteps.value.find(ms => ms.internalSteps.includes(currentStep.value)) || mainSteps.value[0];
});

const isCurrentStepValid = computed(() => {
    if (currentStep.value === 1) {
        return formStep1.name && formStep1.email && formStep1.phone && formStep1.gender && formStep1.place_of_birth_code && formStep1.date_of_birth && formStep1.national_id;
    }
    if (currentStep.value === 2) {
        return formStep2.province_code && formStep2.city_code && formStep2.district_code && formStep2.village_code && formStep2.postal_code && formStep2.address;
    }
    if (currentStep.value === 3) {
        return !!formStep3.ktp_photo || !!props.initialProfile?.has_ktp_photo;
    }
    return false;
});

const getProgressWidth = (mStep) => {
    if (currentMainStep.value.id > mStep.id) return '100%';
    if (currentMainStep.value.id < mStep.id) return '0%';

    const currentIndex = mStep.internalSteps.indexOf(currentStep.value) + 1;
    const total = mStep.internalSteps.length;
    return `${(currentIndex / total) * 100}%`;
};

// Combine errors to easily display them
const currentErrors = () => {
    if (currentStep.value === 1) return formStep1.errors;
    if (currentStep.value === 2) return formStep2.errors;
    if (currentStep.value === 3) return formStep3.errors;
    return {};
};
</script>

<template>
    <Head title="Pendaftaran Owner - kitasewa.id" />

    <AppLayout hideNavbar hideBottombar>
        <DetailNavbar title="Pendaftaran Pemilik Aset" :showBackButton="true" :showSections="false" :showShare="false" :showFavorite="false" forceBackUrl backUrl="/" />

        <!-- Container Utama -->
        <div class="min-h-screen bg-slate-50 font-sans text-[#0A2540] pb-32 pt-[60px]">
            <div class="max-w-5xl mx-auto pt-4 pb-6 px-4 sm:px-6 lg:px-8 flex flex-col gap-6 lg:gap-8">

                <!-- MAIN PROGRESS BAR -->
                <div class="w-full flex items-start justify-between relative">
                    <template v-for="(mStep, index) in mainSteps" :key="mStep.id">
                        <div class="flex-1 flex flex-col relative z-10 px-1 sm:px-2 text-center items-center group">

                            <!-- Icon & Title -->
                            <div class="flex items-center justify-center gap-1.5 sm:gap-2 mb-2 sm:mb-3">
                                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-[11px] sm:text-[13px] font-bold transition-colors shrink-0 border-[1.5px]"
                                    :class="[
                                        currentStep > mStep.internalSteps[mStep.internalSteps.length-1]
                                            ? 'border-[#FFC000] text-[#FFC000] bg-transparent'
                                            : currentMainStep.id === mStep.id
                                            ? 'border-[#FFC000] text-[#FFC000] bg-transparent'
                                            : 'border-slate-300 text-slate-400 bg-transparent'
                                    ]">
                                    <span v-if="currentStep > mStep.internalSteps[mStep.internalSteps.length-1]" class="font-black">✓</span>
                                    <span v-else>{{ mStep.id }}</span>
                                </div>
                                <span class="text-[11px] sm:text-[14px] whitespace-nowrap transition-colors tracking-tight"
                                      :class="currentMainStep.id >= mStep.id ? 'text-[#0A2540] font-bold' : 'text-slate-400 font-medium'">
                                    {{ mStep.title }}
                                </span>
                            </div>

                            <!-- Continuous Progress Line -->
                            <div class="w-full h-[3px] mt-auto relative px-1">
                                <div class="w-full h-full bg-slate-200 relative">
                                    <div class="h-full bg-[#FFC000] transition-all duration-500 ease-out"
                                         :style="{ width: getProgressWidth(mStep) }">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- RIGHT CONTENT AREA (FORM CARD) -->
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg p-6 md:p-8 border border-slate-200/80 shadow-sm space-y-6 relative">

                        <!-- TAHAP 1: DATA DIRI -->
                        <div v-show="currentStep === 1" class="space-y-8 animate-fade-in">
                            <div class="border-b border-slate-200 pb-4">
                                <h2 class="text-2xl font-bold text-[#0A2540]">Informasi Pribadi</h2>
                                <p class="text-sm text-slate-500 mt-1">Lengkapi identitas pribadi Anda sesuai dengan kartu identitas (KTP) yang sah.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                                <!-- Nama Lengkap -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="formStep1.name" placeholder="Sesuai KTP" class="w-full h-[48px] border border-slate-300 rounded-md px-4 text-sm text-[#0A2540] placeholder-slate-400 focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep1.errors.name }" />
                                    <p v-if="formStep1.errors.name" class="text-red-500 text-xs mt-1 font-medium">{{ formStep1.errors.name }}</p>
                                </div>

                                <!-- NIK -->
                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Nomor Induk Kependudukan <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="formStep1.national_id" maxlength="16" placeholder="16 Digit NIK" class="w-full h-[48px] border border-slate-300 rounded-md px-4 text-sm text-[#0A2540] placeholder-slate-400 focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep1.errors.national_id }" />
                                    <div class="flex justify-between items-center mt-1.5">
                                        <p v-if="formStep1.errors.national_id" class="text-red-500 text-xs font-medium">{{ formStep1.errors.national_id }}</p>
                                        <p class="text-xs text-slate-500 ml-auto font-medium">{{ formStep1.national_id.length }}/16 karakter</p>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Email <span class="text-red-500">*</span></label>
                                    <input type="email" v-model="formStep1.email" placeholder="contoh@email.com" class="w-full h-[48px] border border-slate-300 rounded-md px-4 text-sm text-[#0A2540] placeholder-slate-400 focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep1.errors.email }" />
                                    <p v-if="formStep1.errors.email" class="text-red-500 text-xs mt-1 font-medium">{{ formStep1.errors.email }}</p>
                                </div>

                                <!-- No Telepon -->
                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="formStep1.phone" placeholder="0812xxxxxxxx" class="w-full h-[48px] border border-slate-300 rounded-md px-4 text-sm text-[#0A2540] placeholder-slate-400 focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep1.errors.phone }" />
                                    <p v-if="formStep1.errors.phone" class="text-red-500 text-xs mt-1 font-medium">{{ formStep1.errors.phone }}</p>
                                </div>

                                <!-- Jenis Kelamin -->
                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select v-model="formStep1.gender" class="w-full h-[48px] appearance-none border border-slate-300 rounded-md px-4 text-sm text-[#0A2540] focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white cursor-pointer" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep1.errors.gender }">
                                            <option value="" disabled selected>Pilih salah satu</option>
                                            <option value="male">Laki-laki</option>
                                            <option value="female">Perempuan</option>
                                        </select>
                                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                    <p v-if="formStep1.errors.gender" class="text-red-500 text-xs mt-1 font-medium">{{ formStep1.errors.gender }}</p>
                                </div>

                                <!-- Tempat Lahir -->
                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                                    <SearchableSelect
                                        v-model="formStep1.place_of_birth_code"
                                        :options="allCities"
                                        placeholder="Pilih kota lahir"
                                        :error="!!formStep1.errors.place_of_birth_code"
                                    />
                                    <p v-if="formStep1.errors.place_of_birth_code" class="text-red-500 text-xs mt-1 font-medium">{{ formStep1.errors.place_of_birth_code }}</p>
                                </div>

                                <!-- Tanggal Lahir -->
                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="date" v-model="formStep1.date_of_birth" class="w-full h-[48px] appearance-none border border-slate-300 rounded-md px-4 pr-10 text-sm text-[#0A2540] focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep1.errors.date_of_birth }" />
                                    </div>
                                    <p v-if="formStep1.errors.date_of_birth" class="text-red-500 text-xs mt-1 font-medium">{{ formStep1.errors.date_of_birth }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- TAHAP 2: ALAMAT -->
                        <div v-show="currentStep === 2" class="space-y-8 animate-fade-in">
                            <div class="border-b border-slate-200 pb-4">
                                <h2 class="text-2xl font-bold text-[#0A2540]">Alamat Domisili</h2>
                                <p class="text-sm text-slate-500 mt-1">Masukkan alamat domisili tempat Anda tinggal saat ini.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                                <!-- Provinsi -->
                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Provinsi <span class="text-red-500">*</span></label>
                                    <SearchableSelect
                                        v-model="formStep2.province_code"
                                        :options="provinces"
                                        placeholder="Pilih Provinsi"
                                        :error="!!formStep2.errors.province_code"
                                    />
                                    <p v-if="formStep2.errors.province_code" class="text-red-500 text-xs mt-1 font-medium">{{ formStep2.errors.province_code }}</p>
                                </div>

                                <!-- Kota -->
                                <div v-if="formStep2.province_code" class="animate-fade-in">
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Kota / Kabupaten <span class="text-red-500">*</span></label>
                                    <SearchableSelect
                                        v-model="formStep2.city_code"
                                        :options="cities"
                                        placeholder="Pilih Kota"
                                        :error="!!formStep2.errors.city_code"
                                    />
                                    <p v-if="formStep2.errors.city_code" class="text-red-500 text-xs mt-1 font-medium">{{ formStep2.errors.city_code }}</p>
                                </div>

                                <!-- Kecamatan -->
                                <div v-if="formStep2.province_code" class="animate-fade-in">
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Kecamatan <span class="text-red-500">*</span></label>
                                    <SearchableSelect
                                        v-model="formStep2.district_code"
                                        :options="districts"
                                        placeholder="Pilih Kecamatan"
                                        :error="!!formStep2.errors.district_code"
                                    />
                                    <p v-if="formStep2.errors.district_code" class="text-red-500 text-xs mt-1 font-medium">{{ formStep2.errors.district_code }}</p>
                                </div>

                                <!-- Desa / Kelurahan -->
                                <div v-if="formStep2.province_code" class="animate-fade-in">
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Desa / Kelurahan <span class="text-red-500">*</span></label>
                                    <SearchableSelect
                                        v-model="formStep2.village_code"
                                        :options="villages"
                                        placeholder="Pilih Desa"
                                        :error="!!formStep2.errors.village_code"
                                    />
                                    <p v-if="formStep2.errors.village_code" class="text-red-500 text-xs mt-1 font-medium">{{ formStep2.errors.village_code }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Kode Pos <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="formStep2.postal_code" maxlength="5" placeholder="Misal: 40111" class="w-full h-[48px] border border-slate-300 rounded-md px-4 text-sm text-[#0A2540] placeholder-slate-400 focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep2.errors.postal_code }" />
                                    <p v-if="formStep2.errors.postal_code" class="text-red-500 text-xs mt-1 font-medium">{{ formStep2.errors.postal_code }}</p>
                                </div>

                                <!-- Alamat Lengkap -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-[#0A2540] mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea v-model="formStep2.address" rows="3" placeholder="Nama Jalan, Blok, No. Rumah, RT/RW..." class="w-full border border-slate-300 rounded-md px-4 py-3 text-sm text-[#0A2540] placeholder-slate-400 focus:border-[#FFC000] focus:ring-1 focus:ring-[#FFC000] transition-all outline-none bg-white resize-none" :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': formStep2.errors.address }"></textarea>
                                    <p v-if="formStep2.errors.address" class="text-red-500 text-xs mt-1 font-medium">{{ formStep2.errors.address }}</p>
                                    <p v-else class="text-xs text-slate-500 mt-1.5 font-medium">Tambahkan patokan jika perlu untuk memudahkan navigasi.</p>
                                </div>
                            </div>
                        </div>

                        <!-- TAHAP 3: VERIFIKASI IDENTITAS -->
                        <div v-show="currentStep === 3" class="space-y-8 animate-fade-in">
                            <div class="border-b border-slate-200 pb-4">
                                <h2 class="text-2xl font-bold text-[#0A2540]">Upload Dokumen</h2>
                                <p class="text-sm text-slate-500 mt-1">Keamanan data Anda terjamin. Kami menggunakan enkripsi kelas enterprise untuk melindungi dokumen Anda.</p>
                            </div>

                            <!-- Upload Area -->
                            <div>
                                <label class="block text-sm font-bold text-[#0A2540] mb-3">
                                    Foto KTP Asli
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="border-2 border-dashed border-slate-300 rounded-lg p-10 flex flex-col items-center justify-center relative hover:bg-slate-50 hover:border-[#FFC000] transition-all cursor-pointer group min-h-[240px] bg-white" :class="{ 'border-red-500 bg-red-50': formStep3.errors.ktp_photo }">
                                    <input type="file" accept="image/*" @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />

                                    <div v-if="!ktpPreview" class="flex flex-col items-center text-center pointer-events-none">
                                        <div class="w-14 h-14 rounded-full bg-slate-100 border border-slate-200 text-slate-500 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform duration-300">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-[#0A2540] mb-1.5">Pilih file atau seret ke sini</h4>
                                        <p class="text-xs text-slate-500">Mendukung format JPG, JPEG, PNG (Maks. 5MB)</p>
                                    </div>
                                    <div v-else class="flex flex-col items-center gap-4 relative z-0">
                                        <img :src="ktpPreview" class="h-32 object-contain rounded-md border border-slate-200 shadow-sm" />
                                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-200">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                            Dokumen {{ formStep3.ktp_photo ? 'Siap Diupload' : 'Tersimpan' }}
                                        </div>
                                    </div>
                                </div>
                                <p v-if="formStep3.errors.ktp_photo" class="text-red-500 text-xs mt-2 font-medium">{{ formStep3.errors.ktp_photo }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- STICKY BOTTOM ACTION BAR (Mobile) -->
            <DetailBottomBar class="md:hidden"
                :hideLeftContent="true"
                :buttonText="currentStep < 3 ? 'Berikutnya' : 'Selesaikan'"
                @submit="currentStep < 3 ? nextStep() : submit()"
                :disabled="!isCurrentStepValid || (currentStep === 1 ? formStep1.processing : (currentStep === 2 ? formStep2.processing : formStep3.processing))">

                <template #left-content>
                    <button type="button" @click="prevStep" class="h-[40px] px-4 rounded-md border border-slate-300 text-[#0A2540] font-semibold text-[14px] hover:bg-slate-50 transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                        Sebelumnya
                    </button>
                </template>
                <template #right-content>
                    <button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="!isCurrentStepValid || (currentStep === 1 ? formStep1.processing : formStep2.processing)" class="h-[40px] px-6 rounded-md bg-[#F2C94C] text-[#0A2540] font-bold text-[14px] shadow-sm transition-colors disabled:bg-slate-100 disabled:text-slate-400 flex items-center justify-center gap-2">
                        <svg v-if="currentStep === 1 ? formStep1.processing : formStep2.processing" class="animate-spin h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Berikutnya
                    </button>
                    <button v-else type="button" @click="submit" :disabled="!isCurrentStepValid || formStep3.processing" class="h-[40px] px-6 rounded-md bg-[#FFC000] text-[#0A2540] font-bold text-[14px] shadow-sm transition-colors disabled:bg-slate-100 disabled:text-slate-400 flex items-center justify-center gap-2">
                        <svg v-if="formStep3.processing" class="animate-spin -ml-1 mr-1 h-4 w-4 text-[#0A2540]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Selesaikan
                    </button>
                </template>
            </DetailBottomBar>

            <!-- STICKY BOTTOM ACTION BAR (Desktop) -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] z-40 hidden md:block">
                <div class="w-full px-6 lg:px-10 h-16 flex items-center justify-between">
                    <div>
                        <button type="button" @click="prevStep" class="h-[40px] px-6 rounded-md border border-slate-300 text-[#0A2540] font-semibold text-[14px] hover:bg-slate-50 transition-colors bg-white shadow-sm flex items-center gap-2" :class="currentStep === 1 ? 'invisible' : ''">
                            Sebelumnya
                        </button>
                    </div>

                    <div>
                        <button v-if="currentStep < 3" type="button" @click="nextStep" :disabled="!isCurrentStepValid || (currentStep === 1 ? formStep1.processing : formStep2.processing)" class="h-[40px] px-8 rounded-md bg-[#F2C94C] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                            <svg v-if="currentStep === 1 ? formStep1.processing : formStep2.processing" class="animate-spin -ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Berikutnya
                        </button>
                        <button v-else type="button" @click="submit" :disabled="!isCurrentStepValid || formStep3.processing" class="h-[40px] px-8 rounded-md bg-[#FFC000] text-[#0A2540] font-bold text-[14px] hover:brightness-95 transition-all shadow-sm flex items-center gap-2 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                            <svg v-if="formStep3.processing" class="animate-spin -ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
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
