class DashIconFactory implements IconFactoryInterface {
    private className: string = 'edit-icon';
    private mapIconNameMap(icon: string) {
        const map: IconMap = {
            'location': 'location',
            'resize': 'leftright',
            'move': 'move',
            'edit': 'edit'
        }

        return map[icon as keyof IconMap] ?? icon;
    }

    public create(icon: string, color: string, size: number = 20, background: boolean = true): string {
        const iconName = this.mapIconNameMap(icon);

        return background ? this.withBackground(iconName, color, size) : this.withoutBackground(iconName, color, size);
    }

    public format(iconName: string): string {
        return iconName.toLowerCase().replaceAll(' ', '-');
    }

    private withoutBackground(iconName: string, color: string, size: number): string {
        return `<span style="font-size: ${size}px; color: ${color};" class="${this.className} dashicons dashicons-${iconName}"></span>`;
    }

    private withBackground(iconName: string, color: string, size: number): string {
        return `<span style="font-size: ${size}px; padding: 4px; display: flex; justify-content: center; align-items: center; background-color: ${color}; border-radius: 50%; color: white;" class="${this.className} dashicons dashicons-${iconName}"></span>`;
    }
}

export default DashIconFactory;