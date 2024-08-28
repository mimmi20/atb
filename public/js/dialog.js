"use strict";
class Dialog {
    initLayer() {
        const layerTriggers = document.querySelectorAll('.info-layer-trigger');
        const that = this;
        layerTriggers.forEach(function (layerTrigger) {
            const layerId = layerTrigger.getAttribute('data-layer');
            if (layerId === null) {
                console.error('no layer-id');
                return;
            }
            const dialog = document.getElementById(layerId);
            if (!(dialog instanceof HTMLDialogElement)) {
                console.log(`layer ${layerId} not found`);
                return;
            }
            layerTrigger.addEventListener('click', (event) => {
                console.log(`Button was clicked`);
                event.preventDefault();
                event.stopPropagation();
                dialog.showModal();
                dialog.setAttribute('aria-hidden', 'false');
            });
            that.initDialog(dialog);
        });
    }
    initPopover() {
        const popoverTriggers = document.querySelectorAll('[popovertarget]');
        popoverTriggers.forEach(function (popoverTrigger) {
            popoverTrigger.addEventListener('click', (event) => {
                console.log(`Button was clicked`);
                event.preventDefault();
                event.stopPropagation();
            });
        });
    }
    initDialog(dialog) {
        const closeButtons = dialog.querySelectorAll('[data-bs-dismiss="modal"]');
        closeButtons.forEach(function (button) {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                dialog.close();
                dialog.setAttribute('aria-hidden', 'true');
            });
        });
    }
    init() {
        this.initLayer();
        this.initPopover();
    }
}
(function () {
    const dialog = new Dialog();
    dialog.init();
})();
