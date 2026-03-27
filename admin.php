<?php
session_start();
if(!isset($_SESSION['yuvisession']) || $_SESSION['yuvisession'] !== true) {
    header("Location: login.php");
    exit();
}
include("_inc.configs.php");
$pageTitle = "Admin Panel";
$module = ""; if(isset($_GET['module'])){ $module = trim($_GET['module']); }
if($module == "application_logs")
{
    $apps_logs = "";
    if(file_exists($APP_CONFIG['DATA_FOLDER']."/axLogs.enc")) { $apps_logs = @file_get_contents($APP_CONFIG['DATA_FOLDER']."/axLogs.enc"); }
?>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Logs | Admin Panel -
        <?php print($APP_CONFIG['APP_NAME']); ?>
    </title>

</head>

<body>
    <pre><?php print($apps_logs); ?></pre>
</body>

</html>
<?php
exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php print($pageTitle); ?> |
        <?php print($APP_CONFIG['APP_NAME']); ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.11.0/sweetalert2.css" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255,255,255,0.08);
            --secondary-bg: rgba(0,0,0,0.2);
            --accent: #10b981; /* Success green */
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main { flex: 1; }
        footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--border-color);
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
        .container-main {
            padding: 1.5rem 1rem 5rem; /* Space for fixed footer */
        }

        /* Full Width Layout */
        @media (min-width: 992px) {
            .container-main {
                padding-left: 3.5rem;
                padding-right: 3.5rem;
            }
        }

        /* Improved Header */
        header {
            background-color: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-color);
            padding: 0.8rem 1rem;
        }
        .header-content { display: flex; align-items: center; justify-content: space-between; }
        .logo-text { font-size: 1.4rem; font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        .logo-text span { font-size: 1rem; color: var(--text-main); font-weight: 500; opacity: 0.7; }

        /* Navigation Tabs */
        .nav-pills {
            background: var(--card-bg);
            padding: 0.4rem;
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
            display: inline-flex;
            max-width: 100%;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none; /* Firefox */
        }
        .nav-pills::-webkit-scrollbar { display: none; } /* Chrome/Safari */
        .nav-pills .nav-link {
            color: var(--text-muted);
            border-radius: 0.75rem;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .nav-pills .nav-link:hover:not(.active) {
            background: rgba(255,255,255,0.05);
            color: var(--text-main);
        }

        /* Compact Dashboard Cards */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            color: var(--text-main);
            transition: transform 0.2s, border-color 0.2s;
            height: 100%;
        }
        .card:hover { border-color: rgba(59, 130, 246, 0.3); }
        .card-body { padding: 1.75rem; }
        
        .card-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
        
        @media (max-width: 576px) {
            .card-header-flex { flex-direction: column; align-items: flex-start; text-align: left; }
            .card-header-flex .btn { width: 100%; }
        }

        .card-title-ui { font-size: 1.15rem; font-weight: 700; color: var(--primary); margin: 0; }
        
        /* Modern Switches (Toggles) */
        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #334155;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: var(--accent); }
        input:checked + .slider:before { transform: translateX(24px); }

        /* Dashboard Stats Utility */
        .stat-box {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--secondary-bg);
            border-radius: 1rem;
            border: 1px solid var(--border-color);
        }
        .stat-icon {
            width: 42px; height: 42px;
            border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            font-size: 1.25rem;
        }
        .stat-info .label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }
        .stat-info .value { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }

        /* Genre List Premium */
        .genre-list-container {
            max-height: 480px;
            overflow-y: auto;
            background: var(--secondary-bg);
            border-radius: 1rem;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
        }
        @media (max-width: 768px) {
            .genre-list-container { max-height: 350px; }
        }
        .genre-item {
            display: flex; align-items: center;
            padding: 10px 14px;
            border-radius: 0.75rem;
            margin-bottom: 4px;
            transition: background 0.2s;
            cursor: pointer;
        }
        .genre-item:hover { background: rgba(255,255,255,0.05); }
        .genre-item input { margin-right: 12px; transform: scale(1.1); }
        .genre-item label { cursor: pointer; margin: 0; flex: 1; font-weight: 500; }

        /* Form styling */
        .form-control {
            background-color: var(--secondary-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 0.8rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background-color: rgba(255,255,255,0.08);
            border-color: var(--primary);
            color: var(--text-main);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .form-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem; }
        .text-muted { color: var(--text-muted) !important; }
        .form-control::placeholder { color: rgba(255,255,255,0.3) !important; opacity: 1; }

        /* Sidebar/Tooltips Utility */
        .btn-icon {
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 0.6rem;
            transition: all 0.2s;
        }
        .btn-icon:hover { transform: scale(1.05); }

        /* Animation */
        .fade-in-up {
            animation: fadeInUp 0.4s ease-out forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>

<body>
    <header>
        <div class="container-fluid d-flex align-items-center justify-content-between px-lg-4">
            <a href="admin.php" class="logo-text">
                <i class="fa-solid fa-server"></i>
                <?php print($APP_CONFIG['APP_NAME'] ?? 'Live TV'); ?> <span>| Admin</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                 <a href="index.php" class="btn btn-outline-primary btn-sm rounded-pill" title="Go to Home">
                    <i class="fa-solid fa-house"></i> <span class="d-none d-md-inline ms-1">Home</span>
                 </a>
                 <button class="btn btn-outline-danger btn-sm rounded-pill" id="btn_logout_app" title="Logout session">
                    <i class="fa-solid fa-power-off"></i> <span class="d-none d-md-inline ms-1">Logout</span>
                 </button>
            </div>
        </div>
    </header>
    <main>

    <div class="container-main">
        <!-- Dashboard Navigation Tabs -->
        <ul class="nav nav-pills" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="portal-tab" data-bs-toggle="pill" data-bs-target="#portal-content" type="button" role="tab">
                    <i class="fa-solid fa-plug me-2"></i>Portal Config
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="genre-tab" data-bs-toggle="pill" data-bs-target="#genre-content" type="button" role="tab">
                    <i class="fa-solid fa-filter me-2"></i>Genre Filter
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="pill" data-bs-target="#settings-content" type="button" role="tab">
                    <i class="fa-solid fa-gears me-2"></i>Global Settings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="downloads-tab" data-bs-toggle="pill" data-bs-target="#downloads-content" type="button" role="tab">
                    <i class="fa-solid fa-cloud-arrow-down me-2"></i>Downloads
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <!-- PORTAL CONFIG TAB -->
            <div class="tab-pane fade show active fade-in-up" id="portal-content" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-header-flex">
                                    <h4 class="card-title-ui">Stalker Portal Configuration</h4>
                                </div>
                                <hr class="opacity-10" />
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">MAC Stalker URL *</label>
                                        <input type="text" class="form-control" placeholder="http://server.url/c/" id="mac_url" autocomplete="off" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">MAC ID *</label>
                                        <input type="text" class="form-control" placeholder="00:1A:79:..." id="mac_id" autocomplete="off" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Serial IDN</label>
                                        <input type="text" class="form-control" placeholder="Optional" id="mac_serial" autocomplete="off" />
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Device ID 1</label>
                                        <input type="text" class="form-control" id="mac_dv1" placeholder="Optional" autocomplete="off" />
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Device ID 2</label>
                                        <input type="text" class="form-control" id="mac_dv2" placeholder="Optional" autocomplete="off" />
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Signature</label>
                                        <input type="text" class="form-control" id="mac_sig" placeholder="Optional" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button class="btn btn-primary px-4 rounded-pill" type="button" id="btn_mac">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Save Configuration
                                    </button>
                                    <button class="btn btn-danger btn-icon" type="button" id="btn_delete_mac" title="Delete Configuration">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5" id="box_stalker_details" style="display:none;">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="card-header-flex">
                                    <h4 class="card-title-ui text-info">Live Portal Status</h4>
                                    <button class="btn btn-sm btn-outline-info rounded-pill" onclick="update_mac_data()" title="Refresh Details">
                                        <i class="fa-solid fa-rotate"></i>
                                    </button>
                                </div>
                                <hr class="opacity-10" />
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="stat-box">
                                            <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                                            <div class="stat-info">
                                                <div class="label">Expiry Date</div>
                                                <div class="value mac_tv_expiry">Loading...</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="stat-box">
                                            <div class="stat-icon"><i class="fa-solid fa-tv"></i></div>
                                            <div class="stat-info">
                                                <div class="label">Total Channels</div>
                                                <div class="value mac_tv_count">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GENRE FILTER TAB -->
            <div class="tab-pane fade fade-in-up" id="genre-content" role="tabpanel">
                <div class="card border-0 shadow-sm" id="box_genre_filter">
                    <div class="card-body">
                        <div class="card-header-flex border-bottom pb-3 mb-0">
                            <div>
                                <h4 class="card-title-ui">Playlist Genre Filter</h4>
                                <p class="text-muted small mb-0 mt-1">Select genres to show in Public Index & Playlist. If empty, all genres are shown.</p>
                            </div>
                            <button class="btn btn-primary px-4 rounded-pill" id="btn_save_genre_filter">
                                <i class="fa-solid fa-check-double me-2"></i>Save Active Filter
                            </button>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-lg-4 col-md-5">
                                <div class="genre-sidebar bg-black-20 p-3 rounded-4 h-100">
                                    <label class="form-label">Search Genres</label>
                                    <div class="position-relative mb-3">
                                        <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 1rem; top: 1rem;"></i>
                                        <input type="text" class="form-control ps-5" id="genre_search" placeholder="Type to filter...">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-info btn-sm flex-fill" id="btn_genre_all"><i class="fa-solid fa-square-check me-2"></i>All</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-fill" id="btn_genre_none"><i class="fa-solid fa-square me-2"></i>None</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-7">
                                <div class="genre-list-container">
                                    <div id="genre_list_items" class="row row-cols-1 row-cols-md-2 g-1">
                                        <div class="col-12 text-center p-5 text-muted">
                                            <i class="fa-solid fa-spinner fa-spin fa-2x mb-3"></i><br>Loading Genre List...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SETTINGS TAB -->
            <div class="tab-pane fade fade-in-up" id="settings-content" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title-ui mb-4">Core Settings</h4>
                                <div class="stat-box d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-user-shield"></i></div>
                                        <div>
                                            <div class="label">Admin Page Button</div>
                                            <div class="value mac_admin_button_status text-capitalize">ON</div>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="toggle_admin_button">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="stat-box d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-shield-halved"></i></div>
                                        <div>
                                            <div class="label">Stream Proxy Status</div>
                                            <div class="value mac_stream_proxy_status text-capitalize">OFF</div>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="toggle_proxy">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="stat-box d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-terminal"></i></div>
                                        <div>
                                            <div class="label">Logging Status</div>
                                            <div class="value mac_logging_status text-capitalize">OFF</div>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="toggle_logging_chk">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="stat-box d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                        <div>
                                            <div class="label">Playback URL Cache</div>
                                            <div class="value mac_playback_cache_status text-capitalize">OFF</div>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="toggle_playback_cache">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div id="playback_expiry_container" class="mt-2" style="display:none;">
                                    <label class="form-label ms-1">Cache Expiry (Seconds)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="txt_playback_expiry" placeholder="14400">
                                        <button class="btn btn-primary" id="btn_save_playback_expiry" title="Save Expiry">
                                            <i class="fa-solid fa-save"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title-ui mb-3">System Logs</h4>
                                <p class="text-muted small">Monitor real-time application behavior and stalker port connections.</p>
                                <div class="d-flex gap-2 mt-4">
                                    <a href="?module=application_logs" target="_blank" class="btn btn-outline-primary rounded-pill">
                                        <i class="fa-solid fa-file-lines me-2"></i>Full Logs
                                    </a>
                                    <button class="btn btn-outline-danger rounded-pill px-4" id="btn_clear_logs">
                                        <i class="fa-solid fa-broom me-2"></i>Clear Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DOWNLOADS TAB -->
            <div class="tab-pane fade fade-in-up" id="downloads-content" role="tabpanel">
                <div class="card border-0">
                    <div class="card-body">
                        <h4 class="card-title-ui mb-4">Export Playlist</h4>
                        <div class="row g-4">
                            <div class="col-md-6 col-xl-4">
                                <div class="bg-primary rounded-4 p-4 text-white hover-up shadow-lg">
                                    <i class="fa-solid fa-file-export fa-3x mb-3 opacity-50"></i>
                                    <h5>Full M3U8 Playlist</h5>
                                    <p class="small opacity-75">Standard M3U format with full category support & icons.</p>
                                    <a href="playlist.m3u" class="btn btn-light w-100 rounded-pill mt-2" target="_blank">
                                        Download Playlist
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="bg-info rounded-4 p-4 text-white hover-up shadow-lg">
                                    <i class="fa-solid fa-file-code fa-3x mb-3 opacity-50"></i>
                                    <h5>Browser Playlist</h5>
                                    <p class="small opacity-75">View the playlist links as a clean text file in browser.</p>
                                    <a href="playlist.m3u?view=browser" class="btn btn-light w-100 rounded-pill mt-2" target="_blank">
                                        View On Browser
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 p-4 rounded-4 bg-secondary-bg border">
                            <h6 class="text-primary mb-3"><i class="fa-solid fa-link me-2"></i>Direct Playlist URL</h6>
                            <div class="input-group">
                                <input type="text" class="form-control bg-dark text-muted border-0" id="playlist_url_static" readonly value="Loading Link...">
                                <button class="btn btn-primary px-4" onclick="copyPlaylistUrl()">
                                    <i class="fa-solid fa-copy me-2"></i>Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </main>

    <footer>
        <div class="container-fluid">
            &copy; 2026 <?php print($APP_CONFIG['APP_NAME'] ?? 'Stalker Portal'); ?>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.11.0/sweetalert2.all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            var protocol = window.location.protocol;
            var host = window.location.host;
            var path = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/playlist.m3u';
            $("#playlist_url_static").val(protocol + '//' + host + path);
        });

        function copyPlaylistUrl() {
            var playlistUrl = $("#playlist_url_static").val();
            navigator.clipboard.writeText(playlistUrl).then(function () {
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success', title: 'URL Copied', showConfirmButton: false, timer: 1500
                });
            }, function (err) {
                var textarea = document.createElement('textarea');
                textarea.value = playlistUrl;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success', title: 'URL Copied', showConfirmButton: false, timer: 1500
                });
            });
        }

        $('#btn_logout_app').on('click', function() {
            Swal.fire({
                title: 'Logout?',
                text: "Are you sure you want to end your session?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, Logout',
                background: '#1e293b', color: '#f8fafc'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api.php', type: 'POST', data: { action: 'logout' },
                        success: function(response) { window.location.href = 'login.php'; }
                    });
                }
            });
        });
    </script>
    <script src="assets/intriapp.js?token=<?php print(generateRandomAlphanumericString(32)); ?>"
        onload="load_dashboard_data()"></script>
</body>

</html>