{{-- Usage: <x-tiptap-editor name="menu_text" :value="$questionnaire?->menu_text ?? ''" /> --}}
@props(['name' => 'menu_text', 'value' => '', 'readonly' => false])
@php $uid = 'tiptap-' . uniqid(); @endphp

@if($readonly)
    {{-- Lecture seule : affichage HTML brut --}}
    <div class="tiptap-readonly">{!! $value ?: '<p class="text-muted-pa fst-italic">Aucun menu enregistré.</p>' !!}</div>
@else
<style>
#{{ $uid }} .tiptap-content {
    min-height: 180px; padding: 12px 14px; outline: none; cursor: text;
    display: block; width: 100%; font-size: 14px; line-height: 1.6;
    font-family: 'Outfit', sans-serif; color: var(--color-navy, #1a2233);
    border-top: 0;
}
#{{ $uid }} .tiptap-content:empty:before {
    content: attr(data-placeholder); color: #adb5bd; pointer-events: none;
}
</style>

<div class="tiptap-wrap" data-editor-root id="{{ $uid }}">
    <div class="tiptap-toolbar" data-tiptap-toolbar>
        <button type="button" class="tiptap-btn" data-cmd="bold"><b>B</b></button>
        <button type="button" class="tiptap-btn" data-cmd="italic"><i>I</i></button>
        <span class="tiptap-sep"></span>
        <button type="button" class="tiptap-btn" data-cmd="formatBlock" data-val="h2">H2</button>
        <button type="button" class="tiptap-btn" data-cmd="formatBlock" data-val="h3">H3</button>
        <span class="tiptap-sep"></span>
        <button type="button" class="tiptap-btn" data-cmd="insertUnorderedList">&#8226;&nbsp;Liste</button>
        <button type="button" class="tiptap-btn" data-cmd="insertOrderedList">1.&nbsp;Liste</button>
        <span class="tiptap-sep"></span>
        <button type="button" class="tiptap-btn" data-cmd="formatBlock" data-val="blockquote">❝</button>
        <button type="button" class="tiptap-btn" data-cmd="removeFormat" title="Effacer le formatage">✕</button>
    </div>
    <div class="tiptap-content"
         contenteditable="true"
         data-tiptap-content
         data-placeholder="Ex : Lundi – Petit-déjeuner : flocons d'avoine, fruits rouges…">{!! $value !!}</div>
    <textarea name="{{ $name }}" class="d-none">{{ $value }}</textarea>
</div>

<script>
(function () {
    var root    = document.getElementById('{{ $uid }}');
    var toolbar = root.querySelector('[data-tiptap-toolbar]');
    var content = root.querySelector('[data-tiptap-content]');
    var hidden  = root.querySelector('textarea');

    hidden.value = content.innerHTML;

    content.addEventListener('input', function () {
        hidden.value = content.innerHTML;
    });

    toolbar.addEventListener('mousedown', function (e) {
        var btn = e.target.closest('[data-cmd]');
        if (!btn) return;
        e.preventDefault();
        document.execCommand(btn.dataset.cmd, false, btn.dataset.val || null);
        content.focus();
        hidden.value = content.innerHTML;
        updateActive();
    });

    function updateActive() {
        toolbar.querySelectorAll('[data-cmd]').forEach(function (btn) {
            var cmd = btn.dataset.cmd;
            var active = false;
            try { active = document.queryCommandState(cmd); } catch (_) {}
            btn.classList.toggle('tiptap-btn-active', active);
        });
    }

    content.addEventListener('keyup', updateActive);
    content.addEventListener('mouseup', updateActive);

    var form = root.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            hidden.value = content.innerHTML;
        });
    }
})();
</script>
@endif
