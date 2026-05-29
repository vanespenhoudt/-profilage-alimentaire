import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';

document.querySelectorAll('[data-tiptap]').forEach((wrapper) => {
    const templateEl = wrapper.querySelector('[data-tiptap-initial]');
    const editorEl   = wrapper.querySelector('[data-tiptap-content]');
    const hiddenEl   = wrapper.querySelector('[data-tiptap-hidden]');
    const toolbar    = wrapper.querySelector('[data-tiptap-toolbar]');
    const initial    = templateEl ? templateEl.innerHTML.trim() : '';

    const editor = new Editor({
        element: editorEl,
        extensions: [
            StarterKit,
            Placeholder.configure({
                placeholder: 'Ex : Lundi – Petit-déjeuner : flocons d\'avoine, fruits rouges…',
            }),
        ],
        content: initial,
        onUpdate({ editor }) {
            if (hiddenEl) hiddenEl.value = editor.getHTML();
        },
    });

    // Sync hidden textarea on form submit
    const form = wrapper.closest('form');
    if (form && hiddenEl) {
        form.addEventListener('submit', () => {
            hiddenEl.value = editor.getHTML();
        });
    }

    if (!toolbar) return;

    const updateActive = () => {
        toolbar.querySelector('[data-action="bold"]')?.classList.toggle('tiptap-btn-active', editor.isActive('bold'));
        toolbar.querySelector('[data-action="italic"]')?.classList.toggle('tiptap-btn-active', editor.isActive('italic'));
        toolbar.querySelector('[data-action="h2"]')?.classList.toggle('tiptap-btn-active', editor.isActive('heading', { level: 2 }));
        toolbar.querySelector('[data-action="h3"]')?.classList.toggle('tiptap-btn-active', editor.isActive('heading', { level: 3 }));
        toolbar.querySelector('[data-action="bulletList"]')?.classList.toggle('tiptap-btn-active', editor.isActive('bulletList'));
        toolbar.querySelector('[data-action="orderedList"]')?.classList.toggle('tiptap-btn-active', editor.isActive('orderedList'));
        toolbar.querySelector('[data-action="blockquote"]')?.classList.toggle('tiptap-btn-active', editor.isActive('blockquote'));
    };

    editor.on('selectionUpdate', updateActive);
    editor.on('transaction', updateActive);

    toolbar.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        e.preventDefault();
        const chain = editor.chain().focus();
        switch (btn.dataset.action) {
            case 'bold':         chain.toggleBold().run(); break;
            case 'italic':       chain.toggleItalic().run(); break;
            case 'h2':           chain.toggleHeading({ level: 2 }).run(); break;
            case 'h3':           chain.toggleHeading({ level: 3 }).run(); break;
            case 'bulletList':   chain.toggleBulletList().run(); break;
            case 'orderedList':  chain.toggleOrderedList().run(); break;
            case 'blockquote':   chain.toggleBlockquote().run(); break;
            case 'clear':        chain.clearNodes().unsetAllMarks().run(); break;
        }
    });
});
