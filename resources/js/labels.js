import JsBarcode from 'jsbarcode';

document.addEventListener('DOMContentLoaded', () => {
    JsBarcode('.label-barcode', {
        format: 'CODE128',
        displayValue: false,
        height: 30,
        width: 1.2,
        margin: 0,
    }).init();
});
