function sortTable(n, id) {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById(id);
    switching = true;
    // Define a direção de ordenação inicial
    dir = "asc"; 
    // Realiza o loop até que nenhuma troca seja feita
    while (switching) {
        switching = false;
        rows = table.rows;
        // Loop por todas as linhas da tabela (exceto o cabeçalho)
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            // Obtém os dois elementos que serão comparados
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            // Verifica se as duas linhas devem ser trocadas de acordo com a direção ascendente ou descendente
            if (dir == "asc") {
                if (Number(x.innerHTML) > Number(y.innerHTML)) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                const cleanX = x.innerHTML.replace(/[^\d.,-]/g, '').replace(',', '.');
                const cleanY = y.innerHTML.replace(/[^\d.,-]/g, '').replace(',', '.');

                if (Number(cleanX) < Number(cleanY)) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            // Se uma troca deve ser feita, realiza a troca e marca que uma troca foi feita
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            // Cada vez que uma troca é feita, incrementa a contagem de trocas
            switchcount++; 
        } else {
            // Se nenhuma troca foi feita e a direção é "asc", define a direção como "desc" e reinicia o loop
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}