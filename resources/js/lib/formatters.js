export function formatDateIndonesia(date) {
    if (!date) return '';
    if (typeof date == 'string' || date instanceof String) {
        let dateTemp = new Date(date);
        if (isNaN(dateTemp.getTime())) {
            return date;
        }
        date = dateTemp;
    }
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    };
    return date.toLocaleDateString('id-ID', options);
}

export function formatRp(number) {
    number = parseInt(number);
    if (number == NaN) {
        console.error('formatRp: parsed number is NaN');
        return null;
    }
    return number.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    });
}

export function formatDateTimeIndonesia(date) {
    if (!date) return '';
    if (typeof date == 'string' || date instanceof String) {
        let dateTemp = new Date(date);
        if (isNaN(dateTemp.getTime())) {
            return date;
        }
        date = dateTemp;
    }
    const options = {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    };
    return date.toLocaleDateString('id-ID', options).replaceAll('/', '-').replaceAll('.', ':').replace(',', '');
}

export function formatRelativeTime(dateString) {
    console.log(dateString);
    const now = new Date();
    const date = new Date(dateString);
    const diffInMs = now - date;
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));

    // Jika kurang dari 24 jam, tampilkan waktu relatif
    if (diffInHours < 24) {
        if (diffInMinutes < 1) {
            return 'Baru saja';
        } else if (diffInMinutes < 60) {
            return `${diffInMinutes} menit yang lalu`;
        } else {
            return `${diffInHours} jam yang lalu`;
        }
    }

    // Jika lebih dari 24 jam, tampilkan tanggal lengkap
    return formatDateTimeIndonesia(dateString);
}
