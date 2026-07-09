// validasi untuk vuetify form.
export const validators = {
    /*
    v tidak boleh null, undefined, NaN atau string kosong.
    kalau fieldName adalah string dan tidak kosong maka cantumkan nama field pada pesan error validasi.
    */
    required: (v, fieldName) => {
        let isValid = v != null && v != undefined && !Number.isNaN(v);
        if (typeof v == 'string' || v instanceof String) {
            isValid = isValid && v?.trim() != '';
        }
        if ((typeof fieldName == 'string' || fieldName instanceof String) && fieldName.trim() != '') {
            return isValid || `${fieldName} harus diisi`;
        } else {
            return isValid || 'Harus diisi';
        }
    },
};
