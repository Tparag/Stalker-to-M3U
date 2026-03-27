<?php
include("_inc.configs.php");
if(empty(mac_getallChannels())){ header("Location: admin.php"); exit(); }

// Get genres for category filter
$genres_path = $APP_CONFIG['DATA_FOLDER']."/axGenres.enc";
$genres = array();
if(file_exists($genres_path)) {
    $genres_data = @file_get_contents($genres_path);
    if(!empty($genres_data)) {
        $genres = @json_decode($genres_data, true);
    }
}
$genre_filter = app_genre_filter("get");
if(!empty($genre_filter)) {
    $filtered_genres = array();
    foreach($genres as $gid => $gtitle) {
        if(in_array($gid, $genre_filter)) {
            $filtered_genres[$gid] = $gtitle;
        }
    }
    $genres = $filtered_genres;
}
app_recordalogs("VISIT", "Homepage loaded - Channel list displayed");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Home - <?php print($APP_CONFIG['APP_NAME'] ?? 'Live TV'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes"/>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --card-hover: #334155;
            --primary: #3b82f6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-id: #ef4444; /* Kept red for ID visibility */
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main { flex: 1; }
        footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 1.25rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        .container { margin-bottom: 5rem; } /* Space for fixed footer */
        
        /* Header */
        header {
            background-color: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 1rem 0;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }
        .header-content { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .logo-text { font-size: 1.5rem; font-weight: 700; color: var(--primary); text-decoration: none; letter-spacing: 0.5px; }
        
        /* Header Actions */
        .header-actions { display: flex; align-items: center; gap: 1rem; flex: 1; justify-content: flex-end; }
        .search-container { position: relative; flex: 1; max-width: 400px; }
        .search-container i { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        #searchInput {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border-radius: 9999px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: var(--text-main);
            outline: none;
            transition: all 0.3s;
        }
        #searchInput:focus { border-color: var(--primary); background: rgba(255,255,255,0.1); }
        
        /* Category Dropdown */
        .category-select {
            padding: 0.65rem 1rem;
            border-radius: 9999px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-main);
            outline: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            min-width: 160px;
        }
        .category-select:focus { border-color: var(--primary); background: rgba(255,255,255,0.1); }
        .category-select option {
            background-color: var(--bg-color); /* Match base dark background */
            color: var(--text-main);
            padding: 10px;
        }
        
        .stats { color: var(--text-muted); font-size: 0.9rem; font-weight: 500;}
        
        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 18px;
            padding-bottom: 80px;
            margin-top: 1.5rem;
        }
        
        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.3s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
            background: var(--card-hover);
        }
        .btn-admin {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s;
            flex-shrink: 0;
        }
        .btn-admin:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        /* Responsive Helpers */
        .mobile-admin-btn { display: none; }
        .desktop-admin-btn { display: flex; }
        @media (max-width: 768px) {
            .mobile-admin-btn { display: flex; }
            .desktop-admin-btn { display: none; }
        }
        
        .card img {
            width: 100%;
            height: 120px;
            object-fit: contain;
            display: block;
            background: rgba(255,255,255,0.015);
            padding: 12px;
        }
        .card h3 {
            font-size: 14px;
            margin: 12px 0 4px;
            padding: 0 12px;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }
        .card p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0 0 10px;
            padding: 0 12px;
        }
        .card .channel-id {
            font-size: 11px;
            font-weight: 600;
            padding: 0 12px 12px;
            color: var(--text-id);
            margin-top: auto;
        }
        /* Mobile Specific tweaks */
        @media(max-width:768px){ 
            .grid { grid-template-columns: repeat(2, 1fr); gap: 10px; } 
            .card img { height: 100px; }
            .card h3 { font-size: 13px; }
        }
        
        /* Loading & Empty state */
        .state-msg { text-align: center; padding: 4rem 1rem; color: var(--text-muted); grid-column: 1 / -1; }
        
        @media (max-width: 768px) {
            .grid { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
            .card-title { font-size: 0.85rem; }
            .card-content { padding: 0.75rem; }
            .header-content { flex-direction: column; align-items: stretch; gap: 0.75rem; }
            .header-top { display: flex; align-items: center; justify-content: space-between; width: 100%; }
            .header-actions { flex-direction: column; align-items: stretch; width: 100%; gap: 0.75rem; }
            .search-container { max-width: 100%; order: 2; }
            .category-select { width: 100%; order: 1; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="header-top">
                <a href="index.php" class="logo-text"><?php print($APP_CONFIG['APP_NAME'] ?? 'Live TV'); ?></a>
                
                <?php if(app_admin_button("get") == "ON"): ?>
                    <!-- Admin Button Mobile Position -->
                    <a href="admin.php" class="btn-admin mobile-admin-btn" title="Admin Panel">
                        <i class="fas fa-user-shield"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="header-actions">
                <select id="categorySelect" class="category-select">
                    <option value="all">All Categories</option>
                    <?php foreach($genres as $genre_id => $genre_title): ?>
                        <?php if($genre_id !== '*'): ?>
                            <option value="<?php echo htmlspecialchars($genre_title); ?>"><?php echo htmlspecialchars($genre_title); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search channels..." autocomplete="off">
                </div>
                
                <?php if(app_admin_button("get") == "ON"): ?>
                    <!-- Admin Button Desktop Position -->
                    <a href="admin.php" class="btn-admin desktop-admin-btn" title="Admin Panel">
                        <i class="fas fa-user-shield"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>

    <div class="container">
        <div style="margin: 1.5rem 0 0.5rem;">
            <div class="stats"><span id="channelCount">0</span> Channels Total</div>
        </div>

        <div id="channelGrid" class="grid">
            <div class="state-msg"><i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Loading channels...</div>
        </div>
    </div>
    </main>

    <footer>
        <div class="container">
            &copy; 2026 <?php print($APP_CONFIG['APP_NAME'] ?? 'Stalker Portal'); ?>
        </div>
    </footer>
    
    <script>
        let allChannels = [];
        let filteredChannels = [];
        let searchTimeout;
        let currentPage = 1;
        const perPage = 60;
        
        const grid = document.getElementById('channelGrid');
        const countDisplay = document.getElementById('channelCount');
        const searchInput = document.getElementById('searchInput');
        const categorySelect = document.getElementById('categorySelect');

        // Fetch channels on load
        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=getChannels'
        })
        .then(res => res.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    allChannels = data.data.list;
                    applyFilters();
                } else {
                    grid.innerHTML = '<div class="state-msg">Failed to load channels.</div>';
                }
            } catch (e) {
                grid.innerHTML = '<div class="state-msg">Error parsing data.</div>';
            }
        })
        .catch(() => {
            grid.innerHTML = '<div class="state-msg">Network error.</div>';
        });

        // Debounced search
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });

        // Category filter
        categorySelect.addEventListener('change', applyFilters);

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const category = categorySelect.value;
            
            filteredChannels = allChannels.filter(ch => {
                const matchSearch = ch.title.toLowerCase().includes(searchTerm);
                const chCategory = ch.category_title || 'Uncategorized';
                const matchCategory = category === 'all' || chCategory === category;
                return matchSearch && matchCategory;
            });

            countDisplay.innerText = filteredChannels.length;
            currentPage = 1;
            grid.innerHTML = '';
            renderNextBatch();
        }

        function renderNextBatch() {
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const batch = filteredChannels.slice(start, end);

            if (batch.length === 0 && currentPage === 1) {
                grid.innerHTML = '<div class="state-msg">No channels found.</div>';
                return;
            }

            let html = '';
            batch.forEach(v => {
                const cat = v.category_title || 'Uncategorized';
                html += `
                    <a href="player.php?id=${v.id}" class="card">
                        <img src="${v.logo ? v.logo : 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='}" loading="lazy" alt="${v.title}" onerror="this.src='data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='">
                        <h3>${v.title}</h3>
                        <p>${cat}</p>
                        <div class="channel-id">ID: ${v.id}</div>
                    </a>
                `;
            });

            const template = document.createElement('template');
            template.innerHTML = html;
            grid.appendChild(template.content);
            
            currentPage++;
        }

        // Infinite Scroll
        window.onscroll = function() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                if ((currentPage - 1) * perPage < filteredChannels.length) {
                    renderNextBatch();
                }
            }
        };
    </script>
</body>
</html>