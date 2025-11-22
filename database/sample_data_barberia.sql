-- Sample data for barbería (small, coherent dataset)
-- Inserts respect FK constraints and are ordered to avoid FK violations.

BEGIN;

-- Tipo de pagos
INSERT INTO tipo_pagos (id, nombre) VALUES (1, 'contado'), (2, 'tarjeta'), (3, 'transferencia');

-- Usuarios: propietario, barberos, secretaria, clientes
INSERT INTO usuarios (id, nombre, apellido, email, password, telefono, rol, fecha_registro) VALUES
  (1, 'Miguel', 'Acs', 'miguel@barberia.bo', 'miguelpass', '72000001', 'propietario', now()),
  (2, 'Juan', 'Perez', 'juan.barbero@barberia.bo', 'juanpass', '72000002', 'barbero', now()),
  (3, 'Carlos', 'Lopez', 'carlos.barbero@barberia.bo', 'carlospass', '72000003', 'barbero', now()),
  (4, 'Ana', 'Gomez', 'ana.secretaria@barberia.bo', 'anapass', '72000004', 'secretaria', now()),
  (5, 'Luis', 'Garcia', 'luis.cliente@gmail.com', 'luispass', '78000001', 'cliente', now()),
  (6, 'Sofia', 'Reyes', 'sofia.cliente@gmail.com', 'sofiapass', '78000002', 'cliente', now());

-- Servicios
INSERT INTO servicios (id, nombre, descripcion, duracion_estimada, precio) VALUES
  (1, 'Corte de cabello', 'Corte clásico de caballero', 30, 15.00),
  (2, 'Afeitado', 'Afeitado con navaja y toalla caliente', 20, 10.00),
  (3, 'Corte + Barba', 'Corte y arreglo de barba', 45, 25.00),
  (4, 'Coloración', 'Tinte y tratamiento', 60, 40.00);

-- Productos
INSERT INTO productos (id, nombre, descripcion, precio_venta, stock_minimo, stock_actual) VALUES
  (1, 'Shampoo profesional', 'Shampoo para todo tipo de cabello 500ml', 8.50, 2, 10),
  (2, 'Pomada', 'Pomada para peinar 100ml', 6.00, 2, 15);

-- Horarios de barberos (lunes a viernes 09:00-18:00)
INSERT INTO horario_barberos (id, usuario_id, dia_semana, hora_inicio, hora_fin) VALUES
  (1, 2, '1', '09:00:00', '18:00:00'),
  (2, 2, '2', '09:00:00', '18:00:00'),
  (3, 2, '3', '09:00:00', '18:00:00'),
  (4, 2, '4', '09:00:00', '18:00:00'),
  (5, 2, '5', '09:00:00', '18:00:00'),
  (6, 3, '1', '10:00:00', '19:00:00'),
  (7, 3, '2', '10:00:00', '19:00:00'),
  (8, 3, '3', '10:00:00', '19:00:00');

-- Excepción: Carlos (id=3) no disponible un día concreto
INSERT INTO excepciones_horarios (id, usuario_id, es_disponible, fecha, hora_inicio, hora_fin) VALUES
  (1, 3, false, (current_date + 7), '00:00:00', '23:59:59');

-- Movimientos de inventario (ingreso inicial)
INSERT INTO movimiento_inventarios (id, producto_id, usuario_id, cantidad, motivo, tipo_movimiento, fecha) VALUES
  (1, 1, 1, 10, 'Stock inicial', 'ingreso', now()),
  (2, 2, 1, 15, 'Stock inicial', 'ingreso', now());

-- Citas: algunos estados distintos
-- cita 1: pending, cliente 5 with Juan (barbero 2)
INSERT INTO citas (id, cliente_id, barbero_id, tipo_pago_id, pago_inicial, fecha_hora_inicio, fecha_hora_fin, estado) VALUES
  (1, 5, 2, 1, 0.00, '2025-11-10 10:00:00', '2025-11-10 10:30:00', 'pendiente_pago_adelanto'),
  (2, 6, 3, 2, 5.00, '2025-11-11 14:00:00', '2025-11-11 14:45:00', 'confirmada'),
  (3, 5, 3, 1, 0.00, '2025-11-09 16:00:00', '2025-11-09 16:30:00', 'completada');

