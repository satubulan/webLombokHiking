<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}
if (!isset($_GET['trip_id'])) {
    echo "Trip tidak ditemukan.";
    exit();
}
$tripId = intval($_GET['trip_id']);


$guideId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat Room - Guide</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .chat-box {
            width: 100%;
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background: #f4f4f4;
            border-radius: 8px;
            height: 500px;
            overflow-y: scroll;
        }
        .message {
            padding: 10px;
            background: white;
            margin-bottom: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chat-input {
            display: flex;
            margin-top: 20px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border-radius: 5px 0 0 5px;
            border: 1px solid #ccc;
        }
        .chat-input button {
            padding: 10px;
            border: none;
            background: #4CAF50;
            color: white;
            border-radius: 0 5px 5px 0;
        }
    </style>
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h2>💬 Chat Grup Trip</h2>

        <div class="chat-box" id="chatBox">
            <!-- Pesan akan dimuat di sini via JavaScript -->
        </div>

        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Ketik pesan...">
            <button onclick="sendMessage()">Kirim</button>
        </div>

        <a href="dashboard.php" class="btn" style="margin-top: 20px;">⬅ Kembali ke Dashboard</a>
    </div>

    <script>
        
        const tripId = new URLSearchParams(window.location.search).get('trip_id');
        const chatBox = document.getElementById('chatBox');

        function fetchChats() {
            fetch(`chat-fetch.php?trip_id=${tripId}`)
                .then(res => res.json())
                .then(data => {
                    chatBox.innerHTML = '';
                    data.forEach(chat => {
                        const div = document.createElement('div');
                        div.className = 'message';
                        div.innerHTML = `<strong>${chat.sender}</strong><br>${chat.message}<br><small>${chat.sent_at}</small>`;
                        chatBox.appendChild(div);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        setInterval(fetchChats, 3000); // refresh tiap 3 detik
        fetchChats();
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (!message) return;

            fetch('chat-send.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `trip_id=${tripId}&message=${encodeURIComponent(message)}`
            }).then(() => {
                input.value = '';
                fetchChats();
            });
        }

        // Load initial chat messages
        fetchChats();

    </script>
</body>
</html>
