-- =========================================================================
-- PROYECTO: CLICK SEGURO — DATOS SEMILLA INICIALES (CATÁLOGOS BASE)
-- =========================================================================

INSERT IGNORE INTO `tipo_riesgo` (`id_tipo_riesgo`, `nombre`, `descripcion`) VALUES
(1, 'Seguro', 'Ningún motor de seguridad detectó actividad maliciosa en el recurso.'),
(2, 'Sospechoso', 'Uno o dos motores reportaron indicadores anómalos o baja reputación.'),
(3, 'Peligroso', 'Múltiples motores confirmaron phishing, malware o distribución de amenazas.');

INSERT IGNORE INTO `motor_seguridad` (`nombre`, `activo`) VALUES
('Google Safe Browsing', 1),
('Kaspersky', 1),
('BitDefender', 1),
('ESET', 1),
('Fortinet', 1),
('Sophos', 1),
('PhishTank', 1),
('OpenPhish', 1);