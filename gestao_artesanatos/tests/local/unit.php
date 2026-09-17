<?php
require __DIR__ . '/../../src/lib.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
check(quantity_milli('1,500') === 1500, 'vírgula decimal');
check(decimal_quantity('0.001') === '0.001', 'milésimos');
check(milli_decimal(quantity_milli('1.5') - quantity_milli('0.4')) === '1.100', 'precisão');
foreach (['-1','0.0001','1e3','1000000000','abc',[]] as $bad) {
    try { quantity_milli($bad); throw new RuntimeException('Quantidade inválida aceita'); }
    catch (DomainException $e) {}
}
check(valid_date('2024-02-29') && !valid_date('2025-02-29'), 'calendário');
check(csv_safe('=1+1') === "'=1+1", 'injeção CSV');
check(report_period_dates(['period_mode'=>'monthly','year'=>2026,'month'=>2]) === ['2026-02-01','2026-02-28'], 'período mensal');
check(report_period_dates(['period_mode'=>'semester','year'=>2026,'semester'=>2]) === ['2026-07-01','2026-12-31'], 'período semestral');
echo "OK: quantidades, datas, períodos e proteção CSV\n";
