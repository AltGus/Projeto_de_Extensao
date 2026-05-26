<?php
$workshop = $workshop ?? [];

$name = $workshop['name'] ?? '';
$description = $workshop['description'] ?? '';
$color = $workshop['color'] ?? '#4f46e5';
?>

<div class="form-group">
    <label for="name">Nome da oficina</label>

    <input
        class="input"
        type="text"
        id="name"
        name="name"
        value="<?= e($name) ?>"
        placeholder="Ex: Arte e Pintura, Costura, Artesanato"
        required
    >
</div>

<div class="form-group">
    <label for="description">Descrição da oficina</label>

    <textarea
        class="textarea"
        id="description"
        name="description"
        placeholder="Descreva o objetivo da oficina, atividades realizadas e tipo de produção artesanal"
        required
    ><?= e($description) ?></textarea>
</div>

<div class="form-group">
    <label for="color">Cor de identificação</label>

    <input
        class="input"
        type="color"
        id="color"
        name="color"
        value="<?= e($color) ?>"
    >

    <small style="color: var(--muted);">
        Essa cor será usada para identificar visualmente a oficina nos cards.
    </small>
</div>