# Frontend sync plan (Windows vs WSL / Docker Nuxt)

Local Nuxt does **not** read the Cursor workspace on `E:` directly. The `airconcierge-nuxt` container bind-mounts a **separate** WSL tree. Edits in one place do not appear in the other unless you sync or remount.

## Two project trees

| Role | Path |
|------|------|
| Windows (Cursor) | `E:\airconcierge13` |
| Same files via WSL | `/mnt/e/airconcierge13` |
| WSL copy (Docker Nuxt mount) | `/home/pc/airconcierge13` |

Nuxt container mount (as of this note):

```text
/home/pc/airconcierge13/frontend  →  /app  (in airconcierge-nuxt)
```

Docker **does** hot-reload when files change under the **mounted** path. It does **not** sync `E:\` ↔ `/home/pc/` for you.

## Symptom if trees drift

- Cursor/file on disk shows the new UI.
- Browser at `http://localhost:3000` still shows an older page (or vice versa).
- Hard-refresh of `/admin/*` may return Laravel JSON (`{"data":[]}`) because Nuxt proxies `/admin` API calls; prefer SPA navigation (e.g. sidebar **Homes**) instead of hard-refreshing admin URLs.

## Options (pick one later)

### 1. Point Docker at the Windows tree

Remount Nuxt to:

```text
/mnt/e/airconcierge13/frontend
```

- **Pros:** One source of truth; Cursor edits on `E:` are what Docker/HMR sees.
- **Cons:** I/O over `/mnt/e` can be slower than a native WSL disk.

Also keep Docker Nuxt’s Laravel proxy URL correct for in-container networking, e.g. `NUXT_LARAVEL_URL=http://laravel.test` (not `http://localhost:8080` from inside the container).

### 2. Develop against the WSL copy

Open the project in Cursor via the WSL path, e.g.:

```text
\\wsl$\<distro>\home\pc\airconcierge13
```

(Distro name may differ; check in WSL.)

- **Pros:** You edit the same tree Docker already mounts; no sync step.
- **Cons:** Workflow lives in WSL rather than `E:\`.

### 3. Keep both copies and sync manually

After changing files on Windows, from WSL:

```bash
rsync -av --delete /mnt/e/airconcierge13/frontend/ /home/pc/airconcierge13/frontend/ \
  --exclude node_modules --exclude .nuxt --exclude .output
```

Or copy only the files you changed. Docker will then reload from `/home/pc/…`.

- **Pros:** No Docker remount; flexible.
- **Cons:** Easy to forget; trees can drift again.

## Decision

_Choose option 1, 2, or 3 when ready, then update this doc with what was implemented._
