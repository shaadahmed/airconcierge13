<script setup>
/**
 * CSS 3D “disk” pie — floating coin / puck in perspective (not a chart library).
 *
 * Important: never put `filter` on the 3D stage — it flattens preserve-3d and
 * the extrusion collapses into a tilted “page”.
 *
 * @typedef {{ key?: string, label: string, value: number, color: string }} DiskPieItem
 */

/** Side-wall depth in local Z (px). Visible thickness under rotateX. */
const EDGE_STEPS = 10
const EDGE_STEP_PX = 1.4

const props = defineProps({
  /** @type {{ type: import('vue').PropType<DiskPieItem[]> }} */
  items: {
    type: Array,
    default: () => [],
  },
  ariaLabel: {
    type: String,
    default: 'Disk pie chart',
  },
  emptyTitle: {
    type: String,
    default: 'No data',
  },
  emptyDescription: {
    type: String,
    default: 'Counts will appear once data is available.',
  },
})

const hoveredKey = ref(null)
const tooltip = reactive({
  show: false,
  x: 0,
  y: 0,
  label: '',
  value: 0,
  percent: 0,
})

const total = computed(() =>
  props.items.reduce((sum, item) => sum + Math.max(0, Number(item.value) || 0), 0),
)

const segments = computed(() => {
  const sum = total.value
  if (sum <= 0)
    return []

  let cursor = 0

  return props.items
    .map((item, index) => {
      const value = Math.max(0, Number(item.value) || 0)
      const startDeg = (cursor / sum) * 360
      cursor += value
      const endDeg = (cursor / sum) * 360
      const percent = (value / sum) * 100

      return {
        key: item.key ?? `seg-${index}`,
        label: item.label,
        color: item.color,
        value,
        percent,
        startDeg,
        endDeg,
      }
    })
    .filter(segment => segment.value > 0)
})

const conicBackground = computed(() => {
  if (!segments.value.length)
    return 'conic-gradient(#DFE2E3 0deg 360deg)'

  const stops = segments.value.map(
    segment => `${segment.color} ${segment.startDeg}deg ${segment.endDeg}deg`,
  )

  return `conic-gradient(from -90deg, ${stops.join(', ')})`
})

/** Darker conic for the vertical side wall (same seams, deeper shades). */
const edgeBackground = computed(() => {
  if (!segments.value.length)
    return 'conic-gradient(#9aa0a6 0deg 360deg)'

  const stops = segments.value.map(segment => {
    const edge = shadeHex(segment.color, 0.48)

    return `${edge} ${segment.startDeg}deg ${segment.endDeg}deg`
  })

  return `conic-gradient(from -90deg, ${stops.join(', ')})`
})

const chartSummary = computed(() =>
  segments.value
    .map(segment => `${segment.label}: ${segment.value} (${Math.round(segment.percent)}%)`)
    .join(', '),
)

const edgeSteps = Array.from({ length: EDGE_STEPS }, (_, index) => index + 1)

/**
 * Mix hex color toward black by amount (0–1).
 * @param {string} hex
 * @param {number} amount
 */
function shadeHex(hex, amount) {
  const raw = String(hex || '').replace('#', '')
  if (raw.length !== 6)
    return hex

  const mix = channel => {
    const value = Number.parseInt(raw.slice(channel, channel + 2), 16)

    return Math.round(value * (1 - amount))
  }

  const r = mix(0).toString(16).padStart(2, '0')
  const g = mix(2).toString(16).padStart(2, '0')
  const b = mix(4).toString(16).padStart(2, '0')

  return `#${r}${g}${b}`
}

const showTooltip = (segment, event) => {
  hoveredKey.value = segment.key
  tooltip.show = true
  tooltip.label = segment.label
  tooltip.value = segment.value
  tooltip.percent = segment.percent
  tooltip.x = event?.clientX ?? 0
  tooltip.y = event?.clientY ?? 0
}

const moveTooltip = event => {
  if (!tooltip.show)
    return

  tooltip.x = event.clientX
  tooltip.y = event.clientY
}

const hideTooltip = () => {
  hoveredKey.value = null
  tooltip.show = false
}

