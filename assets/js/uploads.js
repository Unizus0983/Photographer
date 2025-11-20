document.addEventListener('DOMContentLoaded', function () {
    // === GESTION UPLOAD FICHIERS ===
    const fileInput = document.getElementById('fileInput');
    const fileNameElement = document.querySelector('.fileName');
    const cancelButton = document.getElementById('cancelFile');

    if (fileInput && fileNameElement && cancelButton) {
        fileInput.addEventListener('change', handleFileSelect);
        cancelButton.addEventListener('click', handleCancelFile);
    }

    function handleFileSelect() {
        const file = this.files[0];
        if (file) {
            fileNameElement.textContent = `Nom du fichier : ${file.name}`;
            cancelButton.style.display = 'inline-block';
        } else {
            resetFileInput();
        }
    }

    function handleCancelFile(e) {
        e.preventDefault();
        resetFileInput();
    }

    function resetFileInput() {
        if (fileInput && fileNameElement && cancelButton) {
            fileInput.value = '';
            fileNameElement.textContent = 'Aucun fichier sélectionné';
            cancelButton.style.display = 'none';
        }
    }
});
