/**
 * Typing Pad Component
 *
 * The box a 성도 types a verse into on 성경 필사. Pasting and dropping
 * text are refused, because the count is of verses typed, and the
 * server has no way to tell a pasted verse from a typed one.
 */
export class TypingPad {
    /**
     * Creates a new TypingPad instance.
     *
     * @param pad - The textarea carrying data-typing-pad
     */
    constructor(private pad: HTMLTextAreaElement) {
        this.pad.addEventListener('paste', (event) => event.preventDefault());
        this.pad.addEventListener('drop', (event) => event.preventDefault());
        this.pad.focus();
    }
}
