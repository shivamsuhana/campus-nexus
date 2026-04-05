/**
 * CampusNexus — Image Upload Preview
 */
function initImagePreview(inputId, previewId, areaId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const area = document.getElementById(areaId);
    if (!input || !preview) return;

    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            showNotification('File size must be less than 5MB', 'error');
            input.value = '';
            return;
        }
        if (!file.type.startsWith('image/')) {
            showNotification('Please select an image file', 'error');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (event) => {
            preview.src = event.target.result;
            preview.style.display = 'block';
            if (area) {
                area.querySelector('i').style.display = 'none';
                area.querySelector('p').textContent = file.name;
            }
        };
        reader.readAsDataURL(file);
    });

    // Drag and drop
    if (area) {
        ['dragenter', 'dragover'].forEach(evt => {
            area.addEventListener(evt, (e) => { e.preventDefault(); area.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            area.addEventListener(evt, (e) => { e.preventDefault(); area.classList.remove('dragover'); });
        });
        area.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length) { input.files = files; input.dispatchEvent(new Event('change')); }
        });
    }
}
