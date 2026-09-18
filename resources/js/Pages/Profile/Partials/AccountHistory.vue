<script setup>
import { computed } from 'vue';
import { LogIn, KeyRound } from 'lucide-vue-next';
import EmptyActivityIllustration from '@/Components/ui/Icons/EmptyActivityIllustration.vue';

const props = defineProps({
    accountActivities: {
        type: Array,
        default: () => []
    }
});

const groupedActivities = computed(() => {
    const groups = {};

    props.accountActivities.forEach(activity => {
        let title = '';
        let icon = null;

        if (activity.type === 'login') {
            title = 'Masuk Akun';
            icon = LogIn;
        } else if (activity.type === 'password_change') {
            title = 'Kata Sandi Diperbarui';
            icon = KeyRound;
        } else {
            title = 'Aktivitas Akun';
            icon = LogIn;
        }

        // Format location
        let locationStr = 'Lokasi tidak diketahui';
        if (activity.province && activity.city) {
            locationStr = `Indonesia, ${activity.province.name}, ${activity.city.name}`;
        } else if (activity.province) {
            locationStr = `Indonesia, ${activity.province.name}`;
        } else if (activity.city) {
            locationStr = `Indonesia, ${activity.city.name}`;
        }

        const date = new Date(activity.created_at);
        const dateFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric', day: 'numeric' });
        const dateString = dateFormatter.format(date);

        const timeFormatter = new Intl.DateTimeFormat('id-ID', { 
            hour: '2-digit', minute: '2-digit' 
        });
        const timeString = timeFormatter.format(date).replace('pukul', '').trim();

        if (!groups[dateString]) {
            groups[dateString] = [];
        }

        const displayDeviceName = activity.device_name || activity.device_type || 'Perangkat Tidak Dikenal';
        let displayOsBrowser = '';
        if (activity.browser || activity.os) {
            displayOsBrowser = `(${activity.browser || 'Browser'}${activity.os ? ', ' + activity.os : ''})`;
        }

        groups[dateString].push({
            ...activity,
            title,
            icon,
            locationStr,
            timeString,
            displayDeviceName,
            displayOsBrowser
        });
    });

    return groups;
});
</script>

<template>
    <div class="bg-transparent p-0 shadow-none md:bg-white md:p-6 md:shadow-md md:rounded-md">
        <h2 class="hidden md:block text-xl font-bold text-[#0A2540] mb-6">Riwayat Akun</h2>

        <div v-if="Object.keys(groupedActivities).length === 0" class="flex flex-col items-center justify-center py-10">
            <EmptyActivityIllustration class="w-48 sm:w-64 h-auto opacity-70 mb-6" />
            <h3 class="text-lg font-bold text-[#0A2540] mb-2 text-center">Belum ada aktivitas akun yang tercatat.</h3>
            <p class="text-sm text-gray-500 text-center max-w-sm">Catatan aktivitas akun Anda akan ditampilkan di sini.</p>
        </div>

        <div v-else class="space-y-6">
            <div v-for="(activities, date) in groupedActivities" :key="date">
                <h3 class="text-sm font-bold text-[#0A2540] mb-4 ml-1">{{ date }}</h3>
                
                <div class="relative bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
                    <div 
                        v-for="(activity, index) in activities" 
                        :key="activity.id"
                        class="relative flex gap-4"
                        :class="{ 'mb-6': index !== activities.length - 1 }"
                    >
                        <!-- Timeline Line (only if not the last item) -->
                        <div v-if="index !== activities.length - 1" class="absolute left-4 top-8 bottom-[-24px] w-[1px] bg-gray-200"></div>
                        
                        <!-- Icon -->
                        <div class="relative z-10 flex-shrink-0 mt-0.5 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <component :is="activity.icon" class="w-4 h-4 text-gray-500" />
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-grow min-w-0">
                            <h4 class="text-sm font-semibold text-[#0A2540]">{{ activity.title }}</h4>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                <template v-if="activity.type === 'login'">
                                    Anda masuk ke akun Anda<br>
                                    dari <span class="font-medium text-gray-600">{{ activity.displayDeviceName }}</span>
                                    <span v-if="activity.displayOsBrowser" class="text-gray-400 text-[11px] ml-1">{{ activity.displayOsBrowser }}</span>
                                    di {{ activity.locationStr }}
                                </template>
                                <template v-else-if="activity.type === 'password_change'">
                                    Kata sandi Anda diperbarui<br>
                                    dari <span class="font-medium text-gray-600">{{ activity.displayDeviceName }}</span>
                                    <span v-if="activity.displayOsBrowser" class="text-gray-400 text-[11px] ml-1">{{ activity.displayOsBrowser }}</span>
                                    di {{ activity.locationStr }}
                                </template>
                                <template v-else>
                                    Aktivitas dilakukan dari <span class="font-medium text-gray-600">{{ activity.displayDeviceName }}</span>
                                </template>
                            </p>
                            <div class="text-xs font-medium text-gray-400 mt-1">
                                {{ activity.timeString }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
