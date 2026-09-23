/**
 * jsdom has no layout, so it ships no scrollIntoView. The Event nav scrolls
 * its lit tab into view on mount, which means every spec that draws the shell
 * would otherwise end in an unhandled rejection. Specs that care whether it
 * was called replace this with a spy of their own.
 */
Element.prototype.scrollIntoView = function scrollIntoView(): void {};

/**
 * jsdom has `<dialog>` but not its modal API. The account drawer opens with
 * `showModal()`, so these stand in with just enough to be observable: the
 * `open` attribute, and a `close` event like the browser's.
 */
HTMLDialogElement.prototype.showModal = function showModal(this: HTMLDialogElement): void {
    this.setAttribute('open', '');
};

HTMLDialogElement.prototype.close = function close(this: HTMLDialogElement): void {
    if (!this.hasAttribute('open')) {
        return;
    }

    this.removeAttribute('open');
    this.dispatchEvent(new Event('close'));
};
