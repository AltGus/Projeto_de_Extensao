<?php
require __DIR__.'/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'], 'test_')) exit(1);
function verify($ok,$message) { if (!$ok) throw new RuntimeException($message); }
function denied(callable $fn,$message) { try { $result=$fn(); if ($result===false) return; } catch (DomainException $e) { return; } throw new RuntimeException($message); }
try {
    $prof=query_one("SELECT id FROM users WHERE email='prof@example.org'")['id'];
    $_SESSION['user']=['id'=>$prof,'role'=>'professor'];
    $workshop=query_one("SELECT id FROM workshops WHERE name='Costura'")['id'];
    $product=query_one("SELECT id FROM products WHERE name='Bolsa'")['id'];
    student_create(['full_name'=>'Teste regressão','guardian_name'=>'Responsável Teste','workshop_ids'=>[$workshop]]);
    $student=(int)query_one('SELECT MAX(id) id FROM students')['id'];
    $base=['student_id'=>$student,'workshop_id'=>$workshop,'product_id'=>$product,'quantity'=>1,'produced_at'=>date('Y-m-d'),'description'=>'Descrição de teste de regressão','purpose'=>'Teste','responsible_user_id'=>$prof,'availability_status'=>'disponivel'];
    for ($i=0;$i<4;$i++) verify(production_create($base),'criar produção');
    $rows=student_productions($student);
    verify(delivery_mark($student,(int)$rows[0]['id']),'entrega');
    denied(fn()=>production_delete((int)$rows[3]['id']),'exclusão invalidou entrega de quatro trabalhos');
    denied(fn()=>production_update((int)$rows[0]['id'],$base),'edição de entregue');
    verify(production_create($base),'quinto trabalho');
    $last=(int)query_one('SELECT MAX(id) id FROM productions')['id'];
    verify(production_delete($last),'excluir quinto sem afetar entrega');
    verify(production_create($base),'sexto trabalho');
    verify((int)query_one('SELECT MAX(work_number) n FROM productions WHERE student_id=?',[$student])['n']===6,'número de trabalho reutilizado');
    denied(fn()=>production_create(array_replace($base,['quantity'=>0])),'quantidade zero');
    denied(fn()=>production_create(array_replace($base,['produced_at'=>date('Y-m-d',strtotime('+1 day'))])),'data futura');
    // Uma produção legada pode ser identificada sem inventar autoria na migração.
    execute_query('INSERT INTO productions(product_id,workshop_id,quantity,produced_at,responsible_user_id,purpose,description) VALUES(?,?,1,?,?,?,?)',[$product,$workshop,date('Y-m-d'),$prof,'Legado','Registro antigo sem aluno']);
    $legacy=last_insert_id();
    verify(production_update($legacy,$base),'vincular produção legada');
    verify((int)production_find($legacy)['work_number']===7,'sequência no vínculo legado');
    $before=(int)query_one('SELECT COUNT(*) n FROM students')['n'];
    denied(fn()=>student_create(['full_name'=>'Inválido','guardian_name'=>'Teste','workshop_ids'=>[2147483647]]),'oficina inexistente');
    verify((int)query_one('SELECT COUNT(*) n FROM students')['n']===$before,'cadastro parcial de aluno');
    denied(fn()=>student_create(['full_name'=>'Inválido','guardian_name'=>'Teste','father_phone'=>'123']),'telefone inválido');
    $mat=['name'=>'Material regressão','category'=>'Teste','unit'=>'m','current_quantity'=>'1.5','min_quantity'=>0,'applies_to_all'=>1];
    verify(material_create($mat),'precisão decimal padrão');
    $mid=(int)query_one('SELECT MAX(id) id FROM materials')['id'];
    denied(fn()=>material_update($mid,array_replace($mat,['quantity_mode'=>'integer','current_quantity'=>0])),'saldo real fracionário convertido em inteiro');
    denied(fn()=>material_update($mid,array_replace($mat,['unit'=>'un'])),'unidade do saldo alterada');
    verify(material_find($mid)['quantity_mode']==='decimal','precisão alterada apesar de erro');
    workshop_remove_participant((int)$workshop,$student);
    verify(count(student_history_workshops($student))===1,'oficina histórica desapareceu do filtro');
    echo "OK: regressões de entregas, exclusão, numeração, legado, cadastro atômico e precisão\n";
} catch (Throwable $e) { fwrite(STDERR,(string)$e."\n");exit(1); }
