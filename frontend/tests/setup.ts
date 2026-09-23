/**
 * jsdom has no layout, so it ships no scrollIntoView. The Event nav scrolls
 * its lit tab into view on mount, which means every spec that draws the shell
 * would otherwise end in an unhandled rejection. Specs that care whether it
 * was called replace this with a spy of their own.
 */
Element.prototype.scrollIntoView = function scrollIntoView(): void {};
