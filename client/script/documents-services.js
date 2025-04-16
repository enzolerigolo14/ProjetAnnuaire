function validerRecherche(input) {
    // Validation du champ de recherche si nécessaire
    input.value = input.value.replace(/[^a-zA-Z0-9 éèêëàâäôöûüç'-]/g, '');
}


// --- Gestion de la modale ---
const modal = document.getElementById('upload-modal');
document.getElementById('open-modal').addEventListener('click', () => {
    modal.style.display = 'block';
});
document.querySelector('.close').addEventListener('click', () => {
    modal.style.display = 'none';
});
window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

document.getElementById('documents').addEventListener('change', function() {
    const fileList = document.getElementById('file-list');
    
    if (this.files.length === 0) {
        fileList.innerHTML = 'Aucun fichier sélectionné';
    } else {
        let html = '<ul>';
        for (let i = 0; i < this.files.length; i++) {
            html += `<li>${this.files[i].name}</li>`;
        }
        html += '</ul>';
        fileList.innerHTML = html;
    }
});


const documentsInput = document.getElementById('documents');
const fileList = document.getElementById('file-list');
let filesArray = [];

documentsInput.addEventListener('change', function () {
    filesArray = Array.from(this.files); 
    renderFileList();
});

function renderFileList() {
    if (filesArray.length === 0) {
        fileList.innerHTML = '';
        return;
    }

    let html = '<ul>';
    filesArray.forEach((file, index) => {
        html += `
            <li class="file-item">
                <span>${file.name}</span>
                <span class="delete-icon" onclick="removeFile(${index})">🗑️</span>
            </li>`;
    });
    html += '</ul>';
    fileList.innerHTML = html;

    updateFileInput();
}

function removeFile(index) {
    filesArray.splice(index, 1);
    renderFileList();
}

function updateFileInput() {
    const dataTransfer = new DataTransfer();
    filesArray.forEach(file => dataTransfer.items.add(file));
    documentsInput.files = dataTransfer.files;
}