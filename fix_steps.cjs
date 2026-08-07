const fs = require('fs');

const log = fs.readFileSync('C:/Users/Fadla/.gemini/antigravity-ide/brain/73039c5c-b768-4e36-b63d-0fd981f547e1/.system_generated/logs/transcript_full.jsonl', 'utf8');

function fixStep(stepNumber, startStrTag, endStrTag, fileDest) {
    let idx = log.lastIndexOf(startStrTag); // get the latest one
    if (idx > -1) {
        let startStr = log.substring(idx);
        let endIdx = startStr.indexOf(endStrTag);
        if (endIdx > -1) {
            let htmlStr = startStr.substring(0, endIdx);
            
            // It's a JSON string fragment, unescape it
            htmlStr = htmlStr.replace(/\\n/g, '\n').replace(/\\"/g, '"').replace(/\\\\/g, '\\');
            
            let content = fs.readFileSync(fileDest, 'utf8');
            let tplStart = content.indexOf('<template>');
            if (tplStart > -1) {
                let script = content.substring(0, tplStart);
                let finalContent = script + '<template>\n' + htmlStr + '\n</template>';
                fs.writeFileSync(fileDest, finalContent);
                console.log('Fixed ' + fileDest);
            }
        }
    }
}

fixStep(2, '<!-- STEP 2: LOKASI & FASILITAS -->', '<!-- STEP 3: HARGA & FOTO', 'resources/js/Pages/owner/Asset/Create/Step2.vue');
fixStep(3, '<!-- STEP 3: HARGA & FOTO', '<!-- POP-UP SUCCESS MODAL', 'resources/js/Pages/owner/Asset/Create/Step3.vue');
