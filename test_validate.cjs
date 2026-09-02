const fs = require('fs');
const data = JSON.parse(fs.readFileSync('api_response.json', 'utf8'));
const assetTypeDetails = { value: data };
const form = { facility_ids: [] };

const validateStep8 = () => { // Fasilitas Aset
    const errors = {};
    if (assetTypeDetails.value?.mandatory_facility_categories?.length > 0) {
        let availableAssetFacilities = [...(assetTypeDetails.value.facilities || [])];
        
        const isLuarOrBersamaSelected = availableAssetFacilities.some(f => 
            (f.name === 'Kamar Mandi Luar' || f.name === 'Kamar Mandi Bersama') && 
            form.facility_ids.includes(f.id)
        );
        if (isLuarOrBersamaSelected) {
            const perabotFacilities = (assetTypeDetails.value.unit_facilities || [])
                .filter(f => f.category?.name === 'Perabot Kamar Mandi');
            availableAssetFacilities = availableAssetFacilities.concat(perabotFacilities);
        }

        for (const cat of assetTypeDetails.value.mandatory_facility_categories) {
            const facilitiesInCat = availableAssetFacilities.filter(f => f.category?.name === cat.name || f.facility_category_id === cat.id);
            if (facilitiesInCat.length === 0) continue;
            
            const hasSelected = facilitiesInCat.some(f => form.facility_ids.includes(f.id));
            if (!hasSelected) {
                errors[`facility_dasar_${cat.id}`] = `Pilih minimal 1 fasilitas dari kategori ${cat.name}.`;
            }
        }
    }
    return errors;
};

console.log(validateStep8());
