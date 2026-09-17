<?php
$workshop=$workshop??[]; $orientators=$orientators??[];
$name=$workshop['name']??''; $description=$workshop['description']??''; $color=$workshop['color']??'#4f46e5'; $responsible=$workshop['responsible_user_id']??'';
?>
<div class="form-group"><label for="name">Nome da oficina</label><input class="input" type="text" id="name" name="name" value="<?= e($name) ?>" required></div>
<div class="form-group"><label for="description">Descrição da oficina</label><textarea class="textarea" id="description" name="description" required><?= e($description) ?></textarea></div>
<div class="form-group"><label for="responsible_user_id">Orientador responsável</label><select class="select" id="responsible_user_id" name="responsible_user_id"><option value="">Sem responsável definido</option><?php foreach($orientators as $u): ?><option value="<?= e($u['id']) ?>" <?= (string)$responsible===(string)$u['id']?'selected':'' ?>><?= e($u['name']) ?> — <?= e($u['email']) ?></option><?php endforeach; ?></select><small>Somente usuários professores/orientadores ativos aparecem aqui.</small></div>
<div class="form-group"><label for="image">Imagem da oficina (JPEG/JPG)</label><input class="input" type="file" id="image" name="image" accept="image/jpeg,.jpg,.jpeg"><?php if(!empty($workshop['image_path'])): ?><img class="workshop-preview" src="<?= e(url($workshop['image_path'])) ?>" alt="Imagem atual da oficina"><?php endif; ?><small>Máximo 5 MB e 12 megapixels. A miniatura é padronizada em proporção 16:9.</small></div>
<div class="form-group"><label for="color">Cor de identificação</label><input class="input" type="color" id="color" name="color" value="<?= e($color) ?>"></div>
