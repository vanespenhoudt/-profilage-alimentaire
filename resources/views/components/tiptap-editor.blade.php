{{-- Usage: <x-tiptap-editor name="menu_text" :value="$questionnaire?->menu_text" /> --}}
@props(['name' => 'menu_text', 'value' => ''])

<div data-tiptap>
    <template data-tiptap-initial>{!! $value !!}</template>
    <div class="tiptap-wrap">
        <div class="tiptap-toolbar" data-tiptap-toolbar>
            <button type="button" class="tiptap-btn" data-action="bold"><b>B</b></button>
            <button type="button" class="tiptap-btn" data-action="italic"><i>I</i></button>
            <span class="tiptap-sep"></span>
            <button type="button" class="tiptap-btn" data-action="h2">H2</button>
            <button type="button" class="tiptap-btn" data-action="h3">H3</button>
            <span class="tiptap-sep"></span>
            <button type="button" class="tiptap-btn" data-action="bulletList">&#8226; Liste</button>
            <button type="button" class="tiptap-btn" data-action="orderedList">1. Liste</button>
            <span class="tiptap-sep"></span>
            <button type="button" class="tiptap-btn" data-action="blockquote">❝</button>
            <button type="button" class="tiptap-btn" data-action="clear" title="Effacer le formatage">✕</button>
        </div>
        <div data-tiptap-content></div>
    </div>
    <textarea name="{{ $name }}" data-tiptap-hidden class="d-none">{{ $value }}</textarea>
</div>
