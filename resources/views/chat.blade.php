<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MyChat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
</head>
<body class="bg-gray-100">

<div class="flex flex-col h-screen max-w-md mx-auto shadow-md bg-white">
    <!-- Header -->
    <div class="bg-green-600 text-white text-center py-3 text-lg font-semibold">
        MyChat
    </div>

    <!-- Chat Box -->
    <div id="chat" class="flex-1 overflow-y-auto p-3 space-y-2">
        <!-- Chat messages akan masuk di sini -->
    </div>

    <!-- Typing Indicator -->
    <div id="typing-indicator" class="text-gray-500 italic text-sm px-3 mb-1"></div>

    <!-- Form Chat -->
    <div class="p-3 border-t bg-white flex items-center gap-2">
        <input type="text" id="nama" placeholder="Nama kamu"
            class="hidden"> {{-- disembunyikan karena sudah diketik di awal --}}
        <input type="text" id="pesan" placeholder="Tulis pesan..."
            class="flex-1 border rounded-full px-4 py-2 text-sm focus:outline-none focus:ring"
            onkeydown="if(event.key === 'Enter') kirim();">
        <button id="kirim-btn"
            onclick="kirim()"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full text-sm font-medium flex items-center gap-2 cursor-pointer">
            <span id="kirim-text">Kirim</span>
            <svg id="loading-icon" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4" />
                <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8z" />
            </svg>
        </button>
    </div>
</div>

<script>
    Pusher.logToConsole = false;

    const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
        cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
        forceTLS: true
    });

    const channel = pusher.subscribe('chat-channel');

    // Menampilkan pesan baru
    channel.bind('chat-event', function (data) {
        const namaKamu = document.getElementById('nama').value.trim();
        const isKamu = (data.nama === namaKamu);
        const typingIndicator = document.getElementById('typing-indicator');

        if (typingIndicator.innerText.includes(data.nama)) {
            typingIndicator.innerText = '';
        }

        const bubble = `
            <div class="flex ${isKamu ? 'justify-end' : 'justify-start'}">
                <div class="${isKamu ? 'bg-green-500 text-white' : 'bg-gray-200 text-black'} px-4 py-2 rounded-2xl max-w-[70%]">
                    <p class="text-xs font-semibold mb-1">${data.nama}</p>
                    <p class="text-sm">${data.pesan}</p>
                </div>
            </div>
        `;
        const chatBox = document.getElementById('chat');
        chatBox.innerHTML += bubble;
        chatBox.scrollTop = chatBox.scrollHeight;
    });

    // Typing
    let timeout = null;
    document.getElementById('pesan').addEventListener('input', function () {
        clearTimeout(timeout);
        let nama = document.getElementById('nama').value;
        fetch('/typing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nama })
        });

        timeout = setTimeout(() => {
            document.getElementById('typing-indicator').innerText = '';
        }, 2000);
    });

    channel.bind('typing-event', function (data) {
        const namaKamu = document.getElementById('nama').value.trim();
        if (data.nama !== namaKamu) {
            document.getElementById('typing-indicator').innerText = `${data.nama} sedang mengetik...`;
        }
    });

    // Kirim Pesan
    function kirim() {
        const nama = document.getElementById('nama').value;
        const pesan = document.getElementById('pesan').value;

        if (!pesan.trim()) return;

        document.getElementById('kirim-text').innerText = 'Mengirim...';
        document.getElementById('loading-icon').classList.remove('hidden');

        fetch('/kirim', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nama, pesan })
        }).then(() => {
            document.getElementById('pesan').value = '';
            document.getElementById('kirim-text').innerText = 'Kirim';
            document.getElementById('loading-icon').classList.add('hidden');
        });
    }

    // Prompt nama pengguna saat load pertama kali
    window.onload = () => {
        const nama = prompt("Masukkan namamu:");
        document.getElementById('nama').value = nama ?? 'Anonim';
    };
</script>

</body>
</html>



{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyChat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="w-full max-w-md shadow-lg rounded-lg bg-white flex flex-col h-[90vh]">
        <div class="bg-green-600 text-white p-4 rounded-t-lg text-center font-semibold text-lg">
            Mychat
        </div>

        <div id="chat" class="flex-1 overflow-y-auto px-4 py-2 space-y-2 bg-gray-50">
            <!-- Pesan akan ditampilkan di sini -->
            <div id="typing-indicator" class="text-sm text-gray-500 italic"></div>

        </div>

        <div class="p-4 border-t flex gap-2">
            <input id="nama" type="text" placeholder="Nama"
                class="border rounded px-3 py-2 w-1/3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
            <input id="pesan" type="text" placeholder="Tulis pesan..."
                class="border rounded px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-green-400" />
                <button id="kirim-btn"
                onclick="kirim()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center gap-2 cursor-pointer">
                <span id="kirim-text">Kirim</span>
                <svg id="loading-icon" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                    <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8z" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        
        Pusher.logToConsole = false;
        var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        var channel = pusher.subscribe('chat-channel');
        channel.bind('chat-event', function (data) {
    let namaKamu = document.getElementById('nama').value.trim();
    let isKamu = (data.nama === namaKamu);

    let indikator = document.getElementById('typing-indicator');
    if (indikator.innerText.includes(data.nama)) {
        indikator.innerText = '';
    }

    let bubble = `
        <div class="flex ${isKamu ? 'justify-end' : 'justify-start'}">
            <div class="${isKamu ? 'bg-green-500 text-white' : 'bg-white text-gray-800'} shadow px-4 py-2 rounded-lg max-w-[70%]">
                <p class="text-xs font-bold mb-1">${data.nama}</p>
                <p class="text-sm">${data.pesan}</p>
            </div>
        </div>
    `;

    let chatBox = document.getElementById('chat');
    chatBox.innerHTML += bubble;
    chatBox.scrollTop = chatBox.scrollHeight;
});
        channel.bind('chat-event', function (data) {
            let chatBox = document.getElementById('chat');
            chatBox.innerHTML += bubble;
            chatBox.scrollTop = chatBox.scrollHeight;
        });

        let timeout = null;

document.getElementById('pesan').addEventListener('input', function () {
    clearTimeout(timeout);

    let nama = document.getElementById('nama').value;
    fetch('/typing', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ nama: nama })
    });

    timeout = setTimeout(() => {
        document.getElementById('typing-indicator').innerText = '';
    }, 2000);
});

channel.bind('typing-event', function (data) {
    let namaKamu = document.getElementById('nama').value.trim();
    if (data.nama !== namaKamu) {
        document.getElementById('typing-indicator').innerText = `${data.nama} sedang mengetik...`;
    }
});

        function kirim() {
            let nama = document.getElementById('nama').value;
    let pesan = document.getElementById('pesan').value;

    // Aktifkan loading
    document.getElementById('kirim-text').innerText = 'Mengirim...';
    document.getElementById('loading-icon').classList.remove('hidden');

    fetch('/kirim', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ nama, pesan })
    }).then(() => {
        document.getElementById('pesan').value = '';
        document.getElementById('kirim-text').innerText = 'Kirim';
        document.getElementById('loading-icon').classList.add('hidden');
    });
        }
    </script>
</body>
</html> --}}
