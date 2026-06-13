import './bootstrap';

document.querySelectorAll('[data-password-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) return;

        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.textContent = visible ? 'Lihat' : 'Sembunyikan';
        button.setAttribute('aria-label', visible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        button.setAttribute('aria-pressed', String(!visible));
        input.focus({preventScroll: true});
    });
});

document.querySelector('[data-gps]')?.addEventListener('click', async () => {
    const button = document.querySelector('[data-gps]');
    const status = document.querySelector('[data-gps-status]');
    const latitude = document.querySelector('[name=latitude]');
    const longitude = document.querySelector('[name=longitude]');

    const updateStatus = (message, type = 'muted') => {
        status.textContent = message;
        status.className = type === 'error' ? 'field-error' : 'muted';
    };

    if (!window.isSecureContext) {
        updateStatus('GPS hanya diizinkan melalui HTTPS atau localhost. Buka aplikasi menggunakan http://localhost:8000.', 'error');
        return;
    }

    if (!('geolocation' in navigator)) {
        updateStatus('Browser atau perangkat ini tidak mendukung GPS.', 'error');
        return;
    }

    if (navigator.permissions) {
        const permission = await navigator.permissions.query({name: 'geolocation'}).catch(() => null);
        if (permission?.state === 'denied') {
            updateStatus('Izin lokasi diblokir. Aktifkan Location pada pengaturan situs browser, lalu muat ulang halaman.', 'error');
            return;
        }
    }

    button.disabled = true;
    button.textContent = 'Mengambil lokasi...';
    updateStatus('Menunggu lokasi perangkat. Proses dapat memerlukan beberapa detik.');

    navigator.geolocation.getCurrentPosition(({coords}) => {
        latitude.value = coords.latitude.toFixed(7);
        longitude.value = coords.longitude.toFixed(7);
        button.textContent = 'Perbarui GPS';
        button.disabled = false;
        updateStatus(`GPS tersimpan: ${latitude.value}, ${longitude.value} (akurasi sekitar ${Math.round(coords.accuracy)} meter)`);
    }, error => {
        const messages = {
            1: 'Izin lokasi ditolak. Aktifkan Location pada pengaturan situs browser.',
            2: 'Lokasi tidak tersedia. Aktifkan layanan lokasi/GPS pada perangkat dan coba lagi.',
            3: 'Pengambilan lokasi terlalu lama. Pastikan GPS aktif lalu coba lagi.',
        };
        button.textContent = 'Coba GPS Lagi';
        button.disabled = false;
        updateStatus(messages[error.code] ?? 'GPS tidak dapat diakses oleh browser.', 'error');
    }, {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 30000,
    });
});

document.querySelector('[data-image-input]')?.addEventListener('change', event => {
    const file = event.target.files[0];
    if (!file) return;

    const image = document.querySelector('[data-image-preview-img]');
    const placeholder = document.querySelector('.upload-placeholder');
    const name = document.querySelector('[data-image-name]');
    image.src = URL.createObjectURL(file);
    image.hidden = false;
    placeholder.hidden = true;
    name.textContent = file.name;
});
