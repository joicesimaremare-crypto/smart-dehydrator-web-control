<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kontrol Sistem Pengering</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        body {
            background: #070b14;
            background-image:
                radial-gradient(60rem 30rem at 85% -10%, rgba(249,115,22,.18), transparent 60%),
                radial-gradient(50rem 30rem at -10% 110%, rgba(34,211,238,.15), transparent 60%);
            background-attachment: fixed;
        }
        .glass {
            background: linear-gradient(145deg, rgba(255,255,255,.07), rgba(255,255,255,.02));
            border: 1px solid rgba(255,255,255,.09);
            backdrop-filter: blur(14px);
        }
        .card { transition: all .35s ease; position: relative; overflow: hidden; }
        .card:hover { transform: translateY(-3px); }

        /* kondisi menyala */
        .on-heater {
            border-color: rgba(251,146,60,.55);
            background: linear-gradient(145deg, rgba(249,115,22,.22), rgba(255,255,255,.02));
            box-shadow: 0 10px 40px -10px rgba(249,115,22,.55), inset 0 0 30px rgba(249,115,22,.08);
        }
        .on-fan {
            border-color: rgba(34,211,238,.55);
            background: linear-gradient(145deg, rgba(6,182,212,.22), rgba(255,255,255,.02));
            box-shadow: 0 10px 40px -10px rgba(6,182,212,.55), inset 0 0 30px rgba(6,182,212,.08);
        }

        /* animasi */
        .spin { animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .wave { animation: rise 1.6s ease-in-out infinite; }
        .wave:nth-child(2) { animation-delay: .3s; }
        .wave:nth-child(3) { animation-delay: .6s; }
        @keyframes rise {
            0%   { transform: translateY(4px); opacity: .15; }
            50%  { opacity: 1; }
            100% { transform: translateY(-5px); opacity: .15; }
        }
        .blink { animation: blink 1.4s ease-in-out infinite; }
        @keyframes blink { 50% { opacity: .3; } }

        /* tombol switch */
        .sw { position: relative; width: 74px; height: 36px; border-radius: 999px; transition: background .3s; cursor: pointer; }
        .sw .knob {
            position: absolute; top: 4px; left: 4px; width: 28px; height: 28px; border-radius: 50%;
            background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.4); transition: transform .3s cubic-bezier(.4,1.4,.6,1);
        }
        .sw.active .knob { transform: translateX(38px); }
        .sw span { position: absolute; top: 50%; transform: translateY(-50%); font-size: 10px; font-weight: 800; letter-spacing: .05em; }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased">
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

    <!-- HEADER -->
    <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-400 flex items-center justify-center shadow-lg shadow-orange-500/30">
                <svg viewBox="0 0 24 24" class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3c1 3-2 4-2 7a3 3 0 0 0 6 0c0-1-.5-2-1-3 3 1 5 4 5 8a8 8 0 0 1-16 0c0-4 3-7 8-12z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Kontrol Sistem Pengering</h1>
                <p class="text-slate-400 text-sm">Monitoring &amp; kontrol heater dan kipas secara real-time</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="glass rounded-2xl px-4 py-2 text-right hidden sm:block">
                <div id="clock" class="text-lg font-bold leading-tight tabular-nums">--:--:--</div>
                <div id="date" class="text-[11px] text-slate-400">&nbsp;</div>
            </div>
            <div id="espStatus" class="glass rounded-2xl px-4 py-3 text-sm font-medium flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span> Memeriksa...
            </div>
        </div>
    </header>

    <!-- STATUS SISTEM -->
    <section id="sysBanner" class="glass rounded-3xl p-5 sm:p-6 mb-6 flex items-center gap-4 transition-all">
        <div id="sysIcon" class="w-12 h-12 rounded-2xl bg-slate-700/60 flex items-center justify-center text-2xl">⏸️</div>
        <div class="flex-1">
            <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Status Sistem</div>
            <div id="sysTitle" class="text-xl font-bold">Standby</div>
            <div id="sysDesc" class="text-sm text-slate-400">Semua perangkat dalam keadaan mati</div>
        </div>
    </section>

    <!-- RINGKASAN -->
    <section class="grid grid-cols-2 gap-4 mb-8">
        <div class="glass rounded-3xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Heater Aktif</span>
                <span class="w-8 h-8 rounded-xl bg-orange-500/15 text-orange-400 flex items-center justify-center text-sm">🔥</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span id="countHeater" class="text-4xl font-extrabold text-orange-400">0</span>
                <span class="text-slate-500 font-semibold">/ 2</span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 mt-3 overflow-hidden">
                <div id="barHeater" class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-300 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>
        <div class="glass rounded-3xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Kipas Aktif</span>
                <span class="w-8 h-8 rounded-xl bg-cyan-500/15 text-cyan-400 flex items-center justify-center text-sm">💨</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span id="countFan" class="text-4xl font-extrabold text-cyan-400">0</span>
                <span class="text-slate-500 font-semibold">/ 2</span>
            </div>
            <div class="h-1.5 rounded-full bg-white/10 mt-3 overflow-hidden">
                <div id="barFan" class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-300 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>
    </section>

    <!-- PERANGKAT -->
    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-sm uppercase tracking-widest text-slate-400 font-semibold">Perangkat</h2>
        <div class="flex-1 h-px bg-white/10"></div>
    </div>
    <section id="cards" class="grid sm:grid-cols-2 gap-5 mb-8"></section>

    <!-- MATIKAN SEMUA -->
    <button onclick="allOff()"
        class="w-full py-4 rounded-2xl font-bold tracking-wide bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-500 hover:to-rose-400 shadow-lg shadow-red-600/25 active:scale-[.98] transition flex items-center justify-center gap-2">
        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/></svg>
        MATIKAN SEMUA
    </button>

    <p class="text-center text-slate-500 text-xs mt-6">Data diperbarui otomatis setiap 3 detik</p>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const headers = { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' };

/* ---------- ICON SVG ---------- */
function fanIcon(on) {
    const blade = 'M32 32 C 24 22, 26 8, 34 6 C 42 8, 40 22, 32 32 Z';
    return `
    <svg viewBox="0 0 64 64" class="w-16 h-16 ${on ? 'spin text-cyan-300' : 'text-slate-500'}">
        <circle cx="32" cy="32" r="29" fill="none" stroke="currentColor" stroke-width="2" opacity=".35"/>
        <g fill="currentColor">
            <path d="${blade}"/>
            <path d="${blade}" transform="rotate(90 32 32)"/>
            <path d="${blade}" transform="rotate(180 32 32)"/>
            <path d="${blade}" transform="rotate(270 32 32)"/>
        </g>
        <circle cx="32" cy="32" r="5" fill="#0b1220" stroke="currentColor" stroke-width="2"/>
    </svg>`;
}

function heaterIcon(on) {
    const cls = on ? 'text-orange-300' : 'text-slate-500';
    const wave = (x) => `<path class="${on ? 'wave' : ''}" d="M${x} 28 C${x - 5} 22 ${x + 5} 17 ${x} 10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" ${on ? '' : 'opacity=".25"'}/>`;
    return `
    <svg viewBox="0 0 64 64" class="w-16 h-16 ${cls}">
        <g>${wave(18)}${wave(32)}${wave(46)}</g>
        <rect x="6" y="34" width="52" height="20" rx="6" fill="none" stroke="currentColor" stroke-width="3"/>
        <path d="M14 44h36" stroke="currentColor" stroke-width="3" stroke-linecap="round" ${on ? '' : 'opacity=".6"'}/>
        <path d="M14 39.5h36M14 48.5h36" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".5"/>
    </svg>`;
}

/* ---------- RENDER ---------- */
function render(data) {
    // status ESP32
    document.getElementById('espStatus').innerHTML = data.esp_online
        ? '<span class="w-2.5 h-2.5 rounded-full bg-emerald-400 blink"></span><span class="text-emerald-300">ESP32 Online</span>'
        : '<span class="w-2.5 h-2.5 rounded-full bg-red-500"></span><span class="text-red-300">ESP32 Offline</span>';

    // hitung
    const heaterOn = data.devices.filter(d => d.type === 'heater' && d.state).length;
    const fanOn    = data.devices.filter(d => d.type === 'fan' && d.state).length;
    document.getElementById('countHeater').textContent = heaterOn;
    document.getElementById('countFan').textContent = fanOn;
    document.getElementById('barHeater').style.width = (heaterOn / 2 * 100) + '%';
    document.getElementById('barFan').style.width = (fanOn / 2 * 100) + '%';

    // banner status sistem
    let icon = '⏸️', title = 'Standby', desc = 'Semua perangkat dalam keadaan mati', tone = '';
    if (heaterOn && fanOn) {
        icon = '♨️'; title = 'Pengeringan Berjalan'; desc = `${heaterOn} heater dan ${fanOn} kipas aktif`; tone = 'ok';
    } else if (heaterOn && !fanOn) {
        icon = '⚠️'; title = 'Heater Menyala Tanpa Kipas'; desc = 'Disarankan menyalakan kipas agar panas merata'; tone = 'warn';
    } else if (!heaterOn && fanOn) {
        icon = '💨'; title = 'Sirkulasi Udara'; desc = `${fanOn} kipas aktif, heater mati`; tone = 'fan';
    }
    const banner = document.getElementById('sysBanner');
    banner.className = 'glass rounded-3xl p-5 sm:p-6 mb-6 flex items-center gap-4 transition-all ' +
        ({ ok: 'on-heater', warn: 'border-amber-400/50', fan: 'on-fan' }[tone] || '');
    document.getElementById('sysIcon').textContent = icon;
    document.getElementById('sysIcon').className = 'w-12 h-12 rounded-2xl flex items-center justify-center text-2xl ' +
        ({ ok: 'bg-orange-500/25', warn: 'bg-amber-500/25', fan: 'bg-cyan-500/25' }[tone] || 'bg-slate-700/60');
    document.getElementById('sysTitle').textContent = title;
    document.getElementById('sysDesc').textContent = desc;

    // kartu perangkat
    document.getElementById('cards').innerHTML = data.devices.map(d => {
        const heater = d.type === 'heater';
        const onCls  = d.state ? (heater ? 'on-heater' : 'on-fan') : '';
        const swBg   = d.state ? (heater ? 'bg-gradient-to-r from-orange-500 to-amber-400' : 'bg-gradient-to-r from-cyan-500 to-sky-400') : 'bg-slate-700';
        const label  = heater ? 'Pemanas' : 'Sirkulasi Udara';
        const badge  = d.state
            ? `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold ${heater ? 'bg-orange-500/25 text-orange-300' : 'bg-cyan-500/25 text-cyan-300'}">● MENYALA</span>`
            : `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-white/10 text-slate-400">○ MATI</span>`;
        return `
        <div class="card glass rounded-3xl p-6 ${onCls}">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">${label}</div>
                    <div class="text-2xl font-extrabold mt-0.5">${d.name}</div>
                    <div class="text-xs text-slate-500 mt-1">GPIO ${d.gpio}</div>
                </div>
                ${heater ? heaterIcon(d.state) : fanIcon(d.state)}
            </div>
            <div class="flex items-center justify-between mt-6">
                ${badge}
                <div class="sw ${swBg} ${d.state ? 'active' : ''}" onclick="toggle(${d.id})">
                    <span class="left-3 text-white ${d.state ? '' : 'hidden'}">ON</span>
                    <span class="right-3 text-slate-400 ${d.state ? 'hidden' : ''}">OFF</span>
                    <div class="knob"></div>
                </div>
            </div>
        </div>`;
    }).join('');
}

/* ---------- JAM ---------- */
function tick() {
    const n = new Date();
    document.getElementById('clock').textContent = n.toLocaleTimeString('id-ID', { hour12: false });
    document.getElementById('date').textContent = n.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}
tick(); setInterval(tick, 1000);

/* ---------- API ---------- */
async function load() {
    try { render(await (await fetch('/devices', { headers })).json()); } catch (e) {}
}
async function toggle(id) {
    render(await (await fetch(`/devices/${id}/toggle`, { method: 'POST', headers })).json());
}
async function allOff() {
    if (!confirm('Matikan semua perangkat?')) return;
    render(await (await fetch('/devices/all-off', { method: 'POST', headers })).json());
}

load();
setInterval(load, 3000);
</script>
</body>
</html>