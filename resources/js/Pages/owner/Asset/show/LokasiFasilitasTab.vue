<script setup>
import { MapPin, FolderPlus, X, Plus, Layers, Pencil } from 'lucide-vue-next';
import AppIcon from '@/Components/AppIcon.vue';
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/ui/card';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    asset: Object,
    form: Object,
    assetFacilities: Array,
    masterFacilityCategories: Array,
    nearbyPlaces: Object,
});

// --- FASILITAS LOGIC ---
const localFacilities = ref([...(props.assetFacilities || [])]);

watch(() => props.assetFacilities, (newVals) => {
    localFacilities.value = [...(newVals || [])];
});

const customCategoryIds = ref([]);
const selectedCategoryId = ref('');
const showAddCategory = ref(false);

const groupedFacilities = computed(() => {
    const groups = {};

    customCategoryIds.value.forEach(id => {
        const cat = props.masterFacilityCategories?.find(c => c.id === id);
        if (cat) {
            groups[cat.id] = { id: cat.id, name: cat.name, icon: cat.icon, facilities: [] };
        }
    });

    localFacilities.value.forEach(fac => {
        const catId = fac.facility_category_id;

        let catName = fac.category?.name || 'Umum';
        let catIcon = fac.category?.icon || 'fa-list';

        if (catId) {
            const masterCat = props.masterFacilityCategories?.find(c => c.id === catId);
            if (masterCat) {
                catName = masterCat.name;
                catIcon = masterCat.icon;
            }

            if (!groups[catId]) {
                groups[catId] = { id: catId, name: catName, icon: catIcon, facilities: [] };
            }
            groups[catId].facilities.push(fac);
        } else {
            if (!groups['umum']) groups['umum'] = { id: 'umum', name: 'Lainnya', icon: 'fa-list', facilities: [] };
            groups['umum'].facilities.push(fac);
        }
    });

    return groups;
});

const availableCategoriesToAdd = computed(() => {
    if (!props.masterFacilityCategories) return [];
    const existingIds = Object.keys(groupedFacilities.value).map(id => id === 'umum' ? 'umum' : Number(id));
    return props.masterFacilityCategories.filter(cat => !existingIds.includes(cat.id));
});

const addCategory = () => {
    if (!selectedCategoryId.value) return;
    if (!customCategoryIds.value.includes(selectedCategoryId.value)) {
        customCategoryIds.value.push(selectedCategoryId.value);
    }
    selectedCategoryId.value = '';
    showAddCategory.value = false;
};

const showAddFacilityModal = ref(false);
const activeCategoryId = ref(null);
const newFacilityId = ref('');

const openAddFacilityModal = (categoryId) => {
    activeCategoryId.value = categoryId;
    newFacilityId.value = '';
    showAddFacilityModal.value = true;
};

const availableFacilitiesForActiveCategory = computed(() => {
    if (!activeCategoryId.value || !props.masterFacilityCategories) return [];
    const category = props.masterFacilityCategories.find(c => c.id === activeCategoryId.value);
    if (!category) return [];
    return category.facilities.filter(mf => !localFacilities.value.some(lf => lf.id === mf.id));
});

const addFacility = () => {
    if (newFacilityId.value && activeCategoryId.value) {
        const category = props.masterFacilityCategories.find(c => c.id === activeCategoryId.value);
        if (category) {
            const facility = category.facilities.find(f => f.id === newFacilityId.value);
            if (facility && !localFacilities.value.some(f => f.id === facility.id)) {
                localFacilities.value.push({
                    id: facility.id,
                    name: facility.name,
                    icon: facility.icon || 'fa-check',
                    facility_category_id: category.id,
                    category: { name: category.name, icon: category.icon }
                });

                router.post(route('owner.asset.facilities.store', props.asset.slug || props.asset.id), {
                    facility_id: facility.id
                }, {
                    preserveScroll: true,
                    onError: () => {
                        localFacilities.value = localFacilities.value.filter(f => f.id !== facility.id);
                        alert('Gagal menambahkan fasilitas.');
                    }
                });
            }
        }
        newFacilityId.value = '';
        showAddFacilityModal.value = false;
    }
};

