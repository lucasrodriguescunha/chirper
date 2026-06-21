const attachmentInput = document.getElementById('attachment-input');
const trigger = document.getElementById('attachment-trigger');
const label = document.getElementById('file-name');
const preview = document.getElementById('attachment-preview');
const previewImg = document.getElementById('attachment-preview-img');
const clearBtn = document.getElementById('attachment-clear');

trigger?.addEventListener('click', () => attachmentInput?.click());

attachmentInput?.addEventListener('change', function () {
    const file = this.files[0];

    if (!file) {
        resetUi();
        return;
    }

    label.textContent = file.name;
    label.classList.remove('hidden');

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});

clearBtn?.addEventListener('click', () => {
    attachmentInput.value = '';
    resetUi();
});

function resetUi() {
    label.textContent = '';
    label.classList.add('hidden');
    previewImg.src = '';
    preview.classList.add('hidden');
}
