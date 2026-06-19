<div class="max-w-2xl">
    <!-- Header info -->
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-violet-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">AI Creative Assistant</p>
                <p class="text-xs text-gray-400">Powered by DeepSeek — data real-time dari database</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500"><span class="font-semibold text-gray-800" id="remaining"><?= $remaining ?></span> / <?= $remaining + $usedToday ?> tersisa hari ini</p>
        </div>
    </div>

    <!-- Contoh pertanyaan -->
    <?php if (empty($history)): ?>
    <div class="mb-4">
        <p class="text-xs text-gray-400 mb-2">Contoh pertanyaan:</p>
        <div class="flex flex-wrap gap-2">
            <?php foreach ([
                'Siapa designer paling produktif bulan ini?',
                'Berapa SLA achievement bulan ini?',
                'Task apa yang overdue?',
                'Siapa yang sedang overload?',
                'Produk apa yang paling banyak request?',
            ] as $q): ?>
            <button onclick="askSuggestion(this.textContent)"
                class="text-xs bg-gray-100 hover:bg-violet-100 hover:text-violet-700 text-gray-600 px-3 py-1.5 rounded-full transition-colors cursor-pointer">
                <?= htmlspecialchars($q) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Chat box -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div id="chatBox" class="p-4 space-y-3 h-80 overflow-y-auto">
            <?php if (empty($history)): ?>
            <div class="flex justify-start">
                <div class="bg-gray-100 text-gray-700 text-sm px-4 py-2.5 rounded-xl max-w-sm">
                    Halo! Tanya apa saja tentang data tim kreatif kamu.
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($history as $msg): ?>
            <div class="flex <?= $msg['role'] === 'user' ? 'justify-end' : 'justify-start' ?>">
                <div class="text-sm px-4 py-2.5 rounded-xl max-w-sm <?= $msg['role'] === 'user' ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-800' ?>">
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Input -->
        <div class="border-t border-gray-100 p-3 flex gap-2" id="inputArea">
            <input type="text" id="chatInput" placeholder="Tanya tentang request, SLA, workload, KPI..."
                class="flex-1 border border-gray-200 rounded-lg px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                <?= $remaining <= 0 ? 'disabled' : '' ?>>
            <button id="sendBtn" onclick="sendMessage()"
                class="bg-violet-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-violet-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                <?= $remaining <= 0 ? 'disabled' : '' ?>>
                Kirim
            </button>
        </div>
        <?php if ($remaining <= 0): ?>
        <p class="px-4 pb-3 text-xs text-red-500 text-center">Limit harian tercapai. Reset besok.</p>
        <?php endif; ?>
    </div>
</div>

<script>
var chatBox    = document.getElementById('chatBox');
var chatInput  = document.getElementById('chatInput');
var sendBtn    = document.getElementById('sendBtn');
var sessionId  = '<?= htmlspecialchars($sessionId) ?>';
var remaining  = <?= $remaining ?>;
var appUrl     = '<?= APP_URL ?>';
var csrfToken  = '<?= Csrf::token() ?>';

chatBox.scrollTop = chatBox.scrollHeight;

chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

function askSuggestion(text) {
    chatInput.value = text;
    sendMessage();
}

function appendMsg(role, text) {
    var wrap = document.createElement('div');
    wrap.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
    var bubble = document.createElement('div');
    bubble.className = 'text-sm px-4 py-2.5 rounded-xl max-w-sm ' + (role === 'user' ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-800');
    bubble.textContent = text;
    wrap.appendChild(bubble);
    chatBox.appendChild(wrap);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function setLoading(state) {
    sendBtn.disabled  = state || remaining <= 0;
    chatInput.disabled = state || remaining <= 0;
}

function sendMessage() {
    var msg = chatInput.value.trim();
    if (!msg || remaining <= 0) return;

    appendMsg('user', msg);
    chatInput.value = '';
    setLoading(true);

    var body = 'message=' + encodeURIComponent(msg) + '&session_id=' + encodeURIComponent(sessionId) + '&_csrf_token=' + encodeURIComponent(csrfToken);

    fetch(appUrl + '/ai/chat/send', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            appendMsg('assistant', 'Error: ' + data.error);
        } else {
            appendMsg('assistant', data.reply || 'Tidak ada jawaban.');
            remaining = remaining - 1;
            var rem = document.getElementById('remaining');
            if (rem) rem.textContent = remaining;
        }
        setLoading(false);
    })
    .catch(function() {
        appendMsg('assistant', 'Koneksi gagal. Coba lagi.');
        setLoading(false);
    });
}
</script>