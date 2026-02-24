export function formatDateIndonesia(date) {
    if (!date) return '';
    if (typeof date == 'string' || date instanceof String) {
        let dateTemp = new Date(date);
        if(isNaN(dateTemp.getTime())) {
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
    if(number == NaN) {
        console.error("formatRp: parsed number is NaN");
        return null;
    }
    return number.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    });
}

export function formatDateTimeIndonesia(date) {
    if (!date) return '';
    if (typeof date == 'string' || date instanceof String) {
        let dateTemp = new Date(date);
        if(isNaN(dateTemp.getTime())) {
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
    return date.toLocaleDateString('id-ID', options)
        .replaceAll('/', '-')
        .replaceAll('.', ':')
        .replace(',', '');
}