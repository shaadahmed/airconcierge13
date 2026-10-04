# Frontend sync plan (Windows vs WSL / Docker Nuxt)

Day-to-day work uses the **WSL-native** tree. Docker Nuxt bind-mounts that path. The copy on `E:` is a **periodic local backup** (git fetch/pull), not the active edit surface.

## Two project trees

| Role | Path |
|------|------|
| Active workspace (Cursor + Docker) | `/home/pc/airconcierge13` |
| Open in Cursor (Windows UNC) | `\\wsl$\Ubuntu\home\pc\airconcierge13` (same as `\\wsl.localhost\Ubuntu\…`) |
| Windows backup checkout | `E:\airconcierge13` (also visible in WSL as `/mnt/e/airconcierge13`) |

Nuxt container mount:

```text
/home/pc/airconcierge13/frontend  →  /app  (in airconcierge-nuxt)
```

Docker **does** hot-reload when files change under the **mounted** path. It does **not** sync `E:\` ↔ `/home/pc/` for you.

## Symptom if trees drift

- Cursor/file on disk shows the new UI.
- Browser at `http://localhost:3000` still shows an older page (or vice versa).
- Hard-refresh of `/admin/*` may return Laravel JSON (`{"data":[]}`) because Nuxt proxies `/admin` API calls; prefer SPA navigation (e.g. sidebar **Homes**) instead of hard-refreshing admin URLs.

## Options (reference)

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
\\wsl$\Ubuntu\home\pc\airconcierge13
```

(or `\\wsl.localhost\Ubuntu\home\pc\airconcierge13` — same filesystem)

- **Pros:** You edit the same tree Docker already mounts; no sync step; native WSL I/O.
- **Cons:** Workflow lives in WSL rather than `E:\`; WSL distro wipe can remove uncommitted work under `/home/pc` unless it is pushed or backed up.

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

**Chosen: option 2** (develop against the WSL copy).

- **Active source of truth:** `/home/pc/airconcierge13` — open in Cursor via `\\wsl$\Ubuntu\home\pc\airconcierge13` (or `cursor .` from that directory in WSL).
- **Docker Nuxt** mounts `/home/pc/airconcierge13/frontend` → `/app`. Recreate/remount the container to that path if it still points at `/mnt/e/...`.
- **Env:** `NUXT_LARAVEL_URL=http://laravel.test`, network `airconcierge13_sail`, port `3000`.
- **Windows `E:\airconcierge13`:** keep as a local backup only. From time to time, in that checkout, `git fetch` / `git pull` so `E:` stays roughly in sync with the remote (and thus with work that was pushed from WSL). Do **not** edit day-to-day on `E:` while using this workflow, or the trees will drift again.
- **Durability:** push regularly from the WSL tree. A WSL distro unregister/wipe removes `/home/pc/...`; regenerable deps (`node_modules`) are fine to reinstall; unpushed commits/work are not.

Cursor edits under the WSL path should hot-reload at http://localhost:3000 without rsync.
