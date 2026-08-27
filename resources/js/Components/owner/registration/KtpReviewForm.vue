<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import DetailBottomBar from '@/Components/ui/DetailBottomBar.vue';
import { IdCard, Home, Loader2 } from 'lucide-vue-next';

const props = defineProps({
  initialData: { type: Object, default: null },
  allCities:   { type: Array, default: () => [] },
  provinces:   { type: Array, default: () => [] },
  isManual:    { type: Boolean, default: false }, // true jika user pilih isi manual
});

const emit = defineEmits(['confirmed']);

// ============================================================
// State
// ============================================================
const cities    = ref([]);
const districts = ref([]);
const villages  = ref([]);

const form = reactive({
  // Data Diri
  name:                props.initialData?.name             ?? '',
  email:               props.initialData?.email            ?? '',
  phone:               props.initialData?.phone            ?? '',
  gender:              props.initialData?.gender           ?? '',
  place_of_birth_code: props.initialData?.place_of_birth_code ?? '',
  date_of_birth:       props.initialData?.date_of_birth    ?? '',
  national_id:         props.initialData?.nik              ?? '',
  // KTP Tambahan
  religion:            props.initialData?.religion         ?? '',
  marital_status:      props.initialData?.marital_status   ?? '',
  occupation:          props.initialData?.occupation       ?? '',
  nationality:         props.initialData?.nationality      ?? 'WNI',
  // Alamat Domisili
  province_code:       props.initialData?.province_code    ?? '',
  city_code:           props.initialData?.city_code        ?? '',
  district_code:       props.initialData?.district_code    ?? '',
  village_code:        props.initialData?.village_code     ?? '',
  postal_code:         props.initialData?.postal_code      ?? '',
  address:             props.initialData?.address_domicile ?? '',
  // KTP path (hidden)
  ktp_temp_path:       props.initialData?.ktp_temp_path    ?? '',
});

// Track field mana yang diisi dari OCR (untuk badge OCR)
const ocrFilledFields = computed(() => {
  if (!props.initialData || props.isManual) return new Set();
  const filled = new Set();
  const ocrMapping = {
    national_id:    'nik',
    name:           'name',
    gender:         'gender',
    date_of_birth:  'birth_date',
    religion:       'religion',
    marital_status: 'marital_status',
    occupation:     'occupation',
    nationality:    'nationality',
    address:        'address_domicile',
    province_code:  'province_code',   // dari DB lookup kecamatan/kelurahan
    city_code:      'city_code',       // dari DB lookup kecamatan/kelurahan
  };
  for (const [formField, ocrField] of Object.entries(ocrMapping)) {
    if (props.initialData[ocrField] != null && props.initialData[ocrField] !== '') {
      filled.add(formField);
    }
  }
  return filled;
});

const errors = reactive({});

// ============================================================
// Location cascading
// ============================================================
const fetchCities = async (provinceCode) => {
  if (!provinceCode) return;
  try {
    const res = await axios.get(`/api/cities?province_code=${provinceCode}`);
    cities.value = res.data.data;
  } catch (e) { /* silent */ }
};

const fetchDistricts = async (cityCode) => {
  if (!cityCode) return;
  try {
    const res = await axios.get(`/api/districts?city_code=${cityCode}`);
    districts.value = res.data.data;
  } catch (e) { /* silent */ }
};

const fetchVillages = async (districtCode) => {
  if (!districtCode) return;
  try {
    const res = await axios.get(`/api/villages?district_code=${districtCode}`);
    villages.value = res.data.data;
  } catch (e) { /* silent */ }
};

watch(() => form.province_code, (val, old) => {
  if (val) fetchCities(val);
  if (old && val !== old) {
    form.city_code = '';
    form.district_code = '';
    form.village_code = '';
    cities.value = [];
    districts.value = [];
    villages.value = [];
  }
});

watch(() => form.city_code, (val, old) => {
  if (val) fetchDistricts(val);
  if (old && val !== old) {
    form.district_code = '';
    form.village_code = '';
    districts.value = [];
    villages.value = [];
  }
});

watch(() => form.district_code, (val, old) => {
  if (val) fetchVillages(val);
  if (old && val !== old) {
    form.village_code = '';
    villages.value = [];
  }
});

onMounted(async () => {
  // Load cascading secara berurutan (await) agar dropdown terisi dengan benar
  if (form.province_code) await fetchCities(form.province_code);
  if (form.city_code)     await fetchDistricts(form.city_code);
  if (form.district_code) await fetchVillages(form.district_code);
});

