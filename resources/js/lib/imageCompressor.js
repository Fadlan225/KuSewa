/**
 * Kompresi gambar menggunakan Canvas API → WebP
 * Resize ke maxWidth maksimal 1280px, kompresi hingga target < maxBytes (default 1MB)
 *
 * @param {File} file - File gambar input
 * @param {number} maxWidth - Lebar maksimal (default 1280)
 * @param {number} quality - Kualitas awal WebP 0-1 (default 0.75)
 * @param {number} maxBytes - Batas ukuran dalam bytes (default 1MB)
 * @returns {Promise<File>} - File terkompresi dalam format WebP
 */
export async function compressImage(file, maxWidth = 1280, quality = 0.75, maxBytes = 1 * 1024 * 1024) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');

                // Hitung dimensi baru (proportional, maks maxWidth)
                let width = img.width;
                let height = img.height;
                if (width > maxWidth) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Coba kompresi dengan penurunan kualitas jika masih terlalu besar
                const tryCompress = (q) => {
                    canvas.toBlob(
                        (blob) => {
                            if (!blob) {
                                reject(new Error('Gagal mengkompresi gambar'));
                                return;
                            }
                            // Jika masih terlalu besar dan kualitas masih bisa diturunkan
                            if (blob.size > maxBytes && q > 0.3) {
                                tryCompress(parseFloat((q - 0.1).toFixed(1)));
                                return;
                            }
                            const compressedFile = new File(
                                [blob],
                                file.name.replace(/\.[^.]+$/, '.webp'),
                                { type: 'image/webp', lastModified: Date.now() }
                            );
                            resolve(compressedFile);
                        },
                        'image/webp',
                        q
                    );
                };

                tryCompress(quality);
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}
