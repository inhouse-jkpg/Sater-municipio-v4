class FieldValidator {
    constructor(private language: any) {}
    public validateUrl(url: string): boolean {
        if (!url) {
            return true;
        }

        try {
            new URL(url);
            return true;
        } catch (error) {
            alert(`${this.language?.invalidUrl ?? 'Invalid URL'}, ${this.language?.shouldFollowFormat ?? 'should follow format'}: https://example.com`);
            return false;
        }
    }
}

export default FieldValidator;