// ============================================================
// Validation
// ============================================================
const validateForm = () => {
  Object.keys(errors).forEach(k => delete errors[k]);
  let valid = true;

  if (!form.name)         { errors.name = 'Nama wajib diisi.'; valid = false; }
  if (!form.national_id)  { errors.national_id = 'NIK wajib diisi.'; valid = false; }
  else if (!/^\d{16}$/.test(form.national_id)) {
    errors.national_id = 'NIK harus tepat 16 digit angka.'; valid = false;
  }
  if (!form.place_of_birth_code) { errors.place_of_birth_code = 'Tempat lahir wajib dipilih.'; valid = false; }
  if (!form.date_of_birth){ errors.date_of_birth = 'Tanggal lahir wajib diisi.'; valid = false; }
  if (!form.gender)       { errors.gender = 'Jenis kelamin wajib dipilih.'; valid = false; }
  if (!form.email)        { errors.email = 'Email wajib diisi.'; valid = false; }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Format email tidak valid.'; valid = false;
  }
  if (!form.phone)        { errors.phone = 'Nomor HP / WhatsApp wajib diisi.'; valid = false; }
  if (!form.province_code){ errors.province_code = 'Provinsi wajib dipilih.'; valid = false; }
  if (!form.city_code)    { errors.city_code = 'Kota wajib dipilih.'; valid = false; }
  if (!form.district_code){ errors.district_code = 'Kecamatan wajib dipilih.'; valid = false; }
  if (!form.village_code) { errors.village_code = 'Kelurahan wajib dipilih.'; valid = false; }
  if (!form.postal_code || !/^\d{5}$/.test(form.postal_code)) {
    errors.postal_code = 'Kode pos harus 5 digit angka.'; valid = false;
  }
  if (!form.address)      { errors.address = 'Alamat lengkap wajib diisi.'; valid = false; }

  return valid;
};

const isSubmitting = ref(false);

const submit = () => {
  if (!validateForm()) {
    nextTick(() => {
      const firstError = document.querySelector('.border-red-500');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus({ preventScroll: true });
      }
    });
    return;
  }
  isSubmitting.value = true;
  emit('confirmed', { ...form });
};

// ============================================================
// Helpers
// ============================================================
const genderLabel = computed(() => form.gender === 'male' ? 'Laki-laki' : form.gender === 'female' ? 'Perempuan' : '');
const birthPlaceHint = computed(() => props.initialData?.birth_place_ocr_text ?? '');
</script>

