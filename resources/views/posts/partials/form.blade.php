@php
    $selectedTags = old(
        'tag_ids',
        isset($post) ? $post->tags->pluck('id')->all() : []
    );
@endphp

<label for="title">Başlık</label>
<input
    id="title"
    name="title"
    type="text"
    value="{{ old('title', $post->title ?? '') }}"
    maxlength="255"
    required
>
@error('title')
    <div class="field-error">{{ $message }}</div>
@enderror

<label for="category_id">Kategori</label>
<select id="category_id" name="category_id">
    <option value="">Kategori seçiniz</option>

    @foreach ($categories as $category)
        <option
            value="{{ $category->id }}"
            @selected((string) old('category_id', $post->category_id ?? '') === (string) $category->id)
        >
            {{ $category->name }}
        </option>
    @endforeach
</select>
@error('category_id')
    <div class="field-error">{{ $message }}</div>
@enderror

<label for="excerpt">Kısa açıklama</label>
<textarea
    id="excerpt"
    name="excerpt"
    rows="3"
>{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
@error('excerpt')
    <div class="field-error">{{ $message }}</div>
@enderror

<label for="content">İçerik</label>
<textarea
    id="content"
    name="content"
    rows="15"
    required
>{{ old('content', $post->content ?? '') }}</textarea>
@error('content')
    <div class="field-error">{{ $message }}</div>
@enderror

<label for="featured_image">Öne çıkan görsel adresi</label>
<input
    id="featured_image"
    name="featured_image"
    type="text"
    value="{{ old('featured_image', $post->featured_image ?? '') }}"
>
@error('featured_image')
    <div class="field-error">{{ $message }}</div>
@enderror

<label for="status">Yazı durumu</label>
<select id="status" name="status" required>
    <option
        value="draft"
        @selected(old('status', $post->status ?? 'draft') === 'draft')
    >
        Taslak
    </option>

    <option
        value="published"
        @selected(old('status', $post->status ?? '') === 'published')
    >
        Yayında
    </option>

    <option
        value="archived"
        @selected(old('status', $post->status ?? '') === 'archived')
    >
        Arşivlenmiş
    </option>
</select>
@error('status')
    <div class="field-error">{{ $message }}</div>
@enderror

<label for="published_at">Yayın tarihi</label>
<input
    id="published_at"
    name="published_at"
    type="datetime-local"
    value="{{ old(
        'published_at',
        isset($post) && $post->published_at
            ? $post->published_at->format('Y-m-d\TH:i')
            : ''
    ) }}"
>
@error('published_at')
    <div class="field-error">{{ $message }}</div>
@enderror

<fieldset>
    <legend>Etiketler</legend>

    <div class="tags">
        @forelse ($tags as $tag)
            <div class="tag-option">
                <input
                    id="tag-{{ $tag->id }}"
                    name="tag_ids[]"
                    type="checkbox"
                    value="{{ $tag->id }}"
                    @checked(in_array($tag->id, $selectedTags))
                >

                <label for="tag-{{ $tag->id }}">
                    {{ $tag->name }}
                </label>
            </div>
        @empty
            <p>Henüz etiket bulunmuyor.</p>
        @endforelse
    </div>
</fieldset>

@error('tag_ids')
    <div class="field-error">{{ $message }}</div>
@enderror

@error('tag_ids.*')
    <div class="field-error">{{ $message }}</div>
@enderror