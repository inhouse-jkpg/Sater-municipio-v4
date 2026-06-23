import Main from "./main";

declare const wp: any;

const focusAttribute = "data-js-focus-axis";

// Init functionality after the DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    wp.media.view.Modal.prototype.on("open", () => {
        setTimeout(() => {
            const focusX = document.querySelector(`input[${focusAttribute}="x"]`) as HTMLInputElement;
            const focusY = document.querySelector(`input[${focusAttribute}="y"]`) as HTMLInputElement;
            const image = document.querySelector(`img.details-image:not(.icon)`) as HTMLImageElement;
            
            if (image && focusX && focusY) {
                const attachmentId = focusX.dataset.attachmentId;
                const attachment = wp.media.attachment(attachmentId);

                if (!checkAttachment(attachment)) {
                    return;
                }

                new Main(image, focusX, focusY);
            }
        }, 300);
    });
});

// Check that the attachment is an image and not an SVG
const checkAttachment = (attachment: any): boolean => {
    return (
        !!attachment &&
        !!attachment.get('mime') &&
        attachment.get('mime').includes("image") &&
        !attachment.get('mime').includes("image/svg+xml")
    );
};