const removeFacility = (id) => {
    const previousState = [...localFacilities.value];
    localFacilities.value = localFacilities.value.filter(f => f.id !== id);

    router.delete(route('owner.asset.facilities.destroy', [props.asset.slug || props.asset.id, id]), {
        preserveScroll: true,
        onError: () => {
            localFacilities.value = previousState;
            alert('Gagal menghapus fasilitas.');
        }
    });
};

// --- NEARBY PLACES LOGIC ---
const categoryLabels = {
    health: 'Fasilitas Kesehatan',
    public_transport: 'Transportasi Publik',
    shopping: 'Pusat Perbelanjaan',
    recreation: 'Tempat Rekreasi',
    food: 'Kuliner',
    religious: 'Tempat Ibadah',
    education: 'Pendidikan',
};

const formatDistance = (km) => {
    if (km < 1) {
        return `${Math.round(km * 1000)} m`;
    }
    return `${km.toFixed(2)} km`;
};

// --- MAP LOGIC ---
const mapContainer = ref(null);
let map = null;

onMounted(() => {
    nextTick(() => {
        if (!props.asset?.latitude || !props.asset?.longitude || !mapContainer.value) return;

        map = L.map(mapContainer.value, {
            zoomControl: false,
        }).setView([props.asset.latitude, props.asset.longitude], 17);
        
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);
        
        // Custom Pin matching the Create Asset page
        const forRentSvgStr = `<svg fill="#0A2540" viewBox="0 0 485 485" class="w-5 h-5">
            <path d="M485,60H295.969V0H189.031v60H0v278.879h189.031V485h106.938V338.879H485V60z"/>
        </svg>`;

        const customIcon = L.divIcon({
            className: 'custom-leaflet-pin',
            html: `
                <div style="position: relative; width: 44px; height: 44px;">
                    <div style="width: 44px; height: 44px; background-color: #FFC000; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 2px 2px 6px rgba(0,0,0,0.3); position: absolute; left: 0; top: 0;"></div>
                    <div style="width: 22px; height: 22px; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 2; display:flex; align-items:center; justify-content:center;">
                        ${forRentSvgStr}
                    </div>
                </div>
                <div style="width: 24px; height: 8px; background: rgba(0,0,0,0.3); border-radius: 50%; filter: blur(2px); position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);"></div>
            `,
            iconSize: [44, 52],
            iconAnchor: [22, 52]
        });

        L.marker([props.asset.latitude, props.asset.longitude], { icon: customIcon }).addTo(map);
    });
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

</script>

<template>
    <div class="flex flex-col gap-6 animate-in fade-in duration-300">
        <!-- LOKASI ASET -->
        <Card class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <CardContent class="p-5 md:p-6">
            <h2 class="font-bold text-slate-800 text-base mb-4">Lokasi & Wilayah</h2>

            <div class="w-full h-64 bg-slate-100 rounded-xl overflow-hidden relative border border-slate-200 mb-6 z-0 shadow-inner group">
                <div ref="mapContainer" class="w-full h-full relative z-0"></div>
                <div class="absolute top-3 left-3 z-10 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 backdrop-blur text-slate-700 text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-sm border border-slate-100 flex items-center gap-1.5">
                    <MapPin class="w-3 h-3 text-[#FFC000]" /> Koordinat Tersimpan
                </div>
            </div>

            <div class="mb-4 space-y-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 block mb-1">Alamat Lengkap</label>
                    <div class="relative group/input">
                        <input v-model="form.address" type="text" class="w-full border border-slate-200 bg-slate-50 hover:bg-white focus:bg-white hover:border-slate-300 rounded-lg focus:ring-4 focus:ring-[#FFC000]/10 focus:border-[#FFC000] focus:outline-none transition-all px-4 py-2.5 pr-10 text-slate-800 font-semibold shadow-sm" placeholder="Jalan, No, RT/RW" />
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-300 group-hover/input:text-[#FFC000] transition-colors">
                            <Pencil class="w-4 h-4" />
                        </div>
                    </div>
                    <div v-if="form.errors.address" class="text-xs text-rose-500 mt-1">{{ form.errors.address }}</div>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500 block mb-1">Kode Pos</label>
                    <div class="relative group/input">
                        <input v-model="form.postal_code" type="text" class="w-full border border-slate-200 bg-slate-50 hover:bg-white focus:bg-white hover:border-slate-300 rounded-lg focus:ring-4 focus:ring-[#FFC000]/10 focus:border-[#FFC000] focus:outline-none transition-all px-4 py-2.5 pr-10 text-slate-800 font-semibold shadow-sm" placeholder="Kode Pos" />
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-300 group-hover/input:text-[#FFC000] transition-colors">
                            <Pencil class="w-4 h-4" />
                        </div>
                    </div>
                    <div v-if="form.errors.postal_code" class="text-xs text-rose-500 mt-1">{{ form.errors.postal_code }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-y-4 gap-x-4 text-sm mb-4">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Provinsi</span>
                    <span class="font-semibold text-slate-700">{{ asset.province?.name || '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Kota/Kabupaten</span>
                    <span class="font-semibold text-slate-700">{{ asset.city?.name || '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Kecamatan</span>
                    <span class="font-semibold text-slate-700">{{ asset.district?.name || '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Kelurahan/Desa</span>
                    <span class="font-semibold text-slate-700">{{ asset.village?.name || '-' }}</span>
                </div>
            </div>

            <!-- LOKASI SEKITAR (NEARBY PLACES) -->
            <div v-if="Object.keys(props.nearbyPlaces || {}).length > 0" class="mt-8">
                <h4 class="text-[18px] font-bold text-[#222222] mb-5">Jarak ke fasilitas publik</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                    <div v-for="(places, category) in props.nearbyPlaces" :key="category" class="flex flex-col gap-2.5 min-w-0">
                        <h5 class="text-[15px] font-semibold text-gray-800">{{ categoryLabels[category] || category }}</h5>
                        <ul class="flex flex-col gap-2 pl-3">
                            <li v-for="place in places" :key="place.name" class="flex items-start gap-2.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0 mt-[7px]"></div>
                                <div class="flex-1 min-w-0 flex justify-between items-start gap-2">
                                    <span class="text-[15px] text-gray-600 leading-relaxed flex-1 min-w-0 break-words pr-2">{{ place.name }}</span>
                                    <span class="text-[15px] text-gray-800 whitespace-nowrap shrink-0">{{ formatDistance(place.distance) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            </CardContent>
        </Card>

        <!-- FASILITAS ASET -->
        <Card class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden h-fit">
            <CardHeader class="border-b border-slate-100 bg-white px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 space-y-0">
                <CardTitle class="font-bold text-slate-900 text-base">Fasilitas Aset ({{ localFacilities.length }})</CardTitle>
                <button @click="showAddCategory = true" class="text-xs font-bold text-slate-900 bg-[#FFC000] hover:bg-[#FFC000]/90 px-3 py-1.5 rounded-lg transition shadow-sm flex items-center justify-center gap-1.5 w-full sm:w-auto !mt-0">
                    <FolderPlus class="w-4 h-4" /> Tambah Kategori
                </button>
            </CardHeader>

            <CardContent class="p-5 md:p-6 space-y-8">
                <!-- Modal/Form Add Category -->
                <div v-if="showAddCategory" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row items-center gap-3 animate-in fade-in zoom-in-95 duration-200">
                    <select v-model="selectedCategoryId" class="text-sm font-semibold border-slate-300 rounded-lg px-3 py-2 w-full sm:w-auto flex-1 focus:ring-[#FFC000] focus:border-[#FFC000] transition">
                        <option value="" disabled>Pilih Kategori Fasilitas</option>
                        <option v-for="cat in availableCategoriesToAdd" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button @click="addCategory" :disabled="!selectedCategoryId" :class="selectedCategoryId ? 'bg-[#FFC000] hover:bg-[#e5ac00] text-slate-900' : 'bg-slate-300 text-slate-500 cursor-not-allowed'" class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">Tambah</button>
                        <button @click="showAddCategory = false" class="flex-1 sm:flex-none bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold transition">Batal</button>
                    </div>
                </div>

                <!-- Loop per Category -->
                <div v-for="(group, categoryId) in groupedFacilities" :key="categoryId" class="space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-2">
                        <div class="w-6 h-6 rounded bg-[#FFC000]/20 text-yellow-700 flex items-center justify-center">
                            <AppIcon :iconClass="group.icon || 'fa-list'"  />
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm">{{ group.name }}</h4>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ group.facilities.length }} fasilitas</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div v-for="fac in group.facilities" :key="fac.id" class="aspect-square rounded-lg border border-slate-200 relative group shadow-sm bg-white flex flex-col items-center justify-center p-3 text-center transition hover:border-slate-300">
                            <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 mb-2 flex items-center justify-center border border-slate-100">
                                <AppIcon :iconClass="fac.icon || 'fa-check'"  />
                            </div>
                            <span class="text-[10px] font-bold text-slate-600 line-clamp-2 leading-tight">{{ fac.name }}</span>
                            <button @click="removeFacility(fac.id)" class="absolute -top-1 right-2 w-5 h-5 bg-rose-500 text-white rounded-full text-[10px] flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-rose-600" title="Hapus Fasilitas">
                                <X class="w-3 h-3" />
                            </button>
                        </div>
                        <button @click="openAddFacilityModal(group.id)" class="aspect-square rounded-lg border-2 border-dashed border-slate-300 hover:border-[#FFC000] hover:bg-yellow-50 bg-slate-50/50 flex flex-col items-center justify-center gap-2 transition group shadow-sm">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-yellow-600 group-hover:scale-110 transition-transform">
                                <Plus class="text-lg w-5 h-5" />
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 group-hover:text-yellow-700 text-center px-2">Tambah<br>Fasilitas</span>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="Object.keys(groupedFacilities).length === 0 && !showAddCategory" class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-4 border border-slate-100">
                        <Layers class="text-3xl w-8 h-8" />
                    </div>
                    <h3 class="font-extrabold text-slate-900 mb-1">Belum Ada Kategori Fasilitas</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto mb-4">Aset tanpa fasilitas yang lengkap mungkin kurang diminati penyewa. Tambahkan kategori fasilitas sekarang.</p>
                    <button @click="showAddCategory = true" class="text-xs font-bold text-slate-900 bg-[#FFC000] hover:bg-[#e5ac00] px-4 py-2 rounded-lg transition shadow-sm inline-flex items-center gap-1.5">
                        <Plus class="w-4 h-4" /> Tambah Kategori
                    </button>
                </div>
            </CardContent>
        </Card>

        <!-- MODAL TAMBAH FASILITAS -->
        <div v-if="showAddFacilityModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Tambah Fasilitas Baru</h3>
                    <div class="mb-5">
                        <label class="text-xs font-bold text-slate-500 block mb-1">Pilih Fasilitas</label>
                        <select v-model="newFacilityId" class="text-sm font-semibold text-slate-700 border border-slate-300 focus:border-[#FFC000] focus:ring-[#FFC000] rounded-lg px-3 py-2 w-full transition">
                            <option value="" disabled>-- Pilih Fasilitas --</option>
                            <option v-for="fac in availableFacilitiesForActiveCategory" :key="fac.id" :value="fac.id">
                                {{ fac.name }}
                            </option>
                        </select>
                        <p v-if="availableFacilitiesForActiveCategory.length === 0" class="text-xs text-rose-500 mt-1">Semua fasilitas di kategori ini sudah ditambahkan.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="showAddFacilityModal = false" class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition text-sm">
                            Batal
                        </button>
                        <button @click="addFacility" :disabled="!newFacilityId" :class="newFacilityId ? 'bg-[#FFC000] hover:bg-[#e5ac00] text-slate-900 shadow-md' : 'bg-slate-300 text-slate-500 cursor-not-allowed'" class="flex-1 px-4 py-2 font-bold rounded-lg transition text-sm">
                            Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
