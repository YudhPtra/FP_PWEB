// ================================================= 
// Dark Mode Logic (Revisi Total untuk Toggle)
// =================================================

const toggleBtn = document.getElementById('darkModeToggle');
const body = document.body;

// --- FUNGSI UTAMA UNTUK MENERAPKAN TEMA ---
function applyTheme(theme) {
    if (theme === 'dark') {
        body.classList.add('dark-mode');
        // Update ikon & class tombol saat Dark
        if (toggleBtn) {
            toggleBtn.innerHTML = '☀️'; 
            toggleBtn.classList.remove('btn-outline-secondary');
            toggleBtn.classList.add('btn-outline-light');
        }
    } else {
        // PENTING: Menghilangkan class dark-mode untuk Light Mode
        body.classList.remove('dark-mode'); 
        // Update ikon & class tombol saat Light
        if (toggleBtn) {
            toggleBtn.innerHTML = '🌙';
            toggleBtn.classList.remove('btn-outline-light');
            toggleBtn.classList.add('btn-outline-secondary');
        }
    }
}


document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme) {
        // Jika ada tema tersimpan, terapkan
        applyTheme(savedTheme);
    } else {
        // Jika tidak ada tema tersimpan, cek preferensi sistem OS
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            applyTheme('light');
            localStorage.setItem('theme', 'light');
        } else {
            // Default ke Light Mode
            applyTheme('dark');
            localStorage.setItem('theme', 'dark');
        }
    }
});


// --- 2. EVENT LISTENER UNTUK TOMBOL TOGGLE ---
if(toggleBtn){
    toggleBtn.addEventListener('click', () => {
        // Cek status tema saat ini dari class body, bukan dari localStorage
        const isCurrentlyDark = body.classList.contains('dark-mode');
        
        if (isCurrentlyDark) {
            // Ubah ke Light Mode
            applyTheme('light');
            localStorage.setItem('theme', 'light');
            console.log("Tema disetel ke: light"); // Debugging
        } else {
            // Ubah ke Dark Mode
            applyTheme('dark');
            localStorage.setItem('theme', 'dark');
            console.log("Tema disetel ke: dark"); // Debugging
        }
    });
}
