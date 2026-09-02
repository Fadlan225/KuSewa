const fs = require('fs');
const path = require('path');

const stepsDir = path.join(__dirname, 'resources/js/Pages/owner/Asset/Create');

const files = fs.readdirSync(stepsDir).filter(f => f.startsWith('Step') && f.endsWith('.vue'));

files.forEach(file => {
    let content = fs.readFileSync(path.join(stepsDir, file), 'utf-8');
    
    // Add imports
    if (!content.includes('useAssetCreateStore')) {
        content = content.replace('<script setup>', `<script setup>
import { useAssetCreateStore } from '@/Stores/useAssetCreateStore';
import { storeToRefs } from 'pinia';`);
    }

    // Replace defineProps and add store usage
    // We'll replace the existing defineProps and emit definitions
    content = content.replace(/const props = defineProps\([^)]+\);/s, (match) => {
        // Keep the original match commented out just in case
        return `// ${match.replace(/\n/g, ' ')}\nconst store = useAssetCreateStore();\nconst { form, assetTypeDetails, isLoadingTypeDetails, selectedAssetTypeName: assetTypeName, allowUnits } = storeToRefs(store);\nconst { tambahUnit, hapusUnit, toggleFasilitas, toggleUnitFasilitas, addFaq, removeFaq, addPolicy, removePolicy, tambahUnitKategoriFoto, hapusUnitKategoriFoto, handleUnitFileUpload, hapusUnitFoto, handleUnitThumbnailUpload, hapusUnitThumbnail, tambahKategoriFoto, hapusKategoriFoto, handleFileUpload, hapusFoto, handleThumbnailUpload, hapusThumbnail } = store;`;
    });

    // Remove `const form = props.form;`
    content = content.replace(/const form = props\.form;\n?/g, '');
    
    // Some files might use `props.assetTypeDetails` or `props.assetTypeName`
    content = content.replace(/props\.assetTypeDetails/g, 'assetTypeDetails.value');
    content = content.replace(/props\.assetTypeName/g, 'assetTypeName.value');
    content = content.replace(/props\.isLoading/g, 'isLoadingTypeDetails.value');
    content = content.replace(/props\.allowUnits/g, 'allowUnits.value');
    content = content.replace(/props\.categories/g, 'store.context.categories');
    content = content.replace(/props\.banks/g, 'store.context.existingBank'); // wait, banks is passed directly? Actually index passes `banks` prop.
    
    // Emits are usually defined like: const emit = defineEmits(['tambahUnit', ...])
    content = content.replace(/const emit = defineEmits\([^)]+\);/s, (match) => {
        return `// ${match.replace(/\n/g, ' ')}`;
    });

    // Replace emit('event_name', args...) with action_name(args...)
    content = content.replace(/emit\(\s*['"]([^'"]+)['"]\s*(?:,\s*([^)]+))?\)/g, (match, eventName, args) => {
        if (args) {
            return `${eventName}(${args})`;
        }
        return `${eventName}()`;
    });

    fs.writeFileSync(path.join(stepsDir, file), content);
});

console.log("Steps updated successfully.");
