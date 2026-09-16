<?php
// Run only against a fresh disposable database whose name starts with test_.
require __DIR__ . '/../../src/bootstrap.php';
if (!str_starts_with(config_database()['dbname'], 'test_')) exit("Use DB_DATABASE=test_gestao\n");
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$_SESSION = [];
try {
    db()->exec(file_get_contents(__DIR__ . '/../../database/schema.sql'));
    check(user_create(['name'=>'Professor','email'=>'prof@example.org','password'=>'senha-de-teste-123','role'=>'professor']), 'professor');
    $prof=last_insert_id();
    check(user_create(['name'=>'Aluno','email'=>'aluno@example.org','password'=>'senha-de-teste-123','role'=>'aluno']), 'aluno');
    $aluno=last_insert_id();
    $_SESSION['user']=['id'=>$prof,'role'=>'professor'];
    check(workshop_create(['name'=>'Costura']), 'oficina'); $w=last_insert_id();
    workshop_add_participant($w,$aluno); workshop_add_participant($w,$aluno);
    check((int)query_one('SELECT COUNT(*) n FROM workshop_user')['n'] === 1,'vínculo único');
    check(product_create(['name'=>'Bolsa','category'=>'Tecido','workshop_id'=>$w]), 'produto');$p=last_insert_id();
    check(material_create(['name'=>'Tecido','category'=>'Tecidos','unit'=>'m','current_quantity'=>'2,500','min_quantity'=>'0.500','workshop_ids'=>[$w]]), 'material');$m=(int)query_one("SELECT id FROM materials WHERE name='Tecido'")['id'];
    check(material_selected_workshops($m)===[$w],'oficina material');
    $move=['material_id'=>$m,'movement_type'=>'saida','quantity'=>'1.250','movement_date'=>'2026-09-16'];
    check(stock_create_movement($move),'saída');$mid=(int)query_one('SELECT MAX(id) id FROM stock_movements')['id'];
    check(material_find($m)['current_quantity']==='1.250','saldo saída');
    check(!stock_create_movement(array_replace($move,['quantity'=>'2'])),'saldo negativo bloqueado');
    check(stock_update_movement($mid,array_replace($move,['quantity'=>'0.250'])),'editar');
    check(material_find($m)['current_quantity']==='2.250','saldo editado');
    check(stock_delete_movement($mid),'excluir movimento');
    check(material_find($m)['current_quantity']==='2.500','saldo revertido');
    check(material_update($m,['name'=>'Tecido','category'=>'Tecidos','unit'=>'m','current_quantity'=>'999','min_quantity'=>'0','applies_to_all'=>1]),'editar material');
    check(material_find($m)['current_quantity']==='2.500','metadata não sobrescreve saldo');
    $data=['product_id'=>$p,'workshop_id'=>$w,'quantity'=>'2','produced_at'=>'2026-09-16','purpose'=>'Doação','description'=>str_repeat('Produção artesanal. ',4),'responsible_user_id'=>$prof];
    $_SESSION['user']=['id'=>$aluno,'role'=>'aluno'];
    check(!production_create(array_replace($data,['quantity'=>'0'])),'zero bloqueado');
    check(!production_create(array_replace($data,['quantity'=>'-1'])),'negativo bloqueado');
    check(production_create($data),'produção');$production=last_insert_id();
    check((int)production_find($production)['responsible_user_id']===$aluno,'responsável forjado ignorado');
    check(count(report_filtered_productions(['start'=>'2026-09-16','workshop_id'=>$w]))===1,'filtro');
    check(count(report_filtered_productions(['start'=>'2026-09-17']))===0,'filtro data');
    $_SESSION['user']=['id'=>$prof,'role'=>'professor'];
    execute_query('UPDATE users SET role=? WHERE id=?',['aluno',$prof]); refresh_session_user();
    check(!is_professor(),'revogação de perfil');
    execute_query('UPDATE users SET role=? WHERE id=?',['professor',$prof]); refresh_session_user();
    $before=(int)query_one('SELECT COUNT(*) n FROM activity_logs')['n'];
    db()->beginTransaction();
    activity_log('Teste','Operação que será revertida');
    check(!stock_create_movement(array_replace($move,['quantity'=>'999'])),'falha em transação externa');
    db()->rollBack();
    check((int)query_one('SELECT COUNT(*) n FROM activity_logs')['n']===$before,'rollback log');
    check(product_delete($p) && workshop_delete($w),'arquivar');
    check(production_find($production)!==null,'histórico preservado');
    check(count(products_all())===0 && count(workshops_all())===0,'arquivados fora dos cadastros');
    check(user_delete($prof),'arquivar usuário'); refresh_session_user();
    check(!is_logged(),'sessão revogada');
    echo "OK: integração de materiais, estoque, produção, filtros, histórico e sessão\n";
} catch (Throwable $e) { fwrite(STDERR,(string)$e . "\n"); exit(1); }
