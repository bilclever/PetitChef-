{{-- Formulaire partagé create / edit --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div class="pc-field">
        <label class="pc-label">Nom du plat *</label>
        <input type="text" name="name" class="pc-input" value="{{ old('name', $dish->name ?? '') }}" placeholder="Riz gras au poulet" required>
    </div>
    <div class="pc-field">
        <label class="pc-label">Prix (FCFA) *</label>
        <input type="number" name="price" class="pc-input" value="{{ old('price', $dish->price ?? '') }}" placeholder="2500" min="0" required>
    </div>
</div>

<div class="pc-field" style="margin-top:14px">
    <label class="pc-label">Description</label>
    <textarea name="description" class="pc-textarea" placeholder="Ingrédients, préparation…">{{ old('description', $dish->description ?? '') }}</textarea>
</div>

<div class="pc-field" style="margin-top:14px">
    <label class="pc-label">Quantité disponible *</label>
    <input type="number" name="quantity" class="pc-input" value="{{ old('quantity', $dish->quantity ?? '') }}" placeholder="10" min="0" required>
</div>

<div class="pc-field" style="margin-top:14px">
    <label class="pc-label">Photo</label>

    <div id="photo-drop-zone" onclick="document.getElementById('photo-input').click()"
        ondragover="event.preventDefault();this.classList.add('drag-over')"
        ondragleave="this.classList.remove('drag-over')"
        ondrop="handleDrop(event)"
        style="border:2px dashed var(--border);border-radius:12px;padding:32px 20px;text-align:center;cursor:pointer;transition:all .2s;background:var(--cream)">

        <div id="photo-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;color:var(--mid-gray);margin:0 auto 10px;display:block">
                <rect x="3" y="3" width="18" height="18" rx="3"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
            <div style="font-size:14px;font-weight:500;color:var(--charcoal)">Glisser une photo ici</div>
            <div style="font-size:12px;color:var(--mid-gray);margin-top:4px">ou <span style="color:var(--terracotta);font-weight:600">cliquer pour parcourir</span></div>
            <div style="font-size:11px;color:var(--mid-gray);margin-top:8px;padding:4px 10px;background:var(--light-gray);border-radius:20px;display:inline-block">JPG, PNG — max 5 Mo</div>
        </div>

        <div id="photo-preview-wrap" style="display:none">
            <img id="photo-preview-img" src="" alt="" style="max-height:180px;border-radius:8px;object-fit:cover;margin-bottom:10px">
            <div style="font-size:12px;color:var(--sage);font-weight:600" id="photo-preview-name"></div>
            <div style="font-size:11px;color:var(--mid-gray);margin-top:4px">Cliquer pour changer</div>
        </div>
    </div>

    <input type="file" name="photo" id="photo-input" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="handleFileSelect(this)">

    @if(!empty($dish->photo_path))
        <div style="margin-top:10px;font-size:12px;color:var(--mid-gray)">Photo actuelle :</div>
        <img src="{{ asset('storage/'.$dish->photo_path) }}" style="width:100%;max-height:160px;object-fit:cover;border-radius:10px;margin-top:6px">
    @endif
</div>

<style>
#photo-drop-zone:hover, #photo-drop-zone.drag-over {
    border-color: var(--terracotta);
    background: #FEF0EA;
}
#photo-drop-zone.drag-over { transform: scale(1.01); }
</style>

<script>
function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { alert('Image trop lourde (max 5 Mo)'); return; }
    showPreview(file);
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('photo-drop-zone').classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('photo-input').files = dt.files;
    showPreview(file);
}
function showPreview(file) {
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photo-placeholder').style.display = 'none';
        document.getElementById('photo-preview-wrap').style.display = 'block';
        document.getElementById('photo-preview-img').src = e.target.result;
        document.getElementById('photo-preview-name').textContent = '✓ ' + file.name;
    };
    reader.readAsDataURL(file);
}
</script>

<div style="display:flex;align-items:center;gap:10px;margin-top:16px">
    <input type="checkbox" name="is_of_day" id="is_of_day" value="1" style="width:16px;height:16px;accent-color:var(--terracotta)"
        {{ old('is_of_day', $dish->is_of_day ?? false) ? 'checked' : '' }}>
    <label for="is_of_day" style="font-size:13px;font-weight:500;cursor:pointer">⭐ Marquer comme plat du jour</label>
</div>
