<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$filter_file = $directories["filter"] . "/$host.json";

$stored_data = file_exists($filter_file) ? json_decode(file_get_contents($filter_file), true) : [];

$show_popup = false;
$popup_message = '';
$popup_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['group'] ?? [];
    $all_groups = group_title(true);

    foreach ($all_groups as $id => $title) {
        $stored_data[$id] = [
            'id' => $id,
            'title' => $title,
            'filter' => in_array($id, $selected)
        ];
    }

    $result = file_put_contents($filter_file, json_encode($stored_data));

    $show_popup = true;
    if ($result === false) {
        $popup_message = 'Error: Unable to save settings. Check file permissions.';
        $popup_type = 'error';
    } else {
        unlink($directories["playlist"] . "/{$host}.m3u");
        $popup_message = 'Settings saved successfully!';
        $popup_type = 'success';
    }
}

$groups = group_title(true);

$currentUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$scriptName = basename($_SERVER['SCRIPT_NAME']);
if (empty($scriptName) || $scriptName == "index.php") {    
    $playlistUrl = rtrim($currentUrl, "/") . "/playlist.php";
} else {    
    $playlistUrl = str_replace($scriptName, "playlist.php", $currentUrl);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Group Filter</title>
    <style>  
        body {
            min-height: 100vh;
            height: auto;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            display: flex;
            justify-content: center;
            align-items: center;           
            margin: 0;
            color: #e0e0e0;
        }

        .container {
            background: rgba(34, 40, 49, 0.9);
            border-radius: 20px;
            padding: 30px;
            margin: 20px;
            overflow: auto;
            max-height: 85vh;
            width: 90%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h2 {
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
            margin: 0 0 25px 0;
            text-align: center;
        }

        .checkbox-container {
            max-height: 350px;
            overflow-y: auto;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 12px 0;
            padding: 8px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.03);
            transition: background 0.2s;
        }

        .form-group:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .form-group label {
            flex: 1;
            text-align: left;
            font-weight: 500;
            color: #a0a0a0;
            padding-left: 10px;
            cursor: pointer;
        }

        .form-group input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
            margin-right: 10px;
        }

        button.save-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(45deg, #0077b6, #023e8a);
            color: white;            
        }

        button.save-btn:hover {
            background: linear-gradient(45deg, #0096c7, #0353a4);
            box-shadow: 0 0px 10px rgba(0, 150, 199, 0.4);
            transform: translateY(-2px);
        }

        .btn {
            padding: 10px;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: rgba(0, 212, 255, 0.2);
            border-color: #00d4ff;
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        .btn i {
            font-size: 16px;
            color: #e0e0e0;
        }

        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(34, 40, 49, 0.95);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            max-width: 90%;
            text-align: center;
            border: 2px solid #00d4ff;
        }

        .popup button {
            width: auto;
            padding: 10px 20px;
            margin: 0 auto;
            display: inline-block;
            background: linear-gradient(45deg, #0077b6, #023e8a);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;                       
        }

        .popup button:hover {
            background: linear-gradient(45deg, #0096c7, #0353a4);
            box-shadow: 0 0px 10px rgba(0, 150, 199, 0.6);
            transform: translateY(-2px);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }      

        .search-container {
            margin-bottom: 20px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            font-size: 14px;
            box-sizing: border-box;
        }

        .search-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 5px rgba(0, 212, 255, 0.5);
        }

        .search-container::after {
            content: '🔍';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a0a0;
        }

        .playlist-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }

        .playlist-container label {
            font-weight: 600;
            color: #a0a0a0;
        }

        .playlist-container input {
            width: 100%;
            flex: 1;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            font-size: 16px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 1.5em;
            }

            .form-group {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px;
            }

            .form-group label {
                padding-left: 0;
                margin-top: 5px;
            }

            .popup {
                width: 80%;
                padding: 15px;
            }
        }
    </style>
    <script>
        function toggleCheckboxes(source) {
            let checkboxes = document.querySelectorAll('.form-group:not([style*="display: none"]) input[name="group[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        function updateCheckAll() {
            let visibleCheckboxes = document.querySelectorAll('.form-group:not([style*="display: none"]) input[name="group[]"]');
            let checkAllBox = document.getElementById('checkAll');
            checkAllBox.checked = visibleCheckboxes.length > 0 && 
                Array.from(visibleCheckboxes).every(checkbox => checkbox.checked);
        }

        function filterGroups() {
            let input = document.getElementById('groupSearch').value.toLowerCase();
            let groups = document.getElementsByClassName('form-group');

            for (let group of groups) {
                if (group.querySelector('#checkAll')) continue;

                let label = group.querySelector('label');
                let text = label.textContent.toLowerCase().trim();

                if (text.includes(input)) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            }
            updateCheckAll();
        }

        document.addEventListener('DOMContentLoaded', function () {
            let checkboxes = document.querySelectorAll('input[name="group[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateCheckAll);
            });

            document.getElementById('groupSearch').addEventListener('input', filterGroups);
            updateCheckAll();
            
            <?php if ($show_popup): ?>
                showPopup();
                document.getElementById('popup-message').innerText = '<?= $popup_message ?>';
                document.getElementById('popup-buttons').innerHTML = `
                    <button onclick="hidePopup()">OK</button>
                `;
            <?php endif; ?>
        });

        function showPopup() {
            document.getElementById('popup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function hidePopup() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }    
        
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        function copyToClipboard() {
            var copyText = document.getElementById("playlist_url");
            copyText.select();
            document.execCommand("copy");
            showPopup();
            document.getElementById('popup-message').innerText = 'URL copied: ' + copyText.value;
            document.getElementById('popup-buttons').innerHTML = `
                <button onclick="hidePopup()">OK</button>
            `;
        }

        document.addEventListener("DOMContentLoaded", function() {            
            let footer = document.createElement("div");
            footer.style.textAlign = "center";
            footer.style.marginTop = "20px";
            footer.style.fontSize = "18px";
            footer.innerHTML = "<strong>Coded with ❤️ by PARAG</strong>";
            
            document.querySelector(".container").appendChild(footer);
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Group Filter</h2>
        <form method="post">
            <div class="search-container">
                <input type="text" id="groupSearch" class="search-input" placeholder="Search groups...">
            </div>
            <div class="checkbox-container">
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="checkAll" onclick="toggleCheckboxes(this)"> Select All
                    </label>
                </div>
                <?php foreach ($groups as $id => $title): ?>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="group[]" value="<?= $id ?>" 
                                <?= !empty($stored_data[$id]['filter']) ? 'checked' : '' ?> 
                                onchange="updateCheckAll()">
                            <?= htmlspecialchars($title) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="save-btn">Save</button>
        </form>
        <div class="playlist-container">
            <label>Playlist:</label>
            <input type="text" id="playlist_url" value="<?= $playlistUrl ?>" readonly>
            <div class="action-buttons">                    
                <button class="btn" onclick="copyToClipboard()">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
        <div class="UserLogout" style="text-align: right; margin-top: 20px;">
            <form action="logout.php" method="POST">
                <button type="submit" class="btn" style="color: white; font-weight: bold; width: auto;">
                    <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Popup and Overlay -->
    <div id="overlay" class="overlay" onclick="hidePopup()"></div>
    <div id="popup" class="popup <?php echo $popup_type; ?>">
        <p id="popup-message"></p>
        <div id="popup-buttons">
            <button onclick="hidePopup()">OK</button>
        </div>
    </div>
</body>
</html>