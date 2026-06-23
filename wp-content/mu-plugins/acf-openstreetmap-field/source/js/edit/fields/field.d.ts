interface Field {
    getContainer(): HTMLElement|null;
    getField(): HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement|null;
    setValue(value: string|boolean): void;
    getValue(): string|boolean;
    showField(): void;
    hideField(): void;
}