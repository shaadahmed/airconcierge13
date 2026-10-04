/**
 * Dev-only: if the SPA root stays empty after boot (common after broken HMR),
 * show a clear recovery message instead of a silent white screen.
 */
export default defineNuxtPlugin(() => {
  if (!import.meta.dev || !import.meta.client)
    return

  const mark = () => {
    const root = document.querySelector('#__nuxt')
    if (!root || root.childElementCount > 0)
      return

    if (document.getElementById('ac-dev-blank-guard'))
      return

    const box = document.createElement('div')
    box.id = 'ac-dev-blank-guard'
    box.setAttribute('role', 'alert')
    box.style.cssText = [
      'font:14px/1.45 system-ui,sans-serif',
      'max-width:36rem',
      'margin:4rem auto',
      'padding:1.25rem 1.5rem',
      'border:1px solid #f0c36d',
      'border-radius:8px',
      'background:#fff8e6',
      'color:#5c4500',
    ].join(';')
    box.innerHTML = [
      '<strong>Dev UI failed to mount</strong>',
      '<p style="margin:.75rem 0">Usually a corrupted Nuxt HMR/.nuxt state. Hard-refresh, or run:</p>',
      '<code style="display:block;padding:.5rem;background:#fff;border-radius:4px">frontend/bin/nuxt-reset.sh</code>',
      '<p style="margin:.75rem 0 0"><button type="button" id="ac-dev-blank-reload" style="padding:.4rem .9rem;cursor:pointer">Reload page</button></p>',
    ].join('')
    document.body.appendChild(box)
    document.getElementById('ac-dev-blank-reload')?.addEventListener('click', () => location.reload())
  }

  window.setTimeout(mark, 2500)
  window.setTimeout(mark, 6000)
})
