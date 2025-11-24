// assets/js/dashboard.js
// Frontend logic for dashboard (method 2): fetch API, render stats, chart, recent activity.
// Expects window.currentUser to be set in the page via PHP.
const API_BASE_URL = '../config/';

async function loadDashboardStats() {
    try {
        const res = await fetch(API_BASE_URL + 'get_dashboard_stats.php', { credentials: 'same-origin' });
        const data = await res.json();
        console.log('dashboard API response:', data);

        if (!data || !data.success) {
            showEmptyState();
            return;
        }

        // Update stats
        document.getElementById('totalKaryawan').textContent = data.stats.total_karyawan || 0;
        document.getElementById('hadirHariIni').textContent = data.stats.hadir_hari_ini || 0;

        // Izin / Sakit - API returns stats.izin_sakit
        document.getElementById('terlambat').textContent = data.stats.izin_sakit || 0;

        // Tidak hadir
        document.getElementById('tidakHadir').textContent = data.stats.tidak_hadir || 0;

        // Percentages & trend
        const total = data.stats.total_karyawan || 0;
        document.getElementById('karyawanTrend').textContent = total > 0 ? 'Data terbaru' : 'Belum ada data';
        document.getElementById('persentaseHadir').textContent = total > 0 ? Math.round((data.stats.hadir_hari_ini / total) * 100) + '% kehadiran' : 'Belum ada data';
        document.getElementById('persentaseTerlambat').textContent = total > 0 ? Math.round((data.stats.izin_sakit / total) * 100) + '% dari total' : 'Belum ada data';
        document.getElementById('persentaseTidakHadir').textContent = total > 0 ? Math.round((data.stats.tidak_hadir / total) * 100) + '% dari total' : 'Belum ada data';

        updateRecentActivity(data.recent_activity || []);
        updateWeeklyChart(data.weekly_data || []);
        document.getElementById('notificationCount').textContent = data.notifications || 0;
    } catch (err) {
        console.error('Error loading dashboard stats:', err);
        showEmptyState();
    }
}

function updateRecentActivity(activities) {
    const container = document.getElementById('recentActivity');
    if (!container) return;
    if (!activities || activities.length === 0) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-clock text-4xl mb-4 opacity-50"></i><p class="text-lg font-medium">Belum ada aktivitas hari ini</p><p class="text-sm">Aktivitas absensi akan muncul di sini</p></div>`;
        return;
    }
    container.innerHTML = activities.map(a => {
        const icon = a.status === 'hadir' ? 'fa-check' : (a.status === 'izin' ? 'fa-calendar-times' : (a.status === 'sakit' ? 'fa-thermometer-half' : 'fa-question'));
        const color = a.status === 'hadir' ? 'bg-green-500' : (a.status === 'izin' ? 'bg-blue-500' : (a.status === 'sakit' ? 'bg-yellow-500' : 'bg-gray-500'));
        return `<div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 ${color} rounded-full flex items-center justify-center"><i class="fas ${icon} text-white text-xs"></i></div>
            <div class="flex-1"><p class="font-medium text-gray-800">${escapeHtml(a.nama)} ${getActivityText(a.status)}</p>
            <p class="text-sm text-gray-600">${a.waktu}${a.waktu_pulang ? ' • Pulang ' + a.waktu_pulang : ''}</p></div></div>`;
    }).join('');
}

function updateWeeklyChart(weeklyData) {
    const container = document.getElementById('weeklyChart');
    if (!container) return;
    if (!weeklyData || weeklyData.length === 0) {
        container.innerHTML = `<div class="empty-state w-full"><i class="fas fa-chart-bar text-4xl mb-4 opacity-50"></i><p class="text-lg font-medium">Belum ada data kehadiran</p><p class="text-sm">Grafik akan muncul setelah ada data absensi</p></div>`;
        return;
    }
    console.log('weeklyData:', weeklyData);
    const values = weeklyData.map(d => Number(d.value) || 0);
    let maxValue = Math.max(...values);
    if (maxValue === 0) maxValue = 1;

    const barsHtml = weeklyData.map(day => {
        const v = Number(day.value) || 0;
        const heightPercent = Math.round((v / maxValue) * 80);
        return `<div class="flex flex-col items-center" style="width:12%"><div style="width:100%; height:${heightPercent}%; min-height:4px;" class="${v>0 ? 'bg-blue-900' : 'bg-gray-300'} rounded-t"></div><span class="text-xs text-gray-600 mt-2">${escapeHtml(day.day)}</span></div>`;
    }).join('');
    container.innerHTML = `<div class="flex items-end justify-between h-48 px-2">${barsHtml}</div>`;
}

function getActivityText(status){ switch(status){ case 'hadir': return 'hadir'; case 'izin': return 'minta izin'; case 'sakit': return 'sakit'; case 'alpa': return 'alpa'; default: return 'aktivitas'; } }
function escapeHtml(s){ if(!s) return ''; return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;'); }

function showEmptyState(){
    if(document.getElementById('totalKaryawan')) document.getElementById('totalKaryawan').textContent='0';
    if(document.getElementById('hadirHariIni')) document.getElementById('hadirHariIni').textContent='0';
    if(document.getElementById('terlambat')) document.getElementById('terlambat').textContent='0';
    if(document.getElementById('tidakHadir')) document.getElementById('tidakHadir').textContent='0';
    if(document.getElementById('karyawanTrend')) document.getElementById('karyawanTrend').textContent='Belum ada data';
    if(document.getElementById('notificationCount')) document.getElementById('notificationCount').textContent='0';
}

window.addEventListener('load', () => {
    if (window.currentUser) {
        const nameEl = document.getElementById('userName');
        const roleEl = document.getElementById('userRole');
        if (nameEl) nameEl.textContent = window.currentUser.name || 'Admin';
        if (roleEl) roleEl.textContent = window.currentUser.role || 'Administrator';
    }
    loadDashboardStats();
    setInterval(loadDashboardStats, 30000);
});