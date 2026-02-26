const input = document.getElementById('addressInput');
const suggestions = document.getElementById('suggestions');

input.addEventListener('input', async () => {
    const query = input.value.trim();
    if (query.length < 3) {
        suggestions.innerHTML = '';
        return;
    }
    try {
        const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`);
        const data = await response.json();
        suggestions.innerHTML = '';
        data.features.forEach(feature => {
            const li = document.createElement('li');
            li.textContent = feature.properties.label;
            li.addEventListener('click', () => {
                input.value = feature.properties.label;
                suggestions.innerHTML = '';
            });
            suggestions.appendChild(li);
        });
    } catch (error) {
        console.error('Erreur API BAN:', error);
    }
});