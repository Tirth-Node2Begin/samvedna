/**
 * Window-event bridge for the mobile drawer.
 *
 * The drawer trigger lives in AppHeader, not the rail — as a `fixed` button at
 * the same 18px inset it landed straight on top of the breadcrumb. A window
 * event keeps the two components decoupled without threading state through the
 * layout.
 */

const TOGGLE = "samvedna:sidebar-toggle";

export function toggleSidebar() {
  window.dispatchEvent(new CustomEvent(TOGGLE));
}

/** Subscribe to toggle requests. Returns an unsubscribe function. */
export function onSidebarToggle(handler: () => void) {
  window.addEventListener(TOGGLE, handler);
  return () => window.removeEventListener(TOGGLE, handler);
}
