{{-- Usage: <x-tiptap-editor name="menu_text" :value="$questionnaire?->menu_text ?? ''" /> --}}
@props(['name' => 'menu_text', 'value' => ''])
@php $uid = 'tiptap-' . uniqid(); @endphp

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
