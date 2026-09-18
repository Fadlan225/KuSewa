const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(dirPath);
    });
}

const vueFiles = [];
walkDir('.', (filePath) => {
    if (filePath.endsWith('.vue')) {
        vueFiles.push(filePath);
    }
});

let updatedFiles = 0;

vueFiles.forEach(file => {
    let original = fs.readFileSync(file, 'utf8');
    let modified = original;

    let lines = modified.split('\n');
    let changed = false;
    
    for (let i = 0; i < lines.length; i++) {
        let line = lines[i];
        
        // Match custom checkmarks
        if (line.includes("{'bg-[#0A2540] border-[#0A2540]':") || line.includes("{'bg-[#0A2540]':")) {
            lines[i] = line.replace(/bg-\[#0A2540\] border-\[#0A2540\]/g, 'bg-[#FFC000] border-[#FFC000]')
                           .replace(/bg-\[#0A2540\]/g, 'bg-[#FFC000]');
            changed = true;
        }

        // Match native checkboxes
        if (line.includes('type="checkbox"') || line.includes("type='checkbox'")) {
            if (line.includes('text-[#0A2540]')) {
                lines[i] = lines[i].replace(/text-\[#0A2540\]/g, 'text-[#FFC000]');
                changed = true;
            }
            if (line.includes('focus:ring-[#0A2540]')) {
                lines[i] = lines[i].replace(/focus:ring-\[#0A2540\]/g, 'focus:ring-[#FFC000]');
                changed = true;
            }
        }
        
        // Sometimes the class is on the same line as <input but type="checkbox" is on another line
        if (line.includes('<input') && (line.includes('v-model') || line.includes(':checked')) && line.includes('text-[#0A2540]') && line.includes('focus:ring')) {
            lines[i] = lines[i].replace(/text-\[#0A2540\]/g, 'text-[#FFC000]')
                               .replace(/focus:ring-\[#0A2540\]/g, 'focus:ring-[#FFC000]');
            changed = true;
        }
    }
    
    if (changed) {
        fs.writeFileSync(file, lines.join('\n'));
        console.log(`Updated: ${file}`);
        updatedFiles++;
    }
});

console.log(`Total files updated: ${updatedFiles}`);