-- Servicios por cita
INSERT INTO cita_servicios (id, cita_id, servicio_id, precio_cobrado) VALUES
  (1, 1, 1, 15.00),
  (2, 2, 3, 25.00),
  (3, 3, 2, 10.00);

-- Venta asociada a cita 3 (completada)
INSERT INTO ventas (id, cita_id, cliente_id, usuario_id, monto_total, fecha_hora, estado_pago, tipo_pago) VALUES
  (1, 3, 5, 4, 10.00, '2025-11-09 17:00:00', 'pagado', 'contado');

-- Detalles de la venta: servicio cobrado
INSERT INTO detalles (id, producto_id, servicio_id, venta_id, cantidad, precio_unitario) VALUES
  (1, NULL, 2, 1, 1, 10.00);

-- Pago por la venta
-- Nota: el esquema de respaldo usa columna `tipo_pago_id` y `monto` (sin columna `metodo_pago`),
-- por eso aquí insertamos (id, tipo_pago_id, venta_id, monto).
INSERT INTO detalle_pagos (id, tipo_pago_id, venta_id, monto) VALUES
  (1, 1, 1, 10.00);

-- Más datos añadidos: usuarios adicionales, servicios, productos, movimientos, citas y ventas

-- Usuarios adicionales (clientes y un administrativo)
INSERT INTO usuarios (id, nombre, apellido, email, password, telefono, rol, fecha_registro) VALUES
  (7, 'Marcos', 'Vargas', 'marcos.cliente@gmail.com', 'marcospass', '78000003', 'cliente', now()),
  (8, 'Valeria', 'Ramos', 'valeria.cliente@gmail.com', 'valeriapass', '78000004', 'cliente', now()),
  (9, 'Diego', 'Ramirez', 'diego.admin@barberia.bo', 'diegopass', '72000005', 'propietario', now()),
  (10, 'Flor', 'Mendez', 'flor.cliente@gmail.com', 'florpass', '78000005', 'cliente', now());

-- Servicios adicionales
INSERT INTO servicios (id, nombre, descripcion, duracion_estimada, precio) VALUES
  (5, 'Tratamiento capilar', 'Mascarilla y secado profesional', 40, 20.00),
  (6, 'Perfilado de barba', 'Perfilado detalle', 15, 8.00);

-- Producto adicional
INSERT INTO productos (id, nombre, descripcion, precio_venta, stock_minimo, stock_actual) VALUES
  (3, 'Acondicionador', 'Acondicionador 500ml', 7.50, 2, 12);

-- Movimientos: compras y ajustes
INSERT INTO movimiento_inventarios (id, producto_id, usuario_id, cantidad, motivo, tipo_movimiento, fecha) VALUES
  (3, 1, 1, 5, 'Compra mensual', 'ingreso', now()),
  (4, 2, 1, 2, 'Ajuste por rotura', 'ajuste', now()),
  (5, 3, 1, 12, 'Stock inicial acondicionador', 'ingreso', now());

-- Nuevas citas: mezcla de clientes/barberos y estados
INSERT INTO citas (id, cliente_id, barbero_id, tipo_pago_id, pago_inicial, fecha_hora_inicio, fecha_hora_fin, estado) VALUES
  (4, 7, 2, 1, 0.00, '2025-11-12 09:30:00', '2025-11-12 10:00:00', 'pendiente_pago_adelanto'),
  (5, 8, 3, 1, 0.00, '2025-11-12 11:00:00', '2025-11-12 11:30:00', 'confirmada'),
  (6, 10, 2, 2, 3.00, '2025-11-13 15:00:00', '2025-11-13 15:45:00', 'confirmada');

