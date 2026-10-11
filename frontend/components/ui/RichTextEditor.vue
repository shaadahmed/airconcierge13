<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Link } from '@tiptap/extension-link'
import { TextAlign } from '@tiptap/extension-text-align'
import { TextStyle } from '@tiptap/extension-text-style'
import { Color } from '@tiptap/extension-color'
import { Highlight } from '@tiptap/extension-highlight'
import { Placeholder } from '@tiptap/extension-placeholder'
import { Image } from '@tiptap/extension-image'
import { TableKit } from '@tiptap/extension-table'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'Start writing…' },
  disabled: Boolean,
  error: { type: [String, Array, Boolean], default: false },
  hint: { type: String, default: '' },
  minHeight: { type: String, default: '500px' },
})

const emit = defineEmits(['update:modelValue'])

const errorMessages = computed(() => {
  if (props.error === true || props.error === false || props.error == null)
    return []

  return Array.isArray(props.error) ? props.error : [props.error]
})

const hasError = computed(() => props.error === true || errorMessages.value.length > 0)

const headingItems = [
  { title: 'Paragraph', value: 'paragraph' },
  { title: 'Heading 1', value: '1' },
  { title: 'Heading 2', value: '2' },
  { title: 'Heading 3', value: '3' },
  { title: 'Heading 4', value: '4' },
  { title: 'Heading 5', value: '5' },
]

const colorItems = [
  { title: 'Default', value: '' },
  { title: 'Black', value: '#111827' },
  { title: 'Gray', value: '#6B7280' },
  { title: 'Red', value: '#DC2626' },
  { title: 'Orange', value: '#EA580C' },
  { title: 'Green', value: '#16A34A' },
  { title: 'Blue', value: '#2563EB' },
  { title: 'Purple', value: '#7C3AED' },
]

const editor = useEditor({
  content: props.modelValue || '',
  editable: !props.disabled,
  extensions: [
    StarterKit.configure({
      heading: { levels: [1, 2, 3, 4, 5] },
      // Use the standalone Link extension below for openOnClick / HTML attrs.
      link: false,
      // Underline stays enabled via StarterKit defaults.
    }),
    TextStyle,
    Color,
    Highlight.configure({ multicolor: true }),
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        rel: 'noopener noreferrer',
        target: '_blank',
      },
    }),
    Image.configure({
      allowBase64: true,
      HTMLAttributes: {
        class: 'rte-image',
      },
    }),
    TableKit.configure({
      table: { resizable: true },
    }),
    Placeholder.configure({
      placeholder: props.placeholder,
    }),
  ],
  onUpdate: ({ editor: current }) => {
    emit('update:modelValue', current.getHTML())
  },
})

watch(() => props.modelValue, value => {
  if (!editor.value)
    return

  const current = editor.value.getHTML()
  if (value === current)
    return

  editor.value.commands.setContent(value || '', { emitUpdate: false })
})

watch(() => props.disabled, value => {
  editor.value?.setEditable(!value)
})

onBeforeUnmount(() => {
  editor.value?.destroy()
})

const headingValue = computed({
  get() {
    if (!editor.value)
      return 'paragraph'

    for (const level of [1, 2, 3, 4, 5]) {
      if (editor.value.isActive('heading', { level }))
        return String(level)
    }

    return 'paragraph'
  },
  set(value) {
    if (!editor.value)
      return

    if (value === 'paragraph') {
      editor.value.chain().focus().setParagraph().run()

      return
    }

    editor.value.chain().focus().toggleHeading({ level: Number(value) }).run()
  },
})

const textColor = computed({
  get() {
    return editor.value?.getAttributes('textStyle').color || ''
  },
  set(value) {
    if (!editor.value)
      return

    if (!value) {
      editor.value.chain().focus().unsetColor().run()

      return
    }

    editor.value.chain().focus().setColor(value).run()
  },
})

const isActive = (nameOrAttrs, attrs) => {
  if (!editor.value)
    return false

  if (attrs === undefined)
    return editor.value.isActive(nameOrAttrs)

  return editor.value.isActive(nameOrAttrs, attrs)
}

