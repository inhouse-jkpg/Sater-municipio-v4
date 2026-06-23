class Edit implements EditInterface {
    private activeEditable: Editable|null = null;
    constructor(private container: HTMLElement) {
        this.setupCancel();
        this.setupSave();
        this.setupDelete();
    }

    private setupDelete(): void {
        this.container.querySelector('[data-js-field-edit-delete]')?.addEventListener('click', () => {
            if (this.getActiveEditable()) {
                this.getActiveEditable()!.delete();
            }
        });
    }

    private setupSave(): void {
        this.container.querySelector('[data-js-field-edit-save]')?.addEventListener('click', () => {
            if (this.getActiveEditable()) {
                this.getActiveEditable()!.save();
            }
        });
    }

    private setupCancel(): void {
        this.container.querySelector('[data-js-field-edit-cancel]')?.addEventListener('click', () => {
            this.runCancel();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.runCancel();
            }
        });
    }

    public setActiveEditable(editable: Editable|null): void {
        if (this.getActiveEditable()) {
            this.getActiveEditable()!.hideFields();
        }

        this.activeEditable = editable;
    }

    public getActiveEditable(): Editable|null {
        return this.activeEditable;
    }

    private runCancel(): void {
        if (this.getActiveEditable()) {
            this.getActiveEditable()!.cancel();
        }
    }
}

export default Edit;