@once
<script>
(() => {
    if (window.CampusCrushImageTools) return;

    function readImage(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('Image illisible'));
            };
            img.src = url;
        });
    }

    function canvasToBlob(canvas, quality) {
        return new Promise((resolve) => {
            canvas.toBlob((blob) => resolve(blob), 'image/jpeg', quality);
        });
    }

    async function compressFile(file, options = {}) {
        if (!file || !file.type || !file.type.startsWith('image/')) return file;

        const maxSide = options.maxSide || 1280;
        const quality = options.quality || 0.74;
        const minSize = options.minSize || 420 * 1024;

        if (file.size <= minSize && file.type === 'image/jpeg') return file;

        const img = await readImage(file);
        const scale = Math.min(1, maxSide / Math.max(img.naturalWidth || img.width, img.naturalHeight || img.height));
        const width = Math.max(1, Math.round((img.naturalWidth || img.width) * scale));
        const height = Math.max(1, Math.round((img.naturalHeight || img.height) * scale));
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d', { alpha: false });
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.drawImage(img, 0, 0, width, height);

        const blob = await canvasToBlob(canvas, quality);
        if (!blob || blob.size >= file.size) return file;

        const baseName = (file.name || options.namePrefix || 'image').replace(/\.[^.]+$/, '');
        return new File([blob], baseName + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
    }

    async function compressInputFiles(input, options = {}) {
        if (!input?.files?.length || typeof DataTransfer === 'undefined') {
            return Array.from(input?.files || []);
        }

        const maxFiles = options.maxFiles || input.files.length;
        const files = Array.from(input.files).slice(0, maxFiles);
        const dataTransfer = new DataTransfer();

        for (const file of files) {
            try {
                dataTransfer.items.add(await compressFile(file, options));
            } catch {
                dataTransfer.items.add(file);
            }
        }

        input.files = dataTransfer.files;
        return Array.from(input.files);
    }

    window.CampusCrushImageTools = { compressFile, compressInputFiles };
})();
</script>
@endonce