const run = callback => {
  if (!editor.value || props.disabled)
    return

  callback(editor.value.chain().focus())
}

const setLink = () => {
  if (!editor.value)
    return

  const previous = editor.value.getAttributes('link').href || ''
  const url = window.prompt('Enter URL', previous)

  if (url === null)
    return

  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run()

    return
  }

  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

const addImage = () => {
  if (!editor.value)
    return

  const url = window.prompt('Enter image URL')

  if (!url)
    return

  editor.value.chain().focus().setImage({ src: url }).run()
}

const insertTable = () => {
  run(chain => chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run())
}
</script>

<template>
  <div
    class="rich-text-editor"
    :class="{ 'rich-text-editor--disabled': disabled, 'rich-text-editor--error': hasError }"
  >
    <div
      v-if="label"
      class="text-body-2 text-medium-emphasis mb-2"
    >
      {{ label }}
    </div>

    <div
      v-if="editor"
      class="rich-text-editor__shell"
    >
      <div class="rich-text-editor__toolbar">
        <VSelect
          v-model="headingValue"
          :items="headingItems"
          density="compact"
          variant="outlined"
          hide-details
          class="rich-text-editor__heading"
          :disabled="disabled"
        />

        <VDivider
          vertical
          class="mx-1"
        />

        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('bold') ? 'primary' : undefined"
          title="Bold"
          @click="run(c => c.toggleBold().run())"
        >
          <VIcon icon="bx-bold" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('italic') ? 'primary' : undefined"
          title="Italic"
          @click="run(c => c.toggleItalic().run())"
        >
          <VIcon icon="bx-italic" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('underline') ? 'primary' : undefined"
          title="Underline"
          @click="run(c => c.toggleUnderline().run())"
        >
          <VIcon icon="bx-underline" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('strike') ? 'primary' : undefined"
          title="Strikethrough"
          @click="run(c => c.toggleStrike().run())"
        >
          <VIcon icon="bx-strikethrough" />
        </VBtn>

        <VDivider
          vertical
          class="mx-1"
        />

        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('link') ? 'primary' : undefined"
          title="Link"
          @click="setLink"
        >
          <VIcon icon="bx-link" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('bulletList') ? 'primary' : undefined"
          title="Bulleted list"
          @click="run(c => c.toggleBulletList().run())"
        >
          <VIcon icon="bx-list-ul" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('orderedList') ? 'primary' : undefined"
          title="Numbered list"
          @click="run(c => c.toggleOrderedList().run())"
        >
          <VIcon icon="bx-list-ol" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('blockquote') ? 'primary' : undefined"
          title="Block quote"
          @click="run(c => c.toggleBlockquote().run())"
        >
          <VIcon icon="bxs-quote-left" />
        </VBtn>

        <VDivider
          vertical
          class="mx-1"
        />

        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive({ textAlign: 'left' }) ? 'primary' : undefined"
          title="Align left"
          @click="run(c => c.setTextAlign('left').run())"
        >
          <VIcon icon="bx-align-left" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive({ textAlign: 'center' }) ? 'primary' : undefined"
          title="Align center"
          @click="run(c => c.setTextAlign('center').run())"
        >
          <VIcon icon="bx-align-middle" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive({ textAlign: 'right' }) ? 'primary' : undefined"
          title="Align right"
          @click="run(c => c.setTextAlign('right').run())"
        >
          <VIcon icon="bx-align-right" />
        </VBtn>

        <VDivider
          vertical
          class="mx-1"
        />

        <VSelect
          v-model="textColor"
          :items="colorItems"
          density="compact"
          variant="outlined"
          hide-details
          class="rich-text-editor__color"
          :disabled="disabled"
          title="Text color"
        />

        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('highlight') ? 'primary' : undefined"
          title="Highlight"
          @click="run(c => c.toggleHighlight().run())"
        >
          <VIcon icon="bx-highlight" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          title="Insert image"
          @click="addImage"
        >
          <VIcon icon="bx-image" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          :color="isActive('table') ? 'primary' : undefined"
          title="Insert table"
          @click="insertTable"
        >
          <VIcon icon="bx-table" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled"
          title="Horizontal rule"
          @click="run(c => c.setHorizontalRule().run())"
        >
          <VIcon icon="bx-minus" />
        </VBtn>

        <VDivider
          vertical
          class="mx-1"
        />

        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled || !editor?.can().undo()"
          title="Undo"
          @click="run(c => c.undo().run())"
        >
          <VIcon icon="bx-undo" />
        </VBtn>
        <VBtn
          icon
          size="small"
          variant="text"
          :disabled="disabled || !editor?.can().redo()"
          title="Redo"
          @click="run(c => c.redo().run())"
        >
          <VIcon icon="bx-redo" />
        </VBtn>
      </div>

      <EditorContent
        :editor="editor"
        class="rich-text-editor__content"
        :style="{ '--rte-min-height': minHeight }"
      />
    </div>

    <div
      v-if="errorMessages.length"
      class="text-error text-caption mt-1"
    >
      {{ errorMessages[0] }}
    </div>
    <div
      v-else-if="hint"
      class="text-caption text-medium-emphasis mt-1"
    >
      {{ hint }}
    </div>
  </div>
