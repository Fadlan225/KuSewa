/**
 * scripts/network.js  (ES Module)
 *
 * Otomatis deteksi IP lokal laptop, lalu jalankan:
 *  - php artisan serve --host=0.0.0.0 --port=8080
 *  - npm run dev  (Vite dengan HMR ke IP yang terdeteksi)
 *
 * Digunakan lewat: composer run network
 */

import { networkInterfaces } from 'os';
import { spawn } from 'child_process';

// ─── Deteksi IP lokal aktif (bukan loopback, bukan virtual) ────────────────
function getLocalIP() {
    const nets = networkInterfaces();
    const candidates = [];

    for (const name of Object.keys(nets)) {
        // Lewati adapter virtual umum
        const skip = /loopback|vmware|virtualbox|hyper-v|wsl|vethernet|docker/i;
        if (skip.test(name)) continue;

        for (const net of nets[name]) {
            if (net.family === 'IPv4' && !net.internal) {
                candidates.push({ name, address: net.address });
            }
        }
    }

    if (candidates.length === 0) {
        console.error('[network] ❌  Tidak ada IP lokal yang ditemukan. Pastikan laptop terhubung ke Wi-Fi / LAN.');
        process.exit(1);
    }

    // Pilih yang pertama (biasanya adapter utama)
    return candidates[0];
}

// ─── Spawn proses dengan output diteruskan ke terminal ─────────────────────
function spawnProcess(cmd, args, env = {}) {
    const proc = spawn(cmd, args, {
        stdio: 'inherit',
        shell: true,
        env: { ...process.env, ...env },
        cwd: process.cwd(),
    });

    proc.on('error', (err) => {
        console.error(`[network] ❌  Gagal menjalankan "${cmd}": ${err.message}`);
    });

    return proc;
}

// ─── Main ───────────────────────────────────────────────────────────────────
const { name: adapterName, address: localIP } = getLocalIP();

console.log('');
console.log('╔══════════════════════════════════════════════════════════╗');
console.log('║              KuSewa  —  Network Dev Mode                ║');
console.log('╠══════════════════════════════════════════════════════════╣');
console.log(`║  Adapter   : ${adapterName.padEnd(44)}║`);
console.log(`║  IP Laptop : ${localIP.padEnd(44)}║`);
console.log('╠══════════════════════════════════════════════════════════╣');
console.log(`║  Akses dari HP  : http://${localIP}:8080`.padEnd(61) + '║');
console.log(`║  Vite HMR       : http://${localIP}:5173`.padEnd(61) + '║');
console.log('╚══════════════════════════════════════════════════════════╝');
console.log('');

// Jalankan Artisan serve
const artisan = spawnProcess('php', ['artisan', 'serve', '--host=0.0.0.0', '--port=8080']);

// Jalankan Vite dev server dengan HMR ke IP terdeteksi
const vite = spawnProcess('npm', ['run', 'dev'], {
    VITE_HMR_HOST: localIP,
    VITE_HMR_PORT: '5173',
});

// Matikan keduanya bila salah satu keluar atau Ctrl+C
function killAll() {
    console.log('\n[network] 🛑  Menghentikan semua proses...');
    try { artisan.kill(); } catch (_) {}
    try { vite.kill(); } catch (_) {}
}

process.on('SIGINT', killAll);
process.on('SIGTERM', killAll);

artisan.on('close', (code) => {
    console.log(`[network] artisan serve berhenti (code ${code})`);
    killAll();
    process.exit(code ?? 0);
});

vite.on('close', (code) => {
    console.log(`[network] Vite berhenti (code ${code})`);
    killAll();
    process.exit(code ?? 0);
});
