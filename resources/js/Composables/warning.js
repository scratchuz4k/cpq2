import { ref, createApp } from 'vue';
import WarningNote from '@/Components/WarningNote.vue';

export const checkWarnings = (warningProviders, link) => {
    if (!warningProviders.some((obj) => Object.values(obj).includes(true))) {
        if (link) {
            window.location.href = link;
        }
        return;
    }

    const wrapper = document.createElement('div');
    let result = swal
        .fire({
            html: '<div id="warning-note-container"></div>',
            showCancelButton: false,
            confirmButtonText: 'Continue',
            showCloseButton: true,
            width: 640,
            customClass: {
                htmlContainer: 'provider-warning'
            }
        })
        .then((result) => {
            if (result.isConfirmed) {
                if (link) {
                    window.location.href = link;
                } else {
                    return;
                }
            } else if (result.dismiss === swal.DismissReason.cancel) {
                isLoading.value = false;
                return;
            }
        });

    // Wait for the modal to be rendered
    swal.getHtmlContainer();

    // Dynamically mount the Vue component into the placeholder div
    const container = document.getElementById('warning-note-container');
    if (container) {
        const app = createApp(WarningNote, { warnings: warningProviders });
        app.mount(container);
    }
};
