const fs = require('fs');
const log = fs.readFileSync('C:/Users/Fadla/.gemini/antigravity-ide/brain/73039c5c-b768-4e36-b63d-0fd981f547e1/.system_generated/logs/transcript_full.jsonl', 'utf8');

const targetStr = '"TargetContent": "                <!-- STEP 1: INFORMASI UTAMA -->';
let startIdx = log.indexOf(targetStr);
if (startIdx !== -1) {
    let endIdx = log.indexOf('"TargetFile":', startIdx);
    if (endIdx !== -1) {
        let contentStr = log.substring(startIdx + 17, endIdx);
        contentStr = contentStr.trim().replace(/,$/, '');
        let content = JSON.parse(contentStr);
        fs.writeFileSync('recovered_html.txt', content);
        console.log('Saved to recovered_html.txt');
    } else { console.log('End not found'); }
} else { console.log('Start not found'); }
