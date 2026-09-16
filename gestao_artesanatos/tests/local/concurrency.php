<?php
require __DIR__ . '/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'], 'test_')) exit(1);
$_SESSION=[];
if (($argv[1] ?? '') === 'worker') {
    $ok=stock_create_movement(['material_id'=>$argv[2],'movement_type'=>'saida','quantity'=>'1','movement_date'=>'2026-09-16']);
    exit($ok ? 0 : 2);
}
material_create(['name'=>'Concorrência','category'=>'Teste','unit'=>'un','current_quantity'=>'1','min_quantity'=>'0','applies_to_all'=>1]);
$id=(int)query_one("SELECT MAX(id) id FROM materials WHERE name='Concorrência'")['id'];
// Hold the row so both independent PHP processes overlap at the stock lock.
db()->beginTransaction();query_one('SELECT id FROM materials WHERE id=? FOR UPDATE',[$id]);
$children=[];
for($i=0;$i<2;$i++) $children[]=proc_open([PHP_BINARY,__FILE__,'worker',(string)$id],[0=>['file','/dev/null','r'],1=>['file','/dev/null','w'],2=>['file','/dev/null','w']],$pipes);
usleep(500000);db()->commit();
$codes=array_map('proc_close',$children);sort($codes);
if($codes!==[0,2] || material_find($id)['current_quantity']!=='0.000' || (int)query_one('SELECT COUNT(*) n FROM stock_movements WHERE material_id=?',[$id])['n']!==1) exit(1);
echo "OK: duas saídas simultâneas, apenas uma autorizada\n";
