
document.addEventListener('DOMContentLoaded', () => {

    // --- ELEMEN DOM ---
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const speciesGrid = document.getElementById('species-grid');
    const noResultsMessage = document.getElementById('no-results');

    // --- FUNGSI ---

    function getStatusText(status) {
        const statusMap = {
            'endangered': 'Terancam Punah',
            'vulnerable': 'Rentan',
            'least-concern': 'Berisiko Rendah'
        };
        return statusMap[status] || 'Tidak Diketahui';
    }

    function getStatusClass(status) {
        return `status-${status}`;
    }

    function renderSpecies(data) {
        speciesGrid.innerHTML = '';

        if (data.length === 0) {
            noResultsMessage.style.display = 'block';
        } else {
            noResultsMessage.style.display = 'none';
        }

        data.forEach(species => {
            const card = document.createElement('div');
            card.className = 'species-card';
            // Perhatikan: nama kolom dari database (nama_umum, nama_ilmiah, dll)
            card.innerHTML = `
                <img src="${species.gambar_url}" alt="${species.nama_umum}" class="species-image">
                <div class="species-info">
                    <div class="species-name">${species.nama_umum}</div>
                    <div class="species-scientific">${species.nama_ilmiah}</div>
                    <span class="species-status ${getStatusClass(species.status_konservasi)}">
                        ${getStatusText(species.status_konservasi)}
                    </span>
                </div>
            `;
            speciesGrid.appendChild(card);
        });
    }

    // Fungsi BARU untuk mengambil data dari server
    async function fetchAndRenderSpecies() {
        const searchTerm = searchInput.value;
        const category = categoryFilter.value;
        const status = statusFilter.value;

        // Buat URL dengan parameter query
        const url = `api_search.php?search=${encodeURIComponent(searchTerm)}&category=${category}&status=${status}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            renderSpecies(data);
        } catch (error) {
            console.error('Fetch error:', error);
            speciesGrid.innerHTML = `<p style="color: red; text-align: center;">Gagal memuat data dari server.</p>`;
        }
    }

    // --- EVENT LISTENERS ---

    searchInput.addEventListener('input', fetchAndRenderSpecies);
    categoryFilter.addEventListener('change', fetchAndRenderSpecies);
    statusFilter.addEventListener('change', fetchAndRenderSpecies);

    // --- INISIALISASI ---
  
    fetchAndRenderSpecies();
});