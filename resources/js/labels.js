import JsBarcode from 'jsbarcode';

document.querySelectorAll('.label-barcode').forEach((el) => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    el.appendChild(svg);
    JsBarcode(svg, el.dataset.value, {
        format: 'CODE128',
        width: 1.5,
        height: 36,
        displayValue: false,
        margin: 0,
    });
});
