import $ from 'jquery'

// Expose jQuery ke global
window.$ = $
window.jQuery = $

async function loadLegacy() {
  await import('bootstrap')
  await import('admin-lte')
}

loadLegacy()
