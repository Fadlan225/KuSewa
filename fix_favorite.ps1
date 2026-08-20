$content = Get-Content "resources\js\Pages\Home\Favorite.vue" -Raw

# 1. Hide mobile top navbar
$content = $content.Replace(
    '<div class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">',
    '<div v-if="!isComponent" class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">'
)

# 2. Add Component Header (Title + Sort)
$mobileSearchKategori = '            <!-- MOBILE SEARCH & KATEGORI -->'
$componentHeader = @"
            <!-- DESKTOP COMPONENT HEADER -->
            <div v-if="isComponent" class="flex justify-between items-center mb-4 mt-2">
                <h2 class="text-xl font-bold text-[#1D1D1F]">Favorit</h2>
                
                <div class="relative">
                    <button
                        @click="isSortOpenMobile = !isSortOpenMobile"
                        class="flex items-center gap-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs font-medium text-[#1D1D1F] transition-colors shadow-xs"
                    >
                        <i :class="sortOptions.find(o => o.label === sort)?.icon || 'fa-solid fa-clock-rotate-left'" class="text-slate-500 text-[10px]"></i>
                        {{ sort }}
                        <ChevronDown class="text-slate-400 text-[9px] ml-1 transition-transform" :class="isSortOpenMobile ? 'rotate-180' : ''" />
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div v-if="isSortOpenMobile" class="absolute z-50 right-0 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden">
                            <div class="py-1">
                                <button
                                    v-for="option in sortOptions"
                                    :key="option.label"
                                    @click="selectSort(option.label)"
                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-xs text-left"
                                    :class="sort === option.label ? 'bg-amber-50 text-[#0A2540] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#1D1D1F]'"
                                >
                                    <AppIcon :iconClass="[option.icon, sort === option.label ? 'text-amber-500' : 'text-slate-400']" class="w-4 text-center" />
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- SEARCH & KATEGORI -->
"@
$content = $content.Replace($mobileSearchKategori, $componentHeader)

# 3. Modify Search/Category block to show if isComponent
$content = $content.Replace(
    '<div class="block lg:hidden mb-5 space-y-2.5">',
    '<div class="mb-5 space-y-2.5" :class="isComponent ? '''' : ''block lg:hidden''">'
)

# 4. Hide Sidebar if isComponent
$content = $content.Replace(
    '<aside class="hidden lg:block lg:col-span-3">',
    '<aside v-if="!isComponent" class="hidden lg:block lg:col-span-3">'
)

# 5. Span full if isComponent
$content = $content.Replace(
    '<section class="col-span-12 lg:col-span-9">',
    '<section class="col-span-12" :class="!isComponent ? ''lg:col-span-9'' : ''''">'
)

# 6. Hide existing mobile sort if isComponent
$existingMobileSort = '<div class="flex items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">'
$content = $content.Replace(
    $existingMobileSort,
    '<div v-if="!isComponent" class="flex items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">'
)

Set-Content "resources\js\Pages\Home\Favorite.vue" $content
