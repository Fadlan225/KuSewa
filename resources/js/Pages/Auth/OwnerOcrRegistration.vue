<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DetailNavbar from '@/Components/ui/DetailNavbar.vue';
import DetailBottomBar from '@/Components/ui/DetailBottomBar.vue';
import KtpUploadArea from '@/Components/owner/registration/KtpUploadArea.vue';
import KtpFormSkeleton from '@/Components/owner/registration/KtpFormSkeleton.vue';
import KtpReviewForm from '@/Components/owner/registration/KtpReviewForm.vue';
import KtpOcrError from '@/Components/owner/registration/KtpOcrError.vue';
import KtpVerificationIllustration from '@/Components/ui/Icons/KtpVerificationIllustration.vue';

const props = defineProps({
    initialUser:    { type: Object, default: null },
    initialProfile: { type: Object, default: null },
});

// ============================================================
// OCR Flow States & Navigation
// ============================================================
const ocrSubStep  = ref(1); // 1: Instructions, 2: Upload/Scan, 3: Review Form, 4: Error
const ocrStatus  = ref('idle');   // idle | uploading | processing | success | failed
const ocrData    = ref(null);     // structured data dari OCR
const ocrError   = ref('');       // pesan error
const isSubmittingOcr = ref(false);

// Combined location lists for dropdown cascade
const provinces = ref([]);
const allCities = ref([]);

const fetchAllCities = async () => {
    try {
        const res = await axios.get('/api/cities');
        allCities.value = res.data.data;
    } catch (e) { /* silent */ }
};

const fetchProvinces = async () => {
    try {
        const res = await axios.get('/api/provinces');
        provinces.value = res.data.data;
    } catch (e) { /* silent */ }
};

onMounted(() => {
    fetchAllCities();
    fetchProvinces();
});

const cancelOcrFlow = () => {
    router.get(route('owner.register'));
};

// ============================================================
// OCR Event Handlers
// ============================================================
const handleOcrSuccess = (data) => {
    ocrData.value   = data;
    ocrStatus.value = 'success';
    ocrSubStep.value = 3; // Lanjut ke Review Form
};

const handleStatusChange = (status) => {
    ocrStatus.value = status;
};

const handleOcrFailed = (error) => {
    ocrError.value  = error ?? 'KTP tidak dapat dibaca.';
    ocrStatus.value = 'failed';
    ocrSubStep.value = 4; // Lanjut ke error view
};

const handleOcrRetry = () => {
    ocrStatus.value = 'idle';
    ocrData.value   = null;
    ocrError.value  = '';
    ocrSubStep.value = 2; // Kembali ke upload area
};

const handleConfirmedOcr = async (formData) => {
    isSubmittingOcr.value = true;

    const payload = {
        ...formData,
        email: formData.email || props.initialUser?.email,
        phone: formData.phone || props.initialUser?.phone,
    };

    router.post(route('owner.register.submit'), payload, {
        onSuccess: () => {
            isSubmittingOcr.value = false;
        },
        onError: (errs) => {
            isSubmittingOcr.value = false;
            console.error('Submit errors:', errs);
        },
        onFinish: () => {
            isSubmittingOcr.value = false;
        },
    });
};

const reviewFormData = computed(() => {
    if (!ocrData.value) return null;
    const d = ocrData.value;
    return {
        name:                  d.name                ?? props.initialUser?.name ?? '',
        email:                 props.initialUser?.email ?? '',
        phone:                 props.initialUser?.phone ?? '',
        gender:                d.gender              ?? props.initialUser?.gender ?? '',
        place_of_birth_code:   d.birth_place_city_code ?? props.initialUser?.place_of_birth_code ?? '',
        date_of_birth:         d.birth_date          ?? props.initialUser?.date_of_birth ?? '',
        nik:                   d.nik                 ?? props.initialProfile?.national_id ?? '',
        religion:              d.religion            ?? props.initialProfile?.religion ?? '',
        marital_status:        d.marital_status      ?? props.initialProfile?.marital_status ?? '',
        occupation:            d.occupation          ?? props.initialProfile?.occupation ?? '',
        nationality:           d.nationality         ?? props.initialProfile?.nationality ?? 'WNI',
        address_domicile:      d.address             ?? props.initialProfile?.address ?? '',
        province_code:         d.province_code       ?? props.initialProfile?.province_code ?? '',
        city_code:             d.city_code           ?? props.initialProfile?.city_code ?? '',
        district_code:         d.district_code       ?? props.initialProfile?.district_code ?? '',
        village_code:          d.village_code        ?? props.initialProfile?.village_code ?? '',
        postal_code:           props.initialProfile?.postal_code ?? '',
        ktp_temp_path:         d.ktp_temp_path       ?? '',
        birth_place_city_code: d.birth_place_city_code ?? null,
        birth_place_ocr_text:  d.birth_place_ocr_text  ?? null,
    };
});

// Dynamic Navbar Title based on sub-step
const navbarTitle = computed(() => {
    if (ocrStatus.value === 'uploading' || ocrStatus.value === 'processing') {
        return 'Memproses e-KTP...';
    }
    switch (ocrSubStep.value) {
        case 1:  return 'Pendaftaran Instant';
        case 2:  return 'Ambil Foto e-KTP';
        case 3:  return 'Periksa Data KTP';
        case 4:  return 'Verifikasi Gagal';
        default: return 'Verifikasi e-KTP';
    }
});

