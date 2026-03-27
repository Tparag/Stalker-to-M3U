# 🔄 Stalker Portal to M3U Converter

Easily convert your **Stalker Portal login (URL + MAC + Serial + Device IDs)** into a clean, high-performance M3U playlist. This project provides a complete ecosystem to manage, filter, and stream your IPTV content with ease.

💻 **Supports PC (XAMPP)**  
📱 **Supports Mobile (KSWEB)**  

---

## 🔐 Default Login Info

📌 **Admin PIN**: `1234` (Can be changed in `_inc.configs.php`)

---

## 🚀 Key Features

### 📡 Advanced Stalker Integration
- **Full Authentication**: Supports URL, MAC Address, Serial Number, Device ID 1, Device ID 2, and Signature.
- **Real-time Status**: View portal expiry date and total channel count in the admin dashboard.

### 🎨 Modern & Responsive UI
- **Stunning Dashboard**: Features a premium "Outfit" font, glassmorphism effects, and a sleek dark mode.
- **Fast Search**: Instant channel filtering as you type.
- **Infinite Scroll**: Smoothly browse thousands of channels without page reloads.
- **Category Filtering**: Quickly jump between different genres directly from the homepage.

### 🛠️ Powerful Admin Control Panel
- **Global Settings**: Toggle features like "Admin Button Visibility" on the fly.
- **Stream Proxy**: Bypass ISP blocks or geo-restrictions by routing streams through your own server.
- **Playback URL Caching**: Drastically reduces loading times by caching stream links with configurable expiry.
- **Playlist Customization**: Select exactly which genres you want to include in your generated M3U playlist.
- **System Logging**: detailed logs to monitor API activity and portal connections.

### 📺 Flexible Playback Options
- **Generated M3U/M3U8**: Get a permanent link for external players.
- **Built-in Web Player**: Watch streams directly in your browser with a dedicated player interface.
- **Browser View**: Open the playlist as a raw text file to inspect stream URLs.

---

## 🖥️ PC Setup (XAMPP)

1. 📥 [Download XAMPP](https://www.apachefriends.org/index.html)
2. 🟢 Start **Apache** in the XAMPP Control Panel.
3. 📁 Copy the script folder to:  
   `C:\xampp\htdocs\stalker-to-m3u`
4. 🌐 Open your browser and go to:  
   `http://localhost/stalker-to-m3u`
5. 🔓 Login, configure your portal in the Admin Panel, and generate your M3U.

---

## 📱 Android Setup (KSWEB)

1. 📲 Install [KSWEB](https://play.google.com/store/apps/details?id=ru.kslabs.ksweb) from the Play Store.
2. 🟢 Start **Apache** within the KSWEB app.
3. 📁 Copy the project folder to:  
   `storage/htdocs/stalker-to-m3u`
4. 🌐 Open your mobile browser and go to:  
   `http://localhost:8000/stalker-to-m3u`
5. 🚀 Log in and start converting your Stalker information!

---

## 🎬 How to Use Playlist in IPTV Players

Simply copy your **Direct Playlist URL** from the "Downloads" tab in the Admin Panel and paste it into any of these players:

📺 **VLC Media Player**  
- Media → Open Network Stream → Paste URL

📲 **OTT Navigator / Tivimate (Android)**  
- Settings → Provider → Add Playlist → Enter M3U URL

📦 **Kodi**  
- PVR IPTV Simple Client → Configure with M3U URL

🧿 **Perfect Player / NS Player / Autho Player**  
- Add new M3U playlist and use the generated link.

---

## ⚠️ Important Notes

- ❗ **Security**: Always change the default admin PIN for production use.
- ❗ **Disclaimer**: This script is for educational purposes. We do not provide or host any content.

---

## 🤝 Support
If you find this project useful, please give it a ⭐!
