-- Ejecutar en phpMyAdmin o consola MySQL sobre la BD `rrhh`

CREATE TABLE horas_extras (
    id_hora_extra INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    fecha DATE NOT NULL,
    horas DECIMAL(4,2) NOT NULL,
    tipo ENUM('diurna','nocturna') NOT NULL DEFAULT 'diurna',
    valor_hora DECIMAL(10,2) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    motivo VARCHAR(255),
    registrado_por VARCHAR(100),
    creada_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id)
);

-- Nueva columna en detalle_nomina para que el recibo muestre cuánto
-- correspondió a horas extra en ese período (igual que ya haces con
-- total_asignaciones y total_deducciones).
ALTER TABLE detalle_nomina
ADD COLUMN total_horas_extra DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total_asignaciones;