</template>

<style scoped>
.rich-text-editor__shell {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
}

.rich-text-editor--error .rich-text-editor__shell {
  border-color: rgb(var(--v-theme-error));
}

.rich-text-editor--disabled {
  opacity: 0.7;
  pointer-events: none;
}

.rich-text-editor__toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 2px;
  padding: 8px 10px;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  background: rgba(var(--v-theme-on-surface), 0.02);
}

.rich-text-editor__heading {
  max-width: 150px;
  flex: 0 0 150px;
}

.rich-text-editor__color {
  max-width: 120px;
  flex: 0 0 120px;
}

.rich-text-editor__content {
  min-height: var(--rte-min-height, 500px);
}

.rich-text-editor__content :deep(.tiptap),
.rich-text-editor__content :deep(.ProseMirror) {
  min-height: var(--rte-min-height, 500px);
  padding: 1rem 1.15rem;
  outline: none;
  font-size: 0.95rem;
  line-height: 1.65;
}

.rich-text-editor__content :deep(.tiptap p.is-editor-empty:first-child::before),
.rich-text-editor__content :deep(.ProseMirror p.is-editor-empty:first-child::before) {
  color: rgba(var(--v-theme-on-surface), 0.4);
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.rich-text-editor__content :deep(h1),
.rich-text-editor__content :deep(h2),
.rich-text-editor__content :deep(h3),
.rich-text-editor__content :deep(h4),
.rich-text-editor__content :deep(h5) {
  margin-block: 0.85em 0.4em;
  line-height: 1.25;
}

.rich-text-editor__content :deep(p),
.rich-text-editor__content :deep(ul),
.rich-text-editor__content :deep(ol),
.rich-text-editor__content :deep(blockquote) {
  margin-block: 0.55em;
}

.rich-text-editor__content :deep(ul),
.rich-text-editor__content :deep(ol) {
  padding-inline-start: 1.35rem;
}

.rich-text-editor__content :deep(blockquote) {
  border-inline-start: 3px solid rgba(var(--v-theme-on-surface), 0.2);
  padding-inline-start: 0.85rem;
  color: rgba(var(--v-theme-on-surface), 0.75);
}

.rich-text-editor__content :deep(a) {
  color: rgb(var(--v-theme-primary));
  text-decoration: underline;
}

.rich-text-editor__content :deep(hr) {
  border: none;
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  margin-block: 1.25rem;
}

.rich-text-editor__content :deep(img.rte-image),
.rich-text-editor__content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 6px;
}

.rich-text-editor__content :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin-block: 1rem;
  overflow: hidden;
}

.rich-text-editor__content :deep(td),
.rich-text-editor__content :deep(th) {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding: 0.45rem 0.6rem;
  min-width: 80px;
  vertical-align: top;
}

.rich-text-editor__content :deep(th) {
  background: rgba(var(--v-theme-on-surface), 0.04);
  font-weight: 600;
}
</style>
