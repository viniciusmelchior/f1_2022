let currentView = 'drivers'; 
let currentData = null;

function renderStandings(items, type) {
    const container = document.getElementById('standings-content');
    if (!items || items.length === 0) {
        container.innerHTML = '<p class="empty-msg">Nenhum dado encontrado.</p>';
        return;
    }

    const maxPts = Math.max(...items.map(i => i.points));
    const leaderPts = items[0].points;

    let html = `<ul class="standings animate-list">`;

    items.forEach((item, index) => {
        const pos = index + 1;
        const diff = index === 0 ? "LÍDER" : (item.points - leaderPts).toFixed(1).replace('.0', '');
        const pct = (item.points / maxPts) * 100;
        const accent = item.color || '#444';

        // const logoHtml = `<div class="logo-container" style="--team-color:${accent}">
        //                     <img src="${window.Laravel.baseUrl}images/${item.short}" class="logo-img" onerror="this.src='imagens/default.png'">
        //                   </div>`;
        const logoHtml = `<div class="logo-container" style="--team-color:${accent}">
                            <img src="${window.Laravel.baseUrl}images/${item.short}" class="logo-img">
                          </div>`;

        let infoHtml = '';
        if (type === 'drivers') {
            infoHtml = `<div class="driver-info">
                            <span class="entry-name">${item.name}</span>
                            <span class="entry-team">${item.team}</span>
                        </div>`;
        } else {
            infoHtml = `<span class="entry-name">${item.name}</span>`;
        }

        html += `
            <li class="row" style="--accent:${accent}">
                <div class="pos-wrapper"><div class="pos">${pos}</div></div>
                <div class="entry">
                    <span class="bar"></span>
                    ${logoHtml}
                    ${infoHtml}
                </div>
                <div class="points">
                    <div class="points-bar"><div class="points-fill" style="width: 0%" data-pct="${pct}"></div></div>
                    <span class="pts-value">${item.points}</span>
                </div>
                <div class="diff">${diff}</div>
            </li>`;
    });

    html += `</ul>`;
    container.innerHTML = html;

    setTimeout(() => {
        container.querySelectorAll('.points-fill').forEach(bar => {
            bar.style.width = bar.dataset.pct + '%';
        });
    }, 50);
}

function updateUIHeader(data) {
    document.getElementById('gp-title').innerText = currentView === 'drivers' ? 'Classificação de Pilotos' : 'Classificação de Equipes';
    document.getElementById('col-name').innerText = currentView === 'drivers' ? 'PILOTO' : 'EQUIPE';
    
    document.getElementById('gp-name').innerText = data.gp_name;
    document.getElementById('gp-city').innerText = data.location;
    document.getElementById('gp-stage').innerText = data.stage;
    // document.getElementById('gp-flag').src = `imagens/${data.flag}`;
    document.getElementById('gp-flag').src = `${window.Laravel.baseUrl}images/${data.flag}`;
}

function updateView(data) {
    currentData = data;
    updateUIHeader(data);
    renderStandings(data[currentView], currentView);
}

async function fetchRaceData(direction) {
    try {
        // Simulação de alteração para teste
        novosDados = []
        const mockData = {...currentData};
        mockData.gp_name = direction === 'next' ? "Próximo GP" : "GP Anterior";
        mockData.stage = "Processando...";
        mockData.flag = "Alemanha.jpg";
        mockData.drivers = [
                {
                    name: 'Vinicius Melchior', 
                    team: 'Melchior Racing', 
                    short: 'rbr', 
                    color: '#FFFFFF', 
                    points: 100
                }
        ];
        mockData.teams = [
            {
                name: 'Melchior Racing',  
                short: 'rbr', 
                color: '#FFFFFF', 
                points: 100
            }
        ];
        updateView(mockData);
    } catch (e) {
        console.error("Erro ao buscar dados", e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof INITIAL_STATE !== 'undefined') {
        updateView(INITIAL_STATE);
    }

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentView = tab.dataset.view;
            updateView(currentData);
        });
    });

    document.getElementById('prev-race').addEventListener('click', () => fetchRaceData('prev'));
    document.getElementById('next-race').addEventListener('click', () => fetchRaceData('next'));
});