// Back navigation handler in dynamic DetailNavbar
const handleNavbarBack = () => {
    if (ocrSubStep.value === 1) {
        router.visit(route('owner.register'));
    } else if (ocrSubStep.value === 2) {
        ocrSubStep.value = 1;
    } else if (ocrSubStep.value === 3 || ocrSubStep.value === 4) {
        handleOcrRetry();
    }
};
</script>

<template>
    <Head title="Pendaftaran Instant" />

    <AppLayout hideNavbar hideBottombar hideFooter>
        <DetailNavbar
            :showSections="false"
            :showShare="false"
            :showFavorite="false"
        >
            <template #content>
                <div class="flex items-center gap-3 sm:gap-4 h-full">
                    <!-- Custom Back Button -->
                    <button @click="handleNavbarBack"
                            type="button"
                            class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors"
                            aria-label="Kembali">
                        <ArrowLeft class="text-[#0A2540] w-6 h-6" />
                    </button>

                    <h1 class="text-lg font-bold text-[#0A2540] truncate max-w-[200px] sm:max-w-xs">{{ navbarTitle }}</h1>
                </div>
            </template>
        </DetailNavbar>

        <!-- Container Utama -->
        <div class="min-h-screen bg-slate-50 font-sans text-[#0A2540] pb-20">
            <div class="max-w-5xl mx-auto pt-1 pb-2 px-5 md:px-8 flex flex-col gap-4 animate-fade-in">

                <!-- SUB-STEP 1: INSTRUCTIONS (SCREENSHOT 1 STYLE - TWO COLUMN LAYOUT) -->
                <div v-if="ocrSubStep === 1" class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center w-full my-4">
                    <!-- Left Column: Illustration -->
                    <div class="col-span-1 md:col-span-5 flex justify-center">
                        <KtpVerificationIllustration class="w-full h-auto max-w-[180px] md:max-w-full max-h-[220px] md:max-h-[380px]" />
                    </div>

                    <!-- Right Column: Content & Buttons -->
                    <div class="col-span-1 md:col-span-7 space-y-6">
                        <div class="space-y-2">
                            <h3 class="text-2xl font-bold text-[#0A2540] tracking-tight">Verifikasi e-KTP</h3>
                            <p class="text-sm text-slate-500">Biar kita lebih kenal, fotoin e-KTP kamu ya!</p>
                        </div>

                        <!-- Instructions List (Simple design, no background circles) -->
                        <div class="w-full bg-[#F7FAFC] border border-slate-200 rounded-xl p-5 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="text-[#FFC000] shrink-0 mt-0.5">
                                    <!-- Sun icon without background circle -->
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-600 leading-normal">Pastiin kamu ada di tempat terang</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="text-emerald-600 shrink-0 mt-0.5">
                                    <!-- Verify Shield/Check icon without background circle -->
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-600 leading-normal">Pastiin fisik e-KTP baik: gak lecek atau rusak dan tulisan bisa dibaca jelas</p>
                            </div>
                        </div>

                         <button @click="ocrSubStep = 2" type="button" class="hidden md:flex w-full h-12 rounded-xl bg-[#FFC000] text-[#0A2540] hover:brightness-95 font-bold text-sm shadow-md transition-all items-center justify-center gap-2">
                            Lanjut
                        </button>
                    </div>

                    <!-- Bottom Bar Action (Mobile Only) -->
                    <DetailBottomBar class="md:hidden"
                        :hideLeftContent="true"
                        buttonText="Lanjut"
                        @submit="ocrSubStep = 2">
                        <template #left-content>
                            <span class="hidden"></span>
                        </template>
                        <template #right-content>
                            <button type="button" @click="ocrSubStep = 2" class="w-full h-[40px] px-6 rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-[13px] shadow-sm transition-colors flex items-center justify-center gap-2">
                                Lanjut
                            </button>
                        </template>
                    </DetailBottomBar>
                </div>

                <!-- SUB-STEP 2: SCAN/UPLOAD AREA (FLAT LAYOUT) -->
                <div v-else-if="ocrSubStep === 2" class="w-full my-4 animate-fade-in">
                    <KtpUploadArea
                        @ocr-success="handleOcrSuccess"
                        @ocr-failed="handleOcrFailed"
                        @manual-entry="handleOcrRetry"
                        @status-change="handleStatusChange"
                    >
                        <template #error="{ message, retry, manual }">
                            <KtpOcrError
                                :message="message"
                                @retry="handleOcrRetry"
                                @manual-entry="cancelOcrFlow"
                            />
                        </template>
                    </KtpUploadArea>
                </div>

                <!-- SUB-STEP 3: REVIEW FORM (FLAT RESPONSIVE LAYOUT) -->
                <div v-else-if="ocrSubStep === 3" class="w-full my-1 animate-fade-in">
                    <KtpReviewForm
                        :initial-data="reviewFormData"
                        :all-cities="allCities"
                        :provinces="provinces"
                        :is-manual="false"
                        @confirmed="handleConfirmedOcr"
                    />
                </div>

                <!-- SUB-STEP 4: ERROR VIEW -->
                <div v-else-if="ocrSubStep === 4" class="bg-white rounded-xl p-6 md:p-8 border border-slate-200/80 shadow-sm animate-fade-in">
                    <KtpOcrError
                        :message="ocrError"
                        @retry="handleOcrRetry"
                        @manual-entry="cancelOcrFlow"
                    />
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