const segmentAtPoint = (faceEl, clientX, clientY) => {
  const rect = faceEl.getBoundingClientRect()
  const cx = rect.left + rect.width / 2
  const cy = rect.top + rect.height / 2
  const dx = clientX - cx
  const dy = clientY - cy
  const radius = Math.min(rect.width, rect.height) / 2
  if (Math.hypot(dx, dy) > radius)
    return null

  let deg = (Math.atan2(dx, -dy) * 180) / Math.PI
  if (deg < 0)
    deg += 360

  return segments.value.find(
    segment => deg >= segment.startDeg && deg < segment.endDeg,
  ) || segments.value[segments.value.length - 1] || null
}

const onDiskPointerMove = event => {
  const segment = segmentAtPoint(event.currentTarget, event.clientX, event.clientY)
  if (!segment) {
    hideTooltip()

    return
  }

  showTooltip(segment, event)
  moveTooltip(event)
}

const onDiskPointerLeave = () => {
  hideTooltip()
}
</script>

<template>
  <div class="disk-pie">
    <EmptyState
      v-if="total <= 0"
      :title="emptyTitle"
      :description="emptyDescription"
    />

    <template v-else>
      <div class="disk-pie__layout">
        <div
          class="disk-pie__viewport"
          :aria-label="`${ariaLabel}. ${chartSummary}`"
          role="img"
        >
          <!-- Shadow is a sibling — never filter the 3D stage or extrusion collapses. -->
          <div
            class="disk-pie__shadow"
            aria-hidden="true"
          />

          <div class="disk-pie__stage">
            <div
              class="disk-pie__extrusion"
              aria-hidden="true"
            >
              <span
                v-for="step in edgeSteps"
                :key="`edge-${step}`"
                class="disk-pie__edge"
                :class="{ 'disk-pie__edge--base': step === EDGE_STEPS }"
                :style="{
                  background: edgeBackground,
                  transform: `translateZ(${-step * EDGE_STEP_PX}px)`,
                }"
              />
            </div>

            <div
              class="disk-pie__face"
              :style="{ background: conicBackground }"
              @pointermove="onDiskPointerMove"
              @pointerleave="onDiskPointerLeave"
            >
              <span
                class="disk-pie__gloss"
                aria-hidden="true"
              />
            </div>
          </div>
        </div>

        <ul
          class="disk-pie__legend"
          role="list"
        >
          <li
            v-for="segment in segments"
            :key="segment.key"
          >
            <button
              type="button"
              class="disk-pie__legend-item"
              :class="{ 'disk-pie__legend-item--active': hoveredKey === segment.key }"
              @pointerenter="showTooltip(segment, $event)"
              @pointerleave="hideTooltip"
              @focus="showTooltip(segment, $event)"
              @blur="hideTooltip"
            >
              <span
                class="disk-pie__swatch"
                :style="{ backgroundColor: segment.color }"
                aria-hidden="true"
              />
              <span class="disk-pie__legend-copy min-w-0">
                <span class="disk-pie__legend-label text-truncate">
                  {{ segment.label }}
                </span>
                <span class="disk-pie__legend-meta">
                  <strong>{{ segment.value }}</strong>
                  <span class="text-medium-emphasis">
                    · {{ Math.round(segment.percent) }}%
                  </span>
                </span>
              </span>
            </button>
          </li>
        </ul>
      </div>

      <Teleport to="body">
        <div
          v-if="tooltip.show"
          class="disk-pie__tooltip"
          :style="{
            left: `${tooltip.x + 14}px`,
            top: `${tooltip.y + 14}px`,
          }"
          role="tooltip"
        >
          <div class="disk-pie__tooltip-label">
            {{ tooltip.label }}
          </div>
          <div class="disk-pie__tooltip-value">
            {{ tooltip.value }}
            <span class="text-medium-emphasis">
              ({{ Math.round(tooltip.percent) }}%)
            </span>
          </div>
        </div>
      </Teleport>
    </template>
  </div>
</template>

