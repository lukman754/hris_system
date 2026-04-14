/**
 * app.js – UI Helpers: modal, table filter, delete confirm
 * HTML ada di PHP, JS hanya handle interaksi minimal
 */

/** Buka modal */
function openModal(id) {
    document.getElementById(id)?.classList.remove('hidden');
}

/** Tutup modal */
function closeModal(id) {
    document.getElementById(id)?.classList.add('hidden');
}

/** Filter baris tabel secara client-side */
function filterTable(tableId, query) {
    const q = query.toLowerCase();
    document.querySelectorAll(`#${tableId} tbody tr`).forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/** Konfirmasi hapus */
function confirmDelete(id, name) {
    if (confirm(`Hapus "${name}"? Tindakan ini tidak bisa dibatalkan.`)) {
        window.location.href = `?page=employees&action=delete&id=${encodeURIComponent(id)}`;
    }
}

/** Tutup modal saat klik backdrop */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) {
                backdrop.classList.add('hidden');
                // Hentikan kamera jika ada
                if (typeof stopCamera === 'function') stopCamera();
                if (typeof stopQR === 'function') stopQR();
            }
        });
    });
});
