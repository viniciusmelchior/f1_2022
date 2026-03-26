function sortTable(n, id) {
    const table = document.getElementById(id);
    const tbody = table.tBodies[0] || table;
    const rows = Array.from(tbody.rows);
    const isAsc = table.dataset.sortOrder === "asc";
    
    // 1. Função para limpar e converter os valores
    const getCellValue = (row) => {
        const cell = row.cells[n].innerText || row.cells[n].textContent;
        const cleanValue = cell.replace(/[^\d.,-]/g, '').replace(',', '.');
        return isNaN(parseFloat(cleanValue)) ? cell.toLowerCase() : parseFloat(cleanValue);
    };

    // 2. Ordenação em memória
    rows.sort((rowA, rowB) => {
        const valA = getCellValue(rowA);
        const valB = getCellValue(rowB);

        if (valA === valB) return 0;
        
        const comparison = valA > valB ? 1 : -1;
        return isAsc ? comparison : -comparison;
    });

    // 3. Inverte o estado para a próxima clicada
    table.dataset.sortOrder = isAsc ? "desc" : "asc";

    // 4. Reinserir as linhas e RESETAR a numeração da primeira coluna
    const fragment = document.createDocumentFragment();
    rows.forEach((row, index) => {
        // Esta linha abaixo garante que a primeira coluna (index 0) 
        // sempre exiba a posição atual, independente do dado original
        row.cells[0].innerText = index + 1; 
        
        fragment.appendChild(row);
    });
    tbody.appendChild(fragment);
}