class Main {
    private defaultX = 50;
    private defaultY = 50;

    constructor(
        private image: HTMLImageElement,
        private focusX: HTMLInputElement,
        private focusY: HTMLInputElement
    ) {
        if (image.complete) {
            this.init();
        } else {
            image.addEventListener("load", () => {
                this.init();
            });
        }
    }

    // Init functionality after image is loaded
    private init() {
        const [container, imageWrapper, marker] = this.structureMarkup();

        let xPercent = parseFloat(this.focusX.value) || this.defaultX;
        let yPercent = parseFloat(this.focusY.value) || this.defaultY;

        this.updateMarkerPosition(xPercent, yPercent, marker);

        this.image.addEventListener("click", (event) => {
            const rect = this.image.getBoundingClientRect();
            
            const percentX = (event.offsetX / rect.width) * 100;
            const percentY = (event.offsetY / rect.height) * 100;
            
            this.updateMarkerPosition(percentX, percentY, marker);
            this.focusX.value = percentX.toFixed(2);
            this.focusY.value = percentY.toFixed(2);
            this.focusX.dispatchEvent(new Event("change", { bubbles: true }));
            this.focusY.dispatchEvent(new Event("change", { bubbles: true }));
        });
    }

    // Sets the markers position
    private updateMarkerPosition(xPercent: number, yPercent: number, marker: HTMLDivElement) {
        marker.style.left = `${xPercent}%`;
        marker.style.top = `${yPercent}%`;
    }

    // Creates the markup and move elements to make functionality work
    private structureMarkup() {
        const container = this.createDiv('wpmu-focus-point__container');
        const imageWrapper = this.createDiv('wpmu-focus-point__image-wrapper');
        const marker = this.createDiv('wpmu-focus-point__marker');

        this.image.insertAdjacentElement("beforebegin", container);
        imageWrapper.appendChild(this.image);
        container.appendChild(imageWrapper);
        imageWrapper.appendChild(marker);

        return [container, imageWrapper, marker];
    }

    private createDiv(className: string): HTMLDivElement {
        const div = document.createElement('div');
        div.className = className;
        return div;
    }
}

export default Main;