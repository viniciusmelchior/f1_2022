function sortTable(n, id) {
    const table = document.getElementById(id);
    const tbody = table.tBodies[0] || table;
    const rows = Array.from(tbody.rows);
    const isAsc = table.dataset.sortOrder === "asc";
    
    // 1. Função para limpar e converter os valores
    const getCellValue = (row) => {
        const cell = row.cells[n].innerText || row.cells[n].textContent;
        // Limpa símbolos, trata vírgula decimal e converte para número
        const cleanValue = cell.replace(/[^\d.,-]/g, '').replace(',', '.');
        return isNaN(parseFloat(cleanValue)) ? cell.toLowerCase() : parseFloat(cleanValue);
    };

    // 2. Ordenação em memória (muito mais rápido que Bubble Sort)
    rows.sort((rowA, rowB) => {
        const valA = getCellValue(rowA);
        const valB = getCellValue(rowB);

        if (valA === valB) return 0;
        
        const comparison = valA > valB ? 1 : -1;
        return isAsc ? comparison : -comparison;
    });

    // 3. Inverte o estado para a próxima clicada
    table.dataset.sortOrder = isAsc ? "desc" : "asc";

    // 4. Reinserir as linhas (o navegador otimiza isso automaticamente)
    const fragment = document.createDocumentFragment();
    rows.forEach(row => fragment.appendChild(row));
    tbody.appendChild(fragment);
}