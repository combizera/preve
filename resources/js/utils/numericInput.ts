export function filterNumericInput(event: KeyboardEvent) {
    const allowedKeys = ['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'];

    if (allowedKeys.includes(event.key)) {
        return;
    }

    if (!/[0-9]/.test(event.key)) {
        event.preventDefault();
    }
}
