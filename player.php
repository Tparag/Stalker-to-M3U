<?php
include("_inc.configs.php");

$id = "";
if(isset($_REQUEST['id'])) { 
    $id = trim($_REQUEST['id']); 
}

if(empty($id)) {
    app_recordalogs("ERROR", "Player accessed without channel ID");
    header("Location: index.php");
    exit();
}

$clive = getChannelDetail($id);
if(empty($clive)) { 
    app_recordalogs("ERROR", "Player accessed with invalid channel ID: ".$id);
    header("Location: index.php");
    exit();
}

app_recordalogs("PLAYBACK", "User started playing: ".$clive['title']." (ID: ".$clive['id'].")");
$playback_url = "live.php?id=".$clive['id'];    
?>
<!-- Source Code By <?php print($APP_CONFIG['WHITELABEL_APP_DEVS']); ?> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php print($clive['title']); ?> Online | <?php print($APP_CONFIG['APP_NAME']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.6.2/dist/plyr.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/plyr@3.6.12/dist/plyr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.1.4/dist/hls.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #0f172a; 
            height: 100vh; 
            height: -webkit-fill-available;
            overflow: hidden;
            position: fixed;
            width: 100%;
        }
        html { 
            height: -webkit-fill-available; 
            overflow: hidden;
        }
        
        .loading { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: #0f172a; 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            gap: 24px;
            z-index: 9999; 
        }
        .google-dots {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            animation: bounce 0.5s alternate infinite ease-in-out;
        }
        .dot.blue { background-color: #4285F4; animation-delay: 0s; }
        .dot.red { background-color: #EA4335; animation-delay: 0.15s; }
        .dot.yellow { background-color: #FBBC05; animation-delay: 0.3s; }
        .dot.green { background-color: #34A853; animation-delay: 0.45s; }
        .loading-text-brand {
            color: #f8fafc;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: 4px;
            text-transform: uppercase;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes bounce {
            0% { transform: translateY(0); }
            100% { transform: translateY(-20px); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        #player-container { 
            width: 100%; 
            height: 100vh; 
            height: -webkit-fill-available; 
            position: relative; 
            background: #000; 
        }
        .plyr { 
            height: 100vh; 
            height: -webkit-fill-available; 
        }
        .plyr__video-wrapper { 
            background: #000; 
        }
        
        /* Mobile specific width fix */
        @media (max-width: 768px) {
            .plyr { 
                height: 100vh !important; 
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: #000 !important;
            }
            .plyr__video-wrapper { 
                width: 100% !important;
                height: auto !important;
            }
            video#player {
                width: 100% !important;
                height: auto !important;
                object-fit: contain !important;
            }
        }
        .plyr__control--overlaid { 
            background: rgba(255,255,255,0.2); 
            backdrop-filter: blur(10px); 
            border: 2px solid rgba(255,255,255,0.3); 
        }
        .plyr__control--overlaid:hover { 
            background: rgba(255,255,255,0.3); 
        }
        
        /* Mobile optimizations for player */
        @media (max-width: 768px) {
            .plyr__controls { 
                padding: 10px !important; 
                background: linear-gradient(to top, rgba(0,0,0,0.8), transparent) !important; 
            }
            .plyr__control { 
                padding: 8px !important; 
            }
        }
    </style>
</head>
<body>
<div id="loading" class="loading">
    <div class="google-dots">
        <div class="dot blue"></div>
        <div class="dot red"></div>
        <div class="dot yellow"></div>
        <div class="dot green"></div>
    </div>
    <div class="loading-text-brand">STALKER PORTAL</div>
</div>

<div id="player-container">
    <video id="player" autoplay controls crossorigin playsinline webkit-playsinline x5-playsinline>
        <source src="<?php print($playback_url); ?>" type="application/x-mpegURL">
    </video>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const video = document.querySelector("video");
    const source = video.getElementsByTagName("source")[0].src;
    const defaultOptions = {
        controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
        settings: ['captions', 'quality', 'speed'],
        quality: { default: 720, options: [1080, 720, 480, 360] },
        fullscreen: { enabled: true, fallback: true, iosNative: true }
    };
    
    if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(source);
        hls.on(Hls.Events.MANIFEST_PARSED, function (event, data) {
            const availableQualities = hls.levels.map((l) => l.height);
            if(availableQualities.length > 0) {
                defaultOptions.quality.options = availableQualities;
                defaultOptions.quality.default = availableQualities[0];
            }
            initializePlayer();
        });
        hls.on(Hls.Events.ERROR, function (event, data) { 
            console.error('HLS Error:', data); 
            if (data.fatal || data.type === Hls.ErrorTypes.NETWORK_ERROR) {
                initializePlayer();
            }
        });
        hls.attachMedia(video);
        window.hls = hls;
    } else { initializePlayer(); }
    
    let playerInitialized = false;
    function initializePlayer() {
        if (playerInitialized) return;
        playerInitialized = true;
        const player = new Plyr(video, defaultOptions);
        setTimeout(() => {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('player-container').style.display = 'block';
            player.play().catch(e => console.log('Autoplay failed:', e));
        }, 1500); 
    }

    // Force initialization if it takes too long or fails
    setTimeout(initializePlayer, 5000);
});

// Fix for iOS viewport height
window.addEventListener('resize', () => {
    document.getElementById('player-container').style.height = window.innerHeight + 'px';
});
</script>
</body>
</html>
<!-- Source Code By <?php print($APP_CONFIG['WHITELABEL_APP_DEVS']); ?> -->