<template>
  <div class="space-y-4">

    <!-- Title & Description Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 md:p-6 shadow-sm space-y-1">
      <h2 class="text-xl md:text-2xl font-bold text-[#0A2540] tracking-tight">Periksa Lagi Data KTP Kamu</h2>
      <p class="text-sm text-slate-500 leading-relaxed">Data telah diisi berdasarkan KTP. Pastikan semua informasi sudah sesuai sebelum melanjutkan.</p>
    </div>

    <!-- Stacked Layout: Atas (Data Diri), Bawah (Alamat di KTP) - Melebar Penuh -->
    <div class="space-y-4 w-full">

      <!-- ================= CARD 1: DATA DIRI ================= -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-4 md:p-6 shadow-sm space-y-4">
        <!-- Section Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <!-- ID Card Icon (Yellow) -->
            <IdCard class="w-6 h-6 text-[#FFC000]" />
            <h3 class="text-base font-bold text-[#0A2540]">Data Diri</h3>
          </div>
        </div>

        <div class="space-y-4">
          <!-- NIK -->
          <div>
            <label class="field-label">
              NIK <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.national_id"
              type="text"
              maxlength="16"
              placeholder="16 Digit NIK"
              class="field-input"
              :class="{ 'border-red-500': errors.national_id }"
            />
            <div class="flex justify-between items-center mt-1">
              <p v-if="errors.national_id" class="text-red-500 text-xs font-medium">{{ errors.national_id }}</p>
              <p class="text-xs text-slate-400 ml-auto">{{ form.national_id.length }}/16</p>
            </div>
          </div>

          <!-- Nama Lengkap -->
          <div>
            <label class="field-label">
              Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input v-model="form.name" type="text" placeholder="Sesuai KTP" class="field-input" :class="{ 'border-red-500': errors.name }"/>
            <p v-if="errors.name" class="text-red-500 text-xs mt-1 font-medium">{{ errors.name }}</p>
          </div>

          <!-- Grid for Tempat & Tanggal Lahir -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tempat Lahir -->
            <div>
              <label class="field-label">
                Tempat Lahir <span class="text-red-500">*</span>
              </label>
              <select v-model="form.place_of_birth_code" class="field-input appearance-none" :class="{ 'border-red-500': errors.place_of_birth_code }">
                <option value="" disabled>
                  {{ birthPlaceHint ? `Pilih kota (OCR: ${birthPlaceHint})` : 'Pilih kota lahir' }}
                </option>
                <option v-for="city in allCities" :key="city.code" :value="city.code">{{ city.name }}</option>
              </select>
              <p v-if="errors.place_of_birth_code" class="text-red-500 text-xs mt-1 font-medium">{{ errors.place_of_birth_code }}</p>
            </div>

            <!-- Tanggal Lahir -->
            <div>
              <label class="field-label">
                Tanggal Lahir <span class="text-red-500">*</span>
              </label>
              <input v-model="form.date_of_birth" type="date" class="field-input" :class="{ 'border-red-500': errors.date_of_birth }"/>
              <p v-if="errors.date_of_birth" class="text-red-500 text-xs mt-1 font-medium">{{ errors.date_of_birth }}</p>
            </div>
          </div>

          <!-- Grid for Jenis Kelamin & Kewarganegaraan -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Jenis Kelamin -->
            <div>
              <label class="field-label">
                Jenis Kelamin <span class="text-red-500">*</span>
              </label>
              <select v-model="form.gender" class="field-input appearance-none" :class="{ 'border-red-500': errors.gender }">
                <option value="" disabled>Pilih salah satu</option>
                <option value="male">Laki-laki</option>
                <option value="female">Perempuan</option>
              </select>
              <p v-if="errors.gender" class="text-red-500 text-xs mt-1 font-medium">{{ errors.gender }}</p>
            </div>

            <!-- Kewarganegaraan -->
            <div>
              <label class="field-label">
                Kewarganegaraan
              </label>
              <input v-model="form.nationality" type="text" placeholder="WNI" class="field-input"/>
            </div>
          </div>

          <!-- Grid for Agama & Status -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Agama -->
            <div>
              <label class="field-label">
                Agama
              </label>
              <input v-model="form.religion" type="text" placeholder="Contoh: Islam" class="field-input"/>
            </div>

            <!-- Status Perkawinan -->
            <div>
              <label class="field-label">
                Status
              </label>
              <input v-model="form.marital_status" type="text" placeholder="Contoh: Belum Kawin" class="field-input"/>
            </div>
          </div>

          <!-- Pekerjaan -->
          <div>
            <label class="field-label">
              Pekerjaan
            </label>
            <input v-model="form.occupation" type="text" placeholder="Contoh: Karyawan Swasta" class="field-input"/>
          </div>

          <!-- Email -->
          <div>
            <label class="field-label">Email <span class="text-red-500">*</span></label>
            <input v-model="form.email" type="email" placeholder="contoh@email.com" class="field-input" :class="{ 'border-red-500': errors.email }"/>
            <p v-if="errors.email" class="text-red-500 text-xs mt-1 font-medium">{{ errors.email }}</p>
          </div>

          <!-- No HP -->
          <div>
            <label class="field-label">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
            <input v-model="form.phone" type="text" placeholder="0812xxxxxxxx" class="field-input" :class="{ 'border-red-500': errors.phone }"/>
            <p v-if="errors.phone" class="text-red-500 text-xs mt-1 font-medium">{{ errors.phone }}</p>
          </div>
        </div>
      </div>

      <!-- ================= CARD 2: ALAMAT DI KTP ================= -->
      <div class="bg-white rounded-2xl border border-slate-200/80 p-4 md:p-6 shadow-sm space-y-4">
        <!-- Section Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <!-- House Icon (Yellow) -->
            <Home class="w-6 h-6 text-[#FFC000]" />
            <h3 class="text-base font-bold text-[#0A2540]">Alamat di KTP</h3>
          </div>
        </div>

        <div class="space-y-4">
          <!-- Negara -->
          <div>
            <label class="field-label">Negara</label>
            <input type="text" value="Indonesia" disabled class="field-input bg-slate-50 border-slate-200 text-slate-500 cursor-not-allowed" />
          </div>

          <!-- Provinsi -->
          <div>
            <label class="field-label">
              Provinsi <span class="text-red-500">*</span>
            </label>
            <select v-model="form.province_code" class="field-input appearance-none" :class="{ 'border-red-500': errors.province_code }">
              <option value="" disabled>Pilih Provinsi</option>
              <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
            </select>
            <p v-if="errors.province_code" class="text-red-500 text-xs mt-1 font-medium">{{ errors.province_code }}</p>
          </div>

          <!-- Kota -->
          <div v-if="form.province_code">
            <label class="field-label">
              Kota / Kabupaten <span class="text-red-500">*</span>
            </label>
            <select v-model="form.city_code" class="field-input appearance-none" :class="{ 'border-red-500': errors.city_code }">
              <option value="" disabled>Pilih Kota</option>
              <option v-for="c in cities" :key="c.code" :value="c.code">{{ c.name }}</option>
            </select>
            <p v-if="errors.city_code" class="text-red-500 text-xs mt-1 font-medium">{{ errors.city_code }}</p>
          </div>

          <!-- Kecamatan -->
          <div v-if="form.city_code">
            <label class="field-label">Kecamatan <span class="text-red-500">*</span></label>
            <select v-model="form.district_code" class="field-input appearance-none" :class="{ 'border-red-500': errors.district_code }">
              <option value="" disabled>Pilih Kecamatan</option>
              <option v-for="d in districts" :key="d.code" :value="d.code">{{ d.name }}</option>
            </select>
            <p v-if="errors.district_code" class="text-red-500 text-xs mt-1 font-medium">{{ errors.district_code }}</p>
          </div>

          <!-- Grid for Kelurahan & Kode Pos -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Kelurahan -->
            <div v-if="form.district_code">
              <label class="field-label">Kelurahan / Desa <span class="text-red-500">*</span></label>
              <select v-model="form.village_code" class="field-input appearance-none" :class="{ 'border-red-500': errors.village_code }">
                <option value="" disabled>Pilih Kelurahan</option>
                <option v-for="v in villages" :key="v.code" :value="v.code">{{ v.name }}</option>
              </select>
              <p v-if="errors.village_code" class="text-red-500 text-xs mt-1 font-medium">{{ errors.village_code }}</p>
            </div>

            <!-- Kode Pos -->
            <div>
              <label class="field-label">Kode Pos <span class="text-red-500">*</span></label>
              <input v-model="form.postal_code" type="text" maxlength="5" placeholder="Misal: 40111" class="field-input" :class="{ 'border-red-500': errors.postal_code }"/>
              <p v-if="errors.postal_code" class="text-red-500 text-xs mt-1 font-medium">{{ errors.postal_code }}</p>
            </div>
          </div>

          <!-- Alamat Lengkap -->
          <div>
            <label class="field-label">
              Alamat Lengkap <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.address" rows="4" placeholder="Nama Jalan, Blok, No. Rumah, RT/RW..." class="field-input resize-none h-auto py-2.5" :class="{ 'border-red-500': errors.address }"></textarea>
            <p v-if="errors.address" class="text-red-500 text-xs mt-1 font-medium">{{ errors.address }}</p>
          </div>
        </div>
      </div>

    </div>

    <!-- Submit Button (Desktop Sticky Bottom Bar) -->
    <Teleport to="body">
      <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] z-50 hidden md:flex items-center justify-between px-8 md:px-12 h-16 animate-fade-in">
        <p class="text-sm font-medium text-slate-500">
          Data yang Anda masukkan akan diperiksa oleh tim KitaSewa sebelum diverifikasi.
        </p>
        <button
          type="button"
          @click="submit"
          :disabled="isSubmitting"
          class="h-10 px-8 rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-sm hover:brightness-95 transition-all shadow-md flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <Loader2 v-if="isSubmitting" class="animate-spin h-4 w-4" />
          Lanjutkan
        </button>
      </div>
    </Teleport>

    <!-- Mobile Bottom Bar Action (Lanjutkan) -->
    <DetailBottomBar class="md:hidden" :hideLeftContent="true" @submit="submit">
      <template #left-content>
        <span class="hidden"></span>
      </template>
      <template #right-content>
        <button
          type="button"
          @click="submit"
          :disabled="isSubmitting"
          class="w-full h-11 px-4 rounded-xl bg-[#FFC000] text-[#0A2540] font-bold text-[13px] shadow-sm transition-colors flex items-center justify-center gap-2 disabled:opacity-60"
        >
          <Loader2 v-if="isSubmitting" class="animate-spin h-3.5 w-3.5" />
          Lanjutkan
        </button>
      </template>
    </DetailBottomBar>

  </div>
</template>

<style scoped>
@reference "tailwindcss";

.field-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 700;
  color: #0A2540;
  margin-bottom: 0.5rem;
}

.field-input {
  width: 100%;
  height: 48px;
  border: 1px solid #cbd5e1;
  border-radius: 0.375rem;
  padding-left: 1rem;
  padding-right: 1rem;
  font-size: 0.875rem;
  color: #0A2540;
  background: white;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.field-input::placeholder {
  color: #94a3b8;
}

.field-input:focus {
  border-color: #FFC000;
  box-shadow: 0 0 0 1px #FFC000;
}

.ocr-badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  line-height: 1;
}
</style>
