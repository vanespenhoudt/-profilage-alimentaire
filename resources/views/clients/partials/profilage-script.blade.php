<script>
document.querySelectorAll('.js-profilage').forEach(function (box) {
    box.addEventListener('change', function () {
        var fait = box.checked;
        box.disabled = true;
        fetch(box.dataset.url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ fait: fait })
        })
        .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
        .then(function (data) {
            box.title = data.fait ? 'Fait le ' + data.date : 'Pas encore fait';
        })
        .catch(function () {
            box.checked = !fait;
            alert("Impossible d'enregistrer. Réessayez.");
        })
        .finally(function () { box.disabled = false; });
    });
});
</script>
