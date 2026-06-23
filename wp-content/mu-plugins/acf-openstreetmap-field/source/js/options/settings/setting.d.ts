import MapStyle from "./mapStyle";

interface Setting {
    getValue(): string;
    save(): string;
    load(value: string): void;
}