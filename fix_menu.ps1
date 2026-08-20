$content = Get-Content "resources\js\Components\ProfileMenu.vue" -Raw

$pusat = @"
        <!-- Grup Menu 'Pusat Aktivitas' (Hanya tampil di Desktop karena isinya hanya rute desktop) -->
        <div class="hidden md:block bg-white p-6 shadow-md rounded-2xl space-y-2">
            <h3 class="text-base sm:text-lg font-bold text-[#0A2540] mb-2">Pusat Aktivitas</h3>
            <div class="border-t border-[#F8F9FA] mb-2"></div>

            <template v-for="(item, index) in activityMenuItems" :key="index">
                <!-- Desktop Link (jika routeDesktop ada) -->
                <Link
                    v-if="item.routeDesktop"
                    :href="item.routeDesktop"
                    :class="[
                        'hidden md:flex items-center justify-between py-3 border-b border-gray-50 px-3 rounded-xl transition-colors duration-150 group relative',
                        checkIsActive(item) ? '' : 'hover:bg-[#F8F9FA]'
                    ]"
                >
                    <div v-if="checkIsActive(item)" class="hidden md:block absolute left-0 top-0 bottom-0 w-1 bg-[#FFC000]"></div>
                    <div class="flex items-center space-x-4">
                        <AppIcon :iconClass="[item.icon, 'text-lg w-6 text-center transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D]' : 'text-[#6C757D] group-hover:text-[#FFC000]']" />
                        <span :class="['text-sm sm:text-base font-semibold transition-colors', checkIsActive(item) ? 'md:text-[#FFC000] text-[#0A2540]' : 'text-[#0A2540] group-hover:text-[#FFC000]']">{{ item.label }}</span>
                    </div>
                    <ChevronRight :class="['text-sm transition-all duration-200', checkIsActive(item) ? 'md:text-[#FFC000] text-[#6C757D] md:translate-x-1' : 'text-[#6C757D] group-hover:translate-x-1 group-hover:text-[#FFC000]']" />
                </Link>
            </template>
        </div>
"@

$content = $content.Replace($pusat + "`r`n`r`n", "")
$content = $content.Replace($pusat + "`n`n", "")
$content = $content.Replace("        <!-- Grup Menu 'Pengaturan' -->", $pusat + "`r`n`r`n        <!-- Grup Menu 'Pengaturan' -->")

Set-Content "resources\js\Components\ProfileMenu.vue" $content
