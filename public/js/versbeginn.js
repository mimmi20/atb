"use strict";
const versbeginn = document.querySelectorAll('[name="versbeginn"]');
const versbeginnDatum = document.getElementById('versbeginn_datum');
if (versbeginn && versbeginnDatum) {
    versbeginn.forEach((element) => {
        element.addEventListener('click', (event) => {
            if (element.value === 'datum') {
                versbeginnDatum.setAttribute('required', 'required');
            }
            else {
                versbeginnDatum.removeAttribute('required');
            }
        });
    });
}
