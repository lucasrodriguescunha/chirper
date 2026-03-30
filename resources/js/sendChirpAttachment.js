/**
 * Atualiza o nome do arquivo selecionado no input de upload.
 *
 * - Quando um arquivo é selecionado, exibe o nome no label
 * - Quando nenhum arquivo está selecionado, esconde o label
 */
document.getElementById('attachment-input').addEventListener('change', function () {
    const label = document.getElementById('file-name');
    if (this.files.length > 0) {
        label.textContent = this.files[0].name;
        label.classList.remove('hidden');
    } else {
        label.classList.add('hidden');
    }
});