-- Servicios por las nuevas citas
INSERT INTO cita_servicios (id, cita_id, servicio_id, precio_cobrado) VALUES
  (4, 4, 1, 15.00),
  (5, 5, 5, 20.00),
  (6, 5, 6, 8.00),
  (7, 6, 3, 25.00),
  (8, 6, 6, 8.00);

-- Ventas asociadas (cita 5 pagada al completar)
INSERT INTO ventas (id, cita_id, cliente_id, usuario_id, monto_total, fecha_hora, estado_pago, tipo_pago) VALUES
  (2, 5, 8, 4, 28.00, '2025-11-12 12:00:00', 'pagado', 'tarjeta');

-- Detalle de venta 2
INSERT INTO detalles (id, producto_id, servicio_id, venta_id, cantidad, precio_unitario) VALUES
  (2, NULL, 5, 2, 1, 20.00),
  (3, NULL, 6, 2, 1, 8.00);

-- Pago por la venta 2
INSERT INTO detalle_pagos (id, tipo_pago_id, venta_id, monto) VALUES
  (2, 2, 2, 28.00);

-- Venta para cita 6 (pendiente saldo)
INSERT INTO ventas (id, cita_id, cliente_id, usuario_id, monto_total, fecha_hora, estado_pago, tipo_pago) VALUES
  (3, 6, 10, 4, 28.00, '2025-11-13 16:00:00', 'pendiente_saldo', 'contado');

INSERT INTO detalles (id, producto_id, servicio_id, venta_id, cantidad, precio_unitario) VALUES
  (4, NULL, 3, 3, 1, 25.00),
  (5, NULL, 6, 3, 1, 8.00);

INSERT INTO detalle_pagos (id, tipo_pago_id, venta_id, monto) VALUES
  (3, 1, 3, 3.00);

-- === Dataset ampliado para reportes ===
-- Usuarios adicionales (clientes) ids 11..20
INSERT INTO usuarios (id, nombre, apellido, email, password, telefono, rol, fecha_registro) VALUES
  (11, 'Ruben', 'Ortega', 'ruben.cliente@gmail.com', 'rubenpass', '78000006', 'cliente', now()),
  (12, 'Elena', 'Diaz', 'elena.cliente@gmail.com', 'elenapass', '78000007', 'cliente', now()),
  (13, 'Mateo', 'Castro', 'mateo.cliente@gmail.com', 'mateopass', '78000008', 'cliente', now()),
  (14, 'Camila', 'Soto', 'camila.cliente@gmail.com', 'camilapass', '78000009', 'cliente', now()),
  (15, 'Jorge', 'Flores', 'jorge.cliente@gmail.com', 'jorgepass', '78000010', 'cliente', now()),
  (16, 'Paola', 'Rueda', 'paola.cliente@gmail.com', 'paolapass', '78000011', 'cliente', now()),
  (17, 'Andres', 'Silva', 'andres.cliente@gmail.com', 'andrespass', '78000012', 'cliente', now()),
  (18, 'Natalia', 'Vera', 'natalia.cliente@gmail.com', 'nataliapass', '78000013', 'cliente', now()),
  (19, 'Hector', 'Molina', 'hector.cliente@gmail.com', 'hectorpass', '78000014', 'cliente', now()),
  (20, 'Rosa', 'Lopez', 'rosa.cliente@gmail.com', 'rosapass', '78000015', 'cliente', now());

-- Servicios adicionales (7..8)
INSERT INTO servicios (id, nombre, descripcion, duracion_estimada, precio) VALUES
  (7, 'Lavado express', 'Lavado y secado rapido', 15, 6.00),
  (8, 'Masaje craneal', 'Masaje relajante de cuero cabelludo', 20, 12.00);

-- Productos adicionales (4)
INSERT INTO productos (id, nombre, descripcion, precio_venta, stock_minimo, stock_actual) VALUES
  (4, 'Gel fijador', 'Gel para peinar 150ml', 5.50, 2, 20);

