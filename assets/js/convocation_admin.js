document.addEventListener('DOMContentLoaded', function() {
    const teamSelect = document.getElementById('team_select');
    const playersContainer = document.getElementById('playersContainer');
    const checkboxList = document.getElementById('playersCheckboxList');
    const recapBody = document.getElementById('recapBody');
    
    let currentPlayers = [];

    if (!teamSelect) return;

    teamSelect.addEventListener('change', function() {
        const teamId = this.value;
        if (!teamId) {
            playersContainer.style.display = 'none';
            return;
        }

        // Appel AJAX
        fetch('api/get_team_players.php?team_id=' + teamId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    currentPlayers = data.players;
                    renderPlayers();
                    playersContainer.style.display = 'block';
                } else {
                    alert("Erreur: " + data.error);
                }
            })
            .catch(err => console.error("Fetch error:", err));
    });

    function renderPlayers() {
        checkboxList.innerHTML = '';
        currentPlayers.forEach((p, index) => {
            const div = document.createElement('div');
            div.className = 'player-item';
            
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'absent_players[]';
            checkbox.value = p.id;
            checkbox.id = 'player_' + p.id;
            checkbox.dataset.index = index;
            // Coché si is_absent est vrai
            if(p.is_absent) checkbox.checked = true;
            
            checkbox.addEventListener('change', function() {
                currentPlayers[this.dataset.index].is_absent = this.checked;
                updateRecap();
            });

            const label = document.createElement('label');
            label.htmlFor = 'player_' + p.id;
            label.textContent = ' ' + p.first_name + ' ' + p.initial_name + '.';
            label.style.cursor = 'pointer';

            div.appendChild(checkbox);
            div.appendChild(label);
            checkboxList.appendChild(div);
        });
        
        updateRecap();
    }

    function updateRecap() {
        recapBody.innerHTML = '';
        currentPlayers.forEach(p => {
            const tr = document.createElement('tr');
            
            const tdName = document.createElement('td');
            tdName.textContent = p.first_name + ' ' + p.initial_name + '.';
            
            const tdStatus = document.createElement('td');
            if (p.is_absent) {
                tdStatus.innerHTML = '<span class="text-danger">Absent</span>';
            } else {
                tdStatus.innerHTML = '<span class="text-success">Présent</span>';
            }

            tr.appendChild(tdName);
            tr.appendChild(tdStatus);
            recapBody.appendChild(tr);
        });
    }
});
