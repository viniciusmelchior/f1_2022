{{-- MELHORIA 20261105: ordenação ao clicar no cabeçalho das tabelas .f1-table (só colunas com .sort-icon).
     Compartilhado por pilotos/show e equipes/show. Usa data-valor da célula quando existir. --}}
<script>
(function () {
    if (window.__perfilOrdenacao) return;
    window.__perfilOrdenacao = true;

    function valor(td) {
        if (!td) return '';
        if (td.dataset.valor !== undefined) {
            const n = parseFloat(td.dataset.valor);
            return isNaN(n) ? td.dataset.valor.toLowerCase() : n;
        }
        const texto = (td.innerText || td.textContent || '').trim();
        const limpo = texto.replace(/[ºª%]/g, '').replace(',', '.');
        const n = parseFloat(limpo);
        return /^-?\d/.test(limpo) && !isNaN(n) ? n : texto.toLowerCase();
    }

    document.addEventListener('click', function (e) {
        const th = e.target.closest('table.f1-table > thead th');
        if (!th || !th.querySelector('.sort-icon')) return;
        const tabela = th.closest('table');
        const tbody = tabela.tBodies[0];
        if (!tbody) return;
        const indice = Array.prototype.indexOf.call(th.parentNode.children, th);
        const asc = !(th.classList.contains('ordenado') && th.dataset.ordem === 'asc');

        tabela.querySelectorAll('thead th.ordenado').forEach(x => x.classList.remove('ordenado'));
        th.classList.add('ordenado');
        th.dataset.ordem = asc ? 'asc' : 'desc';

        const linhas = Array.from(tbody.rows).filter(r => r.cells.length > 1);
        linhas.sort((a, b) => {
            const va = valor(a.cells[indice]), vb = valor(b.cells[indice]);
            // números antes de textos ("-"), em qualquer direção
            if (typeof va !== typeof vb) return typeof va === 'number' ? -1 : 1;
            if (va === vb) return 0;
            return (va > vb ? 1 : -1) * (asc ? 1 : -1);
        });
        linhas.forEach(r => tbody.appendChild(r));
    });
})();
</script>
