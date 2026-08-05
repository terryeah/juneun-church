/**
 * Date Field Format Component
 *
 * Normalises a typed date into YYYY-MM-DD when the field loses focus,
 * so someone entering 19910210 or 1991/02/10 ends up with the format
 * the server expects. The field is a plain text input rather than a
 * native date picker, because a native picker displays its own order
 * according to the browser locale.
 *
 * Anything that does not look like a date is left exactly as typed, so
 * server-side validation still reports it rather than the field
 * silently rewriting a mistake into something plausible.
 */
export class DateFieldFormat {
    private field: HTMLInputElement;

    /**
     * Creates a new DateFieldFormat instance.
     *
     * @param field - Text input carrying the data-date-field attribute
     */
    constructor(field: HTMLInputElement) {
        this.field = field;
        this.field.addEventListener('blur', () => this.normalise());
    }

    /**
     * Rewrites the value in place once it parses as a date.
     */
    private normalise(): void {
        const formatted = this.format(this.field.value.trim());

        if (formatted !== null) {
            this.field.value = formatted;
        }
    }

    /**
     * Reads eight digits, with or without separators, as YYYY-MM-DD.
     *
     * @param value - Raw field value
     * @returns The formatted date, or null when it does not parse
     */
    private format(value: string): string | null {
        const digits = value.replace(/\D/g, '');

        if (digits.length !== 8) {
            return null;
        }

        const year = Number(digits.slice(0, 4));
        const month = Number(digits.slice(4, 6));
        const day = Number(digits.slice(6, 8));

        if (month < 1 || month > 12 || day < 1 || day > 31) {
            return null;
        }

        /** A real calendar check, so 19910231 is left for the server to reject. */
        const date = new Date(Date.UTC(year, month - 1, day));

        if (date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) {
            return null;
        }

        return `${digits.slice(0, 4)}-${digits.slice(4, 6)}-${digits.slice(6, 8)}`;
    }
}
