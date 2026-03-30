/**
 * AtomicMe — DOM Utility Functions
 *
 * Thin wrappers around common DOM patterns used throughout welcome.blade.php.
 * Reduces boilerplate and provides a consistent, readable API.
 *
 * All functions are exported individually so they can be tree-shaken or
 * imported selectively once the project migrates to ES modules.
 */

/**
 * Get a single element by its `id` attribute.
 * Returns `null` (not a thrown error) if the element is absent so callers can
 * use optional chaining without try/catch.
 *
 * @param {string} id  The element's `id` value (without the leading `#`).
 * @returns {HTMLElement|null}
 */
export function getElement(id) {
    return document.getElementById(id);
}

/**
 * Query for all elements matching a CSS selector within an optional root.
 *
 * @param {string}              selector  CSS selector string.
 * @param {Element|Document}   [root]    Defaults to `document`.
 * @returns {NodeListOf<Element>}
 */
export function getAllElements(selector, root = document) {
    return root.querySelectorAll(selector);
}

/**
 * Query for the first element matching a CSS selector within an optional root.
 *
 * @param {string}            selector  CSS selector string.
 * @param {Element|Document} [root]    Defaults to `document`.
 * @returns {Element|null}
 */
export function queryElement(selector, root = document) {
    return root.querySelector(selector);
}

/**
 * Attach an event listener to an element with automatic null-guard.
 * No-ops silently if `el` is null so callers do not need to guard.
 *
 * @param {EventTarget|null} el       The target element.
 * @param {string}           event    Event type, e.g. `'click'`.
 * @param {Function}         handler  Callback function.
 * @returns {void}
 */
export function on(el, event, handler) {
    if (!el) { return; }
    el.addEventListener(event, handler);
}

/**
 * Set an element's `textContent` safely. No-ops if `el` is null.
 *
 * @param {HTMLElement|null} el     Target element.
 * @param {string}           text   Text to assign.
 * @returns {void}
 */
export function setText(el, text) {
    if (!el) { return; }
    el.textContent = text;
}

/**
 * Set an element's `innerHTML` safely. No-ops if `el` is null.
 * Only use for trusted, server-sourced or app-generated HTML — never for
 * raw user input.
 *
 * @param {HTMLElement|null} el    Target element.
 * @param {string}           html  HTML string to assign.
 * @returns {void}
 */
export function setHtml(el, html) {
    if (!el) { return; }
    el.innerHTML = html;
}

/**
 * Add or remove a CSS class on an element based on a boolean condition.
 * Equivalent to calling `classList.add` / `classList.remove` conditionally.
 *
 * @param {HTMLElement|null} el        Target element.
 * @param {string}           className CSS class name.
 * @param {boolean}          value     `true` → add, `false` → remove.
 * @returns {void}
 */
export function setClass(el, className, value) {
    if (!el) { return; }
    el.classList.toggle(className, value);
}

/**
 * Add one or more CSS classes to an element.
 *
 * @param {HTMLElement|null} el          Target element.
 * @param {...string}        classNames  One or more class names.
 * @returns {void}
 */
export function addClass(el, ...classNames) {
    if (!el) { return; }
    el.classList.add(...classNames);
}

/**
 * Remove one or more CSS classes from an element.
 *
 * @param {HTMLElement|null} el          Target element.
 * @param {...string}        classNames  One or more class names.
 * @returns {void}
 */
export function removeClass(el, ...classNames) {
    if (!el) { return; }
    el.classList.remove(...classNames);
}

/**
 * Set an inline CSS `display` property on an element.
 *
 * @param {HTMLElement|null} el     Target element.
 * @param {string}           value  CSS display value, e.g. `'block'`, `'none'`, `'flex'`.
 * @returns {void}
 */
export function setDisplay(el, value) {
    if (!el) { return; }
    el.style.display = value;
}

/**
 * Show an element by setting `display: block`.
 *
 * @param {HTMLElement|null} el  Target element.
 * @returns {void}
 */
export function show(el) {
    setDisplay(el, 'block');
}

/**
 * Hide an element by setting `display: none`.
 *
 * @param {HTMLElement|null} el  Target element.
 * @returns {void}
 */
export function hide(el) {
    setDisplay(el, 'none');
}

/**
 * Remove the `selected` class from every element in a NodeList / Array,
 * then add it to `activeEl`. This is the single-select pattern used throughout
 * the app for emoji grids, colour pickers, time selectors, etc.
 *
 * @param {NodeListOf<Element>|Array<Element>} group     All sibling elements.
 * @param {Element}                            activeEl  The element to mark active.
 * @returns {void}
 */
export function selectInGroup(group, activeEl) {
    group.forEach(el => el.classList.remove('selected'));
    if (activeEl) { activeEl.classList.add('selected'); }
}

/**
 * Create a DOM element with optional class names and inner HTML.
 *
 * @param {string}   tag        HTML tag name, e.g. `'div'`.
 * @param {string}  [classes]   Space-separated class names.
 * @param {string}  [innerHTML] Optional inner HTML.
 * @returns {HTMLElement}
 */
export function createElement(tag, classes = '', innerHTML = '') {
    const el = document.createElement(tag);
    if (classes) { el.className = classes; }
    if (innerHTML) { el.innerHTML = innerHTML; }
    return el;
}

/**
 * Scroll the window (or a given container) to the top.
 *
 * @param {Element|Window} [target]  Defaults to `window`.
 * @returns {void}
 */
export function scrollToTop(target = window) {
    target.scrollTo(0, 0);
}