-- Movimientos adicionales (6..9)
INSERT INTO movimiento_inventarios (id, producto_id, usuario_id, cantidad, motivo, tipo_movimiento, fecha) VALUES
  (6, 4, 1, 20, 'Compra proveedor A', 'ingreso', now()),
  (7, 2, 1, 5, 'Venta mostrador', 'salida_venta', now()),
  (8, 1, 1, 2, 'Ajuste stock', 'ajuste', now()),
  (9, 3, 1, 3, 'Reposición acondicionador', 'ingreso', now());

-- Añadimos varias citas (ids 7..26) para obtener un volumen razonable
INSERT INTO citas (id, cliente_id, barbero_id, tipo_pago_id, pago_inicial, fecha_hora_inicio, fecha_hora_fin, estado) VALUES
  (7, 11, 2, 1, 0.00, '2025-11-05 09:00:00', '2025-11-05 09:30:00', 'completada'),
  (8, 12, 2, 1, 0.00, '2025-11-05 09:45:00', '2025-11-05 10:15:00', 'completada'),
  (9, 13, 3, 2, 5.00, '2025-11-06 10:00:00', '2025-11-06 10:30:00', 'confirmada'),
  (10, 14, 3, 1, 0.00, '2025-11-06 10:45:00', '2025-11-06 11:15:00', 'pendiente_pago_adelanto'),
  (11, 15, 2, 1, 0.00, '2025-11-07 11:30:00', '2025-11-07 12:00:00', 'completada'),
  (12, 16, 3, 2, 10.00, '2025-11-07 12:15:00', '2025-11-07 12:45:00', 'confirmada'),
  (13, 17, 2, 1, 0.00, '2025-11-08 13:00:00', '2025-11-08 13:30:00', 'completada'),
  (14, 18, 3, 1, 0.00, '2025-11-08 14:00:00', '2025-11-08 14:30:00', 'cancelada'),
  (15, 19, 2, 1, 0.00, '2025-11-09 15:00:00', '2025-11-09 15:30:00', 'completada'),
  (16, 20, 3, 2, 5.00, '2025-11-10 16:00:00', '2025-11-10 16:30:00', 'confirmada'),
  (17, 5, 2, 1, 0.00, '2025-11-11 09:00:00', '2025-11-11 09:30:00', 'completada'),
  (18, 6, 3, 1, 0.00, '2025-11-11 10:00:00', '2025-11-11 10:30:00', 'completada'),
  (19, 7, 2, 1, 0.00, '2025-11-12 11:30:00', '2025-11-12 12:00:00', 'pendiente_pago_adelanto'),
  (20, 8, 3, 1, 0.00, '2025-11-12 12:30:00', '2025-11-12 13:00:00', 'confirmada'),
  (21, 9, 2, 1, 0.00, '2025-11-13 09:00:00', '2025-11-13 09:30:00', 'completada'),
  (22, 10, 3, 1, 0.00, '2025-11-13 10:00:00', '2025-11-13 10:30:00', 'completada'),
  (23, 11, 2, 1, 0.00, '2025-11-14 11:00:00', '2025-11-14 11:30:00', 'confirmada'),
  (24, 12, 3, 2, 5.00, '2025-11-14 12:00:00', '2025-11-14 12:30:00', 'confirmada'),
  (25, 13, 2, 1, 0.00, '2025-11-15 13:00:00', '2025-11-15 13:30:00', 'completada'),
  (26, 14, 3, 1, 0.00, '2025-11-15 14:00:00', '2025-11-15 14:30:00', 'completada');

