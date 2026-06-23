type IconMap = {
    location: string;
    move: string;
    resize: string;
    edit: string;
}

interface IconFactoryInterface {
    create(icon: string, color: string, size?: number, background?: boolean): string;
    format(iconName: string): string;
}