<style scoped>
.disk-pie__layout {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.disk-pie__viewport {
  position: relative;
  flex: 0 0 52%;
  min-width: 0;
  perspective: 920px;
  perspective-origin: 50% 42%;
  padding-block: 1.35rem 2.35rem;
  transform-style: preserve-3d;
}

.disk-pie__shadow {
  position: absolute;
  left: 50%;
  bottom: 0.55rem;
  width: min(78%, 230px);
  height: 1.35rem;
  border-radius: 50%;
  background: radial-gradient(
    ellipse at center,
    rgba(20, 24, 32, 0.34) 0%,
    rgba(20, 24, 32, 0.14) 42%,
    transparent 72%
  );
  transform: translateX(-42%) translateX(10px);
  pointer-events: none;
  z-index: 0;
}

.disk-pie__stage {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 220px;
  margin-inline: auto;
  transform: rotateX(58deg);
  transform-style: preserve-3d;
}

.disk-pie__legend {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  gap: 0.5rem;
  margin: 0;
  padding: 0;
  list-style: none;
  min-width: 0;
}

.disk-pie__legend-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  width: 100%;
  padding: 0.45rem 0.55rem;
  border: 1px solid rgba(75, 70, 92, 0.08);
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.72);
  text-align: start;
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.disk-pie__legend-item:hover,
.disk-pie__legend-item:focus-visible,
.disk-pie__legend-item--active {
  border-color: rgba(104, 101, 255, 0.35);
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 2px 8px rgba(34, 48, 62, 0.08);
  outline: none;
}

.disk-pie__swatch {
  flex-shrink: 0;
  width: 0.7rem;
  height: 0.7rem;
  border-radius: 999px;
  box-shadow: 0 2px 4px rgba(34, 48, 62, 0.16);
}

.disk-pie__legend-copy {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  gap: 0.1rem;
  min-width: 0;
}

.disk-pie__legend-label {
  font-size: 0.75rem;
  line-height: 1.25;
  color: rgba(75, 70, 92, 0.78);
}

.disk-pie__legend-meta {
  font-size: 0.8125rem;
  line-height: 1.25;
  color: rgba(34, 48, 62, 0.95);
  font-variant-numeric: tabular-nums;
}

.disk-pie__legend-meta strong {
  font-weight: 700;
}

.disk-pie__extrusion {
  position: absolute;
  inset: 0;
  transform-style: preserve-3d;
  pointer-events: none;
}

.disk-pie__edge {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  backface-visibility: hidden;
}

.disk-pie__edge--base {
  box-shadow: 0 0 0 1px rgba(20, 24, 32, 0.12);
}

.disk-pie__face {
  position: relative;
  z-index: 1;
  aspect-ratio: 1;
  width: 100%;
  border-radius: 50%;
  border: 0;
  cursor: default;
  transform-style: preserve-3d;
  backface-visibility: hidden;
}

.disk-pie__gloss {
  position: absolute;
  inset: 0;
  border-radius: inherit;
  pointer-events: none;
  background:
    radial-gradient(
      130% 100% at 28% 18%,
      rgba(255, 255, 255, 0.22) 0%,
      rgba(255, 255, 255, 0.06) 24%,
      transparent 54%
    ),
    linear-gradient(
      180deg,
      transparent 38%,
      rgba(20, 24, 32, 0.16) 100%
    );
}

.disk-pie__tooltip {
  position: fixed;
  z-index: 2400;
  min-width: 7.5rem;
  padding: 0.5rem 0.65rem;
  border-radius: 8px;
  background: rgba(34, 48, 62, 0.94);
  color: #fff;
  pointer-events: none;
  box-shadow: 0 8px 20px rgba(34, 48, 62, 0.28);
}

.disk-pie__tooltip-label {
  font-size: 0.75rem;
  opacity: 0.85;
}

.disk-pie__tooltip-value {
  margin-top: 0.15rem;
  font-size: 0.875rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

@media (prefers-reduced-motion: reduce) {
  .disk-pie__stage {
    transform: none;
  }

  .disk-pie__extrusion {
    display: none;
  }

  .disk-pie__legend-item {
    transition: none;
  }
}
</style>