-- Servicios por estas citas (ids 9..30)
INSERT INTO cita_servicios (id, cita_id, servicio_id, precio_cobrado) VALUES
  (9, 7, 1, 15.00),
  (10, 8, 7, 6.00),
  (11, 9, 3, 25.00),
  (12, 10, 4, 40.00),
  (13, 11, 2, 10.00),
  (14, 12, 5, 20.00),
  (15, 13, 1, 15.00),
  (16, 15, 3, 25.00),
  (17, 16, 6, 8.00),
  (18, 17, 1, 15.00),
  (19, 18, 2, 10.00),
  (20, 21, 4, 40.00),
  (21, 22, 5, 20.00),
  (22, 23, 1, 15.00),
  (23, 24, 7, 6.00),
  (24, 25, 3, 25.00),
  (25, 26, 5, 20.00);

-- Ventas y detalles para citas completadas (ventas ids 4..10)
INSERT INTO ventas (id, cita_id, cliente_id, usuario_id, monto_total, fecha_hora, estado_pago, tipo_pago) VALUES
  (4, 7, 11, 4, 15.00, '2025-11-05 09:45:00', 'pagado', 'contado'),
  (5, 8, 12, 4, 6.00, '2025-11-05 10:30:00', 'pagado', 'contado'),
  (6, 11, 15, 4, 10.00, '2025-11-07 12:00:00', 'pagado', 'contado'),
  (7, 13, 17, 4, 15.00, '2025-11-08 13:30:00', 'pagado', 'contado'),
  (8, 15, 19, 4, 25.00, '2025-11-09 16:00:00', 'pagado', 'contado'),
  (9, 17, 5, 4, 15.00, '2025-11-11 09:45:00', 'pagado', 'contado'),
  (10, 18, 6, 4, 10.00, '2025-11-11 10:45:00', 'pagado', 'contado');

INSERT INTO detalles (id, producto_id, servicio_id, venta_id, cantidad, precio_unitario) VALUES
  (6, NULL, 1, 4, 1, 15.00),
  (7, NULL, 7, 5, 1, 6.00),
  (8, NULL, 2, 6, 1, 10.00),
  (9, NULL, 1, 7, 1, 15.00),
  (10, NULL, 3, 8, 1, 25.00),
  (11, NULL, 1, 9, 1, 15.00),
  (12, NULL, 2, 10, 1, 10.00);

INSERT INTO detalle_pagos (id, tipo_pago_id, venta_id, monto) VALUES
  (4, 1, 4, 15.00),
  (5, 1, 5, 6.00),
  (6, 1, 6, 10.00),
  (7, 1, 7, 15.00),
  (8, 1, 8, 25.00),
  (9, 1, 9, 15.00),
  (10, 1, 10, 10.00);

-- Fin del dataset ampliado


-- === Ventas sin cita (ventas de productos en mostrador) ===
-- Estas ventas no tienen `cita_id` (NULL) y representan ventas de productos al contado/mostrador
INSERT INTO ventas (id, cita_id, cliente_id, usuario_id, monto_total, fecha_hora, estado_pago, tipo_pago) VALUES
  (11, NULL, 5, 4, 17.50, now(), 'pagado', 'contado'),
  (12, NULL, 12, 4, 11.00, now(), 'pagado', 'contado');

-- Detalles para ventas de productos (referencian productos existentes)
INSERT INTO detalles (id, producto_id, servicio_id, venta_id, cantidad, precio_unitario) VALUES
  (13, 1, NULL, 11, 1, 8.50),
  (14, 2, NULL, 11, 1, 6.00),
  (15, 4, NULL, 12, 2, 5.50);

-- Pagos para ventas de productos
INSERT INTO detalle_pagos (id, tipo_pago_id, venta_id, monto) VALUES
  (11, 1, 11, 17.50),
  (12, 1, 12, 11.00);

-- Movimientos de inventario para ventas de mostrador (salida_venta)
INSERT INTO movimiento_inventarios (id, producto_id, usuario_id, cantidad, motivo, tipo_movimiento, fecha) VALUES
  (10, 1, 4, 1, 'Venta mostrador - shampoo', 'salida_venta', now()),
  (11, 4, 4, 2, 'Venta mostrador - gel', 'salida_venta', now());

COMMIT;

-- End of sample data (expanded)