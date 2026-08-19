import JsBarcode from 'jsbarcode';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('barcode');
    if (el && el.dataset.value) {
        JsBarcode(el, el.dataset.value, {
            format: 'CODE128',
            displayValue: true,
            fontSize: 11,
            height: 40,
            width: 1.2,
            margin: 4,
        });
    }
});
