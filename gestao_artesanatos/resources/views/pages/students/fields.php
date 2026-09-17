<?php
$student=$student??[]; $workshops=$workshops??[]; $selectedWorkshops=$selectedWorkshops??[];
?>
<div class="form-group"><label for="full_name">Nome completo</label><input class="input" id="full_name" name="full_name" maxlength="160" value="<?= e($student['full_name']??'') ?>" required></div>
<div class="form-group"><label for="guardian_name">Responsável ou responsáveis</label><input class="input" id="guardian_name" name="guardian_name" maxlength="200" value="<?= e($student['guardian_name']??'') ?>" required></div>
<div class="grid two">
<div class="form-group"><label for="father_phone">Telefone do pai (opcional)</label><input class="input" id="father_phone" name="father_phone" maxlength="30" value="<?= e($student['father_phone']??'') ?>" inputmode="tel"></div>
<div class="form-group"><label for="mother_phone">Telefone da mãe (opcional)</label><input class="input" id="mother_phone" name="mother_phone" maxlength="30" value="<?= e($student['mother_phone']??'') ?>" inputmode="tel"></div>
</div>
<div class="form-group"><label>Oficinas</label><div class="check-grid">
<?php foreach($workshops as $w): ?><label><input type="checkbox" name="workshop_ids[]" value="<?= e($w['id']) ?>" <?= in_array((int)$w['id'],array_map('intval',$selectedWorkshops),true)?'checked':'' ?>> <?= e($w['name']) ?></label><?php endforeach; ?>
<?php if(!$workshops): ?><small>Nenhuma oficina cadastrada.</small><?php endif; ?>
</div></div>
<div class="form-group"><label for="notes">Observações administrativas</label><textarea class="textarea" id="notes" name="notes"><?= e($student['notes']??'') ?></textarea></div>
