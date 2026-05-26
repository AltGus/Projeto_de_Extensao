USE gestao_artesanal;

-- Senha dos dois usuários abaixo: password
-- Professor: admin@ong.local
-- Aluno: aluno@ong.local

INSERT INTO users (name, email, password, role) VALUES
('Administrador do Sistema', 'admin@ong.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'professor'),
('Aluno Participante', 'aluno@ong.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'aluno');

INSERT INTO workshops (name, description, color) VALUES
('Arte e Pintura', 'Oficina voltada para pinturas, desenhos, telas e atividades artísticas.', '#4f46e5'),
('Costura', 'Oficina voltada para costura, tecidos, bordados e peças manuais.', '#db2777'),
('Artesanato', 'Oficina geral para criação de peças artesanais variadas.', '#16a34a'),
('Pintura em Potes', 'Oficina focada em decoração e customização de potes.', '#f97316');

INSERT INTO workshop_user (workshop_id, user_id) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(4, 1);

INSERT INTO products (name, description, category, workshop_id) VALUES
('Pintura em Tela', 'Pintura artística produzida pelos participantes.', 'Pintura', 1),
('Pote Decorado', 'Pote reaproveitado e decorado artesanalmente.', 'Decoração', 4),
('Cesto Artesanal', 'Cesto feito com materiais artesanais.', 'Artesanato', 3),
('Bordado Manual', 'Peça artesanal produzida com linha e tecido.', 'Costura', 2),
('Peça Artesanal Variada', 'Produto artesanal produzido nas oficinas.', 'Artesanato', 3);

INSERT INTO productions (
    product_id,
    workshop_id,
    quantity,
    produced_at,
    responsible_user_id,
    purpose,
    description
) VALUES
(1, 1, 12, '2026-05-01', 1, 'Exposição interna', 'Produção de pinturas para exposição da ONG.'),
(2, 4, 8, '2026-05-02', 1, 'Venda beneficente', 'Potes decorados para feira beneficente.'),
(3, 3, 5, '2026-05-03', 1, 'Oficina prática', 'Cestos produzidos durante aula de artesanato.'),
(4, 2, 10, '2026-05-04', 1, 'Aprendizado técnico', 'Bordados criados pelos participantes.'),
(5, 3, 7, '2026-05-05', 1, 'Atividade livre', 'Peças artesanais variadas produzidas em oficina.');

INSERT INTO materials (name, category, unit, current_quantity, min_quantity) VALUES
('Tinta acrílica', 'Tintas', 'un', 30, 5),
('Tinta guache', 'Tintas', 'un', 25, 5),
('Pincéis', 'Ferramentas', 'un', 20, 5),
('Tesouras', 'Ferramentas', 'un', 12, 3),
('Agulhas', 'Ferramentas', 'un', 40, 10),
('Linha', 'Materiais', 'un', 35, 8),
('Lã', 'Materiais', 'un', 22, 5),
('Tecido', 'Materiais', 'm', 18, 4),
('Papel', 'Materiais', 'un', 60, 15),
('Cola', 'Materiais', 'un', 16, 4);

INSERT INTO stock_movements (
    material_id,
    movement_type,
    quantity,
    notes,
    movement_date,
    user_id
) VALUES
(1, 'entrada', 30, 'Estoque inicial de tinta acrílica.', '2026-05-01', 1),
(2, 'entrada', 25, 'Estoque inicial de tinta guache.', '2026-05-01', 1),
(3, 'entrada', 20, 'Estoque inicial de pincéis.', '2026-05-01', 1),
(4, 'entrada', 12, 'Estoque inicial de tesouras.', '2026-05-01', 1),
(5, 'entrada', 40, 'Estoque inicial de agulhas.', '2026-05-01', 1),
(6, 'entrada', 35, 'Estoque inicial de linha.', '2026-05-01', 1),
(7, 'entrada', 22, 'Estoque inicial de lã.', '2026-05-01', 1),
(8, 'entrada', 18, 'Estoque inicial de tecido.', '2026-05-01', 1),
(9, 'entrada', 60, 'Estoque inicial de papel.', '2026-05-01', 1),
(10, 'entrada', 16, 'Estoque inicial de cola.', '2026-05-01', 1),

(1, 'saida', 4, 'Uso de tinta acrílica na oficina de Arte e Pintura.', '2026-05-03', 1),
(3, 'saida', 2, 'Uso de pincéis durante atividade de pintura.', '2026-05-03', 1),
(6, 'saida', 5, 'Uso de linha na oficina de costura.', '2026-05-04', 1),
(8, 'saida', 3, 'Uso de tecido para bordados.', '2026-05-04', 1);

INSERT INTO activity_logs (user_id, action, description) VALUES
(1, 'Sistema iniciado', 'Dados iniciais do sistema foram cadastrados.'),
(1, 'Oficinas criadas', 'Oficinas exemplo foram registradas no sistema.'),
(1, 'Produções cadastradas', 'Produções iniciais foram adicionadas para demonstração.'),
(1, 'Estoque inicial', 'Materiais e movimentações iniciais foram cadastrados.');