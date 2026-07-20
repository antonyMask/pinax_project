USE `sistema_contable_pinax`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sistema_contable_pinax
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping routines for database 'sistema_contable_pinax'
--
/*!50003 DROP PROCEDURE IF EXISTS `cc_ins_modulo_catalogo` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `cc_ins_modulo_catalogo`(

    IN p_cod_tipo_cuenta BIGINT,

    IN p_nom_tipo_cuenta VARCHAR(80),

    IN p_ind_naturaleza_tipo VARCHAR(20),

    IN p_des_tipo_cuenta VARCHAR(255),



    IN p_cod_num_cuenta VARCHAR(30),

    IN p_nom_cuenta VARCHAR(150),

    IN p_cod_cuenta_padre BIGINT,

    IN p_num_nivel_jerarquia TINYINT,

    IN p_ind_naturaleza_cuenta VARCHAR(20),

    IN p_ind_acepta_movimiento VARCHAR(10),

    IN p_des_cuenta VARCHAR(255),

    IN p_ind_estado VARCHAR(20),

    IN p_usr_adicion VARCHAR(100),



    OUT p_cod_cuenta_generada BIGINT

)
BEGIN

    DECLARE v_cod_tipo_cuenta BIGINT;



    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        SET p_cod_cuenta_generada = NULL;

    END;



    START TRANSACTION;



    IF p_ind_naturaleza_cuenta NOT IN ('deudora', 'acreedora') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: naturaleza de cuenta no valida.';

    END IF;



    IF p_ind_acepta_movimiento NOT IN ('si', 'no') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: indicador de movimiento no valido.';

    END IF;



    IF p_ind_estado NOT IN ('activo', 'inactivo') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de cuenta no valido.';

    END IF;



    IF p_cod_tipo_cuenta IS NULL OR p_cod_tipo_cuenta = 0 THEN



        IF p_ind_naturaleza_tipo NOT IN ('deudora', 'acreedora') THEN

            SIGNAL SQLSTATE '45000'

            SET MESSAGE_TEXT = 'Error: naturaleza de tipo de cuenta no valida.';

        END IF;



        INSERT INTO cc_tipo_cuenta (

            nom_tipo_cuenta,

            ind_naturaleza,

            des_tipo_cuenta,

            ind_estado,

            usr_adicion,

            fec_adicion

        ) VALUES (

            p_nom_tipo_cuenta,

            p_ind_naturaleza_tipo,

            p_des_tipo_cuenta,

            'activo',

            IFNULL(p_usr_adicion, 'sistema'),

            NOW()

        );



        SET v_cod_tipo_cuenta = LAST_INSERT_ID();



    ELSE



        IF NOT EXISTS (

            SELECT 1

            FROM cc_tipo_cuenta

            WHERE cod_tipo_cuenta = p_cod_tipo_cuenta

        ) THEN

            SIGNAL SQLSTATE '45000'

            SET MESSAGE_TEXT = 'Error: el tipo de cuenta no existe.';

        END IF;



        SET v_cod_tipo_cuenta = p_cod_tipo_cuenta;

    END IF;



    INSERT INTO cc_catalogo_cuenta (

        cod_num_cuenta,

        nom_cuenta,

        cod_tipo_cuenta,

        cod_cuenta_padre,

        num_nivel_jerarquia,

        ind_naturaleza,

        ind_acepta_movimiento,

        des_cuenta,

        ind_estado,

        usr_adicion,

        fec_adicion

    ) VALUES (

        p_cod_num_cuenta,

        p_nom_cuenta,

        v_cod_tipo_cuenta,

        p_cod_cuenta_padre,

        p_num_nivel_jerarquia,

        p_ind_naturaleza_cuenta,

        p_ind_acepta_movimiento,

        p_des_cuenta,

        IFNULL(p_ind_estado, 'activo'),

        IFNULL(p_usr_adicion, 'sistema'),

        NOW()

    );



    SET p_cod_cuenta_generada = LAST_INSERT_ID();



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cc_sel_modulo_catalogo` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `cc_sel_modulo_catalogo`(

    IN p_cod_tipo_cuenta BIGINT

)
BEGIN

    SELECT

        cc.cod_cuenta,

        cc.cod_num_cuenta,

        cc.nom_cuenta,

        cc.cod_tipo_cuenta,

        tc.nom_tipo_cuenta,

        tc.ind_naturaleza AS naturaleza_tipo,

        cc.cod_cuenta_padre,

        cp.nom_cuenta AS nom_cuenta_padre,

        cc.num_nivel_jerarquia,

        cc.ind_naturaleza AS naturaleza_cuenta,

        cc.ind_acepta_movimiento,

        cc.des_cuenta,

        cc.ind_estado,

        cc.fec_adicion

    FROM cc_catalogo_cuenta cc

    INNER JOIN cc_tipo_cuenta tc

        ON cc.cod_tipo_cuenta = tc.cod_tipo_cuenta

    LEFT JOIN cc_catalogo_cuenta cp

        ON cc.cod_cuenta_padre = cp.cod_cuenta

    WHERE p_cod_tipo_cuenta IS NULL

       OR p_cod_tipo_cuenta = 0

       OR cc.cod_tipo_cuenta = p_cod_tipo_cuenta

    ORDER BY cc.cod_cuenta;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cc_upd_modulo_catalogo` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `cc_upd_modulo_catalogo`(

    IN p_cod_cuenta BIGINT,

    IN p_cod_num_cuenta VARCHAR(30),

    IN p_nom_cuenta VARCHAR(150),

    IN p_cod_tipo_cuenta BIGINT,

    IN p_cod_cuenta_padre BIGINT,

    IN p_num_nivel_jerarquia TINYINT,

    IN p_ind_naturaleza_cuenta VARCHAR(20),

    IN p_ind_acepta_movimiento VARCHAR(10),

    IN p_des_cuenta VARCHAR(255),

    IN p_ind_estado VARCHAR(20),

    IN p_usr_modificacion VARCHAR(100),



    IN p_actualizar_tipo BOOLEAN,

    IN p_nom_tipo_cuenta VARCHAR(80),

    IN p_ind_naturaleza_tipo VARCHAR(20),

    IN p_des_tipo_cuenta VARCHAR(255)

)
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

    END;



    START TRANSACTION;



    IF NOT EXISTS (

        SELECT 1

        FROM cc_catalogo_cuenta

        WHERE cod_cuenta = p_cod_cuenta

    ) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: la cuenta contable no existe.';

    END IF;



    IF p_ind_naturaleza_cuenta NOT IN ('deudora', 'acreedora') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: naturaleza de cuenta no valida.';

    END IF;



    IF p_ind_acepta_movimiento NOT IN ('si', 'no') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: indicador de movimiento no valido.';

    END IF;



    IF p_ind_estado NOT IN ('activo', 'inactivo') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de cuenta no valido.';

    END IF;



    UPDATE cc_catalogo_cuenta

    SET

        cod_num_cuenta = p_cod_num_cuenta,

        nom_cuenta = p_nom_cuenta,

        cod_tipo_cuenta = p_cod_tipo_cuenta,

        cod_cuenta_padre = p_cod_cuenta_padre,

        num_nivel_jerarquia = p_num_nivel_jerarquia,

        ind_naturaleza = p_ind_naturaleza_cuenta,

        ind_acepta_movimiento = p_ind_acepta_movimiento,

        des_cuenta = p_des_cuenta,

        ind_estado = p_ind_estado,

        usr_modificacion = IFNULL(p_usr_modificacion, 'sistema'),

        fec_modificacion = NOW()

    WHERE cod_cuenta = p_cod_cuenta;



    IF p_actualizar_tipo = TRUE THEN



        IF p_ind_naturaleza_tipo NOT IN ('deudora', 'acreedora') THEN

            SIGNAL SQLSTATE '45000'

            SET MESSAGE_TEXT = 'Error: naturaleza de tipo de cuenta no valida.';

        END IF;



        UPDATE cc_tipo_cuenta

        SET

            nom_tipo_cuenta = p_nom_tipo_cuenta,

            ind_naturaleza = p_ind_naturaleza_tipo,

            des_tipo_cuenta = p_des_tipo_cuenta,

            usr_modificacion = IFNULL(p_usr_modificacion, 'sistema'),

            fec_modificacion = NOW()

        WHERE cod_tipo_cuenta = p_cod_tipo_cuenta;

    END IF;



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cm_ins_modulo_mayorizacion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `cm_ins_modulo_mayorizacion`(

    IN p_cod_cuenta BIGINT,

    IN p_cod_periodo BIGINT,

    IN p_sal_inicial DECIMAL(14,2),

    IN p_tot_debe DECIMAL(14,2),

    IN p_tot_haber DECIMAL(14,2),

    IN p_ind_estado VARCHAR(20),

    OUT p_cod_saldo_generado BIGINT

)
BEGIN

    DECLARE v_sal_final DECIMAL(14,2);



    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        SET p_cod_saldo_generado = NULL;

    END;



    START TRANSACTION;



    IF NOT EXISTS (

        SELECT 1

        FROM cc_catalogo_cuenta

        WHERE cod_cuenta = p_cod_cuenta

    ) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: la cuenta contable no existe.';

    END IF;



    IF NOT EXISTS (

        SELECT 1

        FROM ga_periodo_contable

        WHERE cod_periodo = p_cod_periodo

    ) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el periodo contable no existe.';

    END IF;



    IF p_ind_estado NOT IN ('abierto', 'cerrado', 'recalculado') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de saldo no valido.';

    END IF;



    SET v_sal_final = IFNULL(p_sal_inicial, 0.00)

                    + IFNULL(p_tot_debe, 0.00)

                    - IFNULL(p_tot_haber, 0.00);



    INSERT INTO cm_saldo_cuenta_periodo (

        cod_cuenta,

        cod_periodo,

        sal_inicial,

        tot_debe,

        tot_haber,

        sal_final,

        ind_estado,

        fec_actualizacion

    ) VALUES (

        p_cod_cuenta,

        p_cod_periodo,

        IFNULL(p_sal_inicial, 0.00),

        IFNULL(p_tot_debe, 0.00),

        IFNULL(p_tot_haber, 0.00),

        v_sal_final,

        p_ind_estado,

        NOW()

    );



    SET p_cod_saldo_generado = LAST_INSERT_ID();



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cm_sel_modulo_mayorizacion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `cm_sel_modulo_mayorizacion`(

    IN p_cod_saldo BIGINT,

    IN p_cod_periodo BIGINT,

    IN p_cod_cuenta BIGINT,

    IN p_ind_estado VARCHAR(20)

)
BEGIN

    SELECT

        s.cod_saldo,

        s.cod_cuenta,

        c.cod_num_cuenta,

        c.nom_cuenta,

        s.cod_periodo,

        p.nom_periodo,

        s.sal_inicial,

        s.tot_debe,

        s.tot_haber,

        s.sal_final,

        s.ind_estado,

        s.fec_actualizacion

    FROM cm_saldo_cuenta_periodo s

    INNER JOIN cc_catalogo_cuenta c

        ON s.cod_cuenta = c.cod_cuenta

    INNER JOIN ga_periodo_contable p

        ON s.cod_periodo = p.cod_periodo

    WHERE (p_cod_saldo IS NULL OR s.cod_saldo = p_cod_saldo)

      AND (p_cod_periodo IS NULL OR s.cod_periodo = p_cod_periodo)

      AND (p_cod_cuenta IS NULL OR s.cod_cuenta = p_cod_cuenta)

      AND (p_ind_estado IS NULL OR s.ind_estado = p_ind_estado)

    ORDER BY s.cod_periodo, c.cod_cuenta;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `cm_upd_modulo_mayorizacion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `cm_upd_modulo_mayorizacion`(

    IN p_cod_saldo BIGINT,

    IN p_sal_inicial DECIMAL(14,2),

    IN p_tot_debe DECIMAL(14,2),

    IN p_tot_haber DECIMAL(14,2),

    IN p_ind_estado VARCHAR(20)

)
BEGIN

    DECLARE v_estado_actual VARCHAR(20);

    DECLARE v_sal_final DECIMAL(14,2);



    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

    END;



    START TRANSACTION;



    SELECT ind_estado

    INTO v_estado_actual

    FROM cm_saldo_cuenta_periodo

    WHERE cod_saldo = p_cod_saldo;



    IF v_estado_actual IS NULL THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el registro de saldo no existe.';

    END IF;



    IF v_estado_actual = 'cerrado' THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: no se pueden modificar saldos de un periodo cerrado.';

    END IF;



    IF p_ind_estado NOT IN ('abierto', 'cerrado', 'recalculado') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de saldo no valido.';

    END IF;



    SET v_sal_final = IFNULL(p_sal_inicial, 0.00)

                    + IFNULL(p_tot_debe, 0.00)

                    - IFNULL(p_tot_haber, 0.00);



    UPDATE cm_saldo_cuenta_periodo

    SET

        sal_inicial = IFNULL(p_sal_inicial, 0.00),

        tot_debe = IFNULL(p_tot_debe, 0.00),

        tot_haber = IFNULL(p_tot_haber, 0.00),

        sal_final = v_sal_final,

        ind_estado = p_ind_estado,

        fec_actualizacion = NOW()

    WHERE cod_saldo = p_cod_saldo;



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ga_ins_modulo_asientos` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `ga_ins_modulo_asientos`(

    IN p_num_asiento VARCHAR(50),

    IN p_cod_periodo BIGINT,

    IN p_cod_user BIGINT,

    IN p_fec_asiento DATE,

    IN p_des_asiento VARCHAR(255),

    IN p_tip_asiento VARCHAR(20),

    IN p_tot_debe DECIMAL(14,2),

    IN p_tot_haber DECIMAL(14,2),

    IN p_ind_estado VARCHAR(20),

    IN p_usr_adicion VARCHAR(100),

    IN p_detalle_json LONGTEXT,

    OUT p_cod_asiento_generado BIGINT

)
BEGIN

    DECLARE v_idx INT DEFAULT 0;

    DECLARE v_total_lineas INT DEFAULT 0;

    DECLARE v_cod_cuenta BIGINT;

    DECLARE v_num_linea INT;

    DECLARE v_des_linea VARCHAR(255);

    DECLARE v_mon_debe DECIMAL(14,2);

    DECLARE v_mon_haber DECIMAL(14,2);



    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        SET p_cod_asiento_generado = NULL;

    END;



    START TRANSACTION;



    IF NOT EXISTS (

        SELECT 1

        FROM ga_periodo_contable

        WHERE cod_periodo = p_cod_periodo

    ) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el periodo contable no existe.';

    END IF;



    IF p_tip_asiento NOT IN ('manual', 'ajuste', 'apertura', 'cierre', 'reversion') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: tipo de asiento no valido.';

    END IF;



    IF p_ind_estado NOT IN ('borrador', 'aprobado', 'anulado') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de asiento no valido.';

    END IF;



    IF IFNULL(p_tot_debe, 0.00) <> IFNULL(p_tot_haber, 0.00) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el total debe debe ser igual al total haber.';

    END IF;



    INSERT INTO ga_asiento_contable (

        num_asiento,

        cod_periodo,

        cod_user,

        fec_asiento,

        des_asiento,

        tip_asiento,

        tot_debe,

        tot_haber,

        ind_estado,

        usr_adicion,

        fec_adicion

    ) VALUES (

        p_num_asiento,

        p_cod_periodo,

        p_cod_user,

        p_fec_asiento,

        p_des_asiento,

        p_tip_asiento,

        IFNULL(p_tot_debe, 0.00),

        IFNULL(p_tot_haber, 0.00),

        p_ind_estado,

        IFNULL(p_usr_adicion, 'sistema'),

        NOW()

    );



    SET p_cod_asiento_generado = LAST_INSERT_ID();



    IF p_detalle_json IS NOT NULL THEN

        SET v_total_lineas = JSON_LENGTH(p_detalle_json);



        WHILE v_idx < v_total_lineas DO



            SET v_cod_cuenta = JSON_UNQUOTE(JSON_EXTRACT(p_detalle_json, CONCAT('$[', v_idx, '].cod_cuenta')));

            SET v_num_linea = JSON_UNQUOTE(JSON_EXTRACT(p_detalle_json, CONCAT('$[', v_idx, '].num_linea')));

            SET v_des_linea = JSON_UNQUOTE(JSON_EXTRACT(p_detalle_json, CONCAT('$[', v_idx, '].des_linea')));

            SET v_mon_debe = JSON_UNQUOTE(JSON_EXTRACT(p_detalle_json, CONCAT('$[', v_idx, '].mon_debe')));

            SET v_mon_haber = JSON_UNQUOTE(JSON_EXTRACT(p_detalle_json, CONCAT('$[', v_idx, '].mon_haber')));



            IF NOT EXISTS (

                SELECT 1

                FROM cc_catalogo_cuenta

                WHERE cod_cuenta = v_cod_cuenta

            ) THEN

                SIGNAL SQLSTATE '45000'

                SET MESSAGE_TEXT = 'Error: una de las cuentas del detalle no existe.';

            END IF;



            INSERT INTO ga_detalle_asiento (

                cod_asiento,

                cod_cuenta,

                num_linea,

                des_linea,

                mon_debe,

                mon_haber,

                usr_adicion,

                fec_adicion

            ) VALUES (

                p_cod_asiento_generado,

                v_cod_cuenta,

                v_num_linea,

                v_des_linea,

                IFNULL(v_mon_debe, 0.00),

                IFNULL(v_mon_haber, 0.00),

                IFNULL(p_usr_adicion, 'sistema'),

                NOW()

            );



            SET v_idx = v_idx + 1;

        END WHILE;

    END IF;



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ga_sel_modulo_asientos` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `ga_sel_modulo_asientos`(

    IN p_cod_asiento BIGINT,

    IN p_cod_periodo BIGINT,

    IN p_cod_user BIGINT,

    IN p_tip_asiento VARCHAR(20),

    IN p_ind_estado VARCHAR(20),

    IN p_incluir_detalle BOOLEAN

)
BEGIN

    SELECT

        a.cod_asiento,

        a.num_asiento,

        a.cod_periodo,

        p.nom_periodo,

        a.cod_user,

        a.fec_asiento,

        a.des_asiento,

        a.tip_asiento,

        a.tot_debe,

        a.tot_haber,

        a.ind_estado,

        a.usr_adicion,

        a.fec_adicion,

        CASE

            WHEN a.tot_debe = a.tot_haber THEN 'cuadrado'

            ELSE 'descuadrado'

        END AS estado_partida

    FROM ga_asiento_contable a

    INNER JOIN ga_periodo_contable p

        ON a.cod_periodo = p.cod_periodo

    WHERE (p_cod_asiento IS NULL OR a.cod_asiento = p_cod_asiento)

      AND (p_cod_periodo IS NULL OR a.cod_periodo = p_cod_periodo)

      AND (p_cod_user IS NULL OR a.cod_user = p_cod_user)

      AND (p_tip_asiento IS NULL OR a.tip_asiento = p_tip_asiento)

      AND (p_ind_estado IS NULL OR a.ind_estado = p_ind_estado)

    ORDER BY a.cod_asiento;



    IF p_incluir_detalle = TRUE AND p_cod_asiento IS NOT NULL THEN

        SELECT

            d.cod_detalle_asiento,

            d.cod_asiento,

            d.num_linea,

            d.cod_cuenta,

            c.cod_num_cuenta,

            c.nom_cuenta,

            d.des_linea,

            d.mon_debe,

            d.mon_haber,

            d.usr_adicion,

            d.fec_adicion

        FROM ga_detalle_asiento d

        INNER JOIN cc_catalogo_cuenta c

            ON d.cod_cuenta = c.cod_cuenta

        WHERE d.cod_asiento = p_cod_asiento

        ORDER BY d.num_linea;

    END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ga_upd_modulo_asientos` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `ga_upd_modulo_asientos`(

    IN p_cod_asiento BIGINT,

    IN p_num_asiento VARCHAR(50),

    IN p_cod_periodo BIGINT,

    IN p_cod_user BIGINT,

    IN p_fec_asiento DATE,

    IN p_des_asiento VARCHAR(255),

    IN p_tip_asiento VARCHAR(20),

    IN p_tot_debe DECIMAL(14,2),

    IN p_tot_haber DECIMAL(14,2),

    IN p_ind_estado VARCHAR(20)

)
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

    END;



    START TRANSACTION;



    IF NOT EXISTS (

        SELECT 1

        FROM ga_asiento_contable

        WHERE cod_asiento = p_cod_asiento

    ) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el asiento contable no existe.';

    END IF;



    IF NOT EXISTS (

        SELECT 1

        FROM ga_periodo_contable

        WHERE cod_periodo = p_cod_periodo

    ) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el periodo contable no existe.';

    END IF;



    IF p_tip_asiento NOT IN ('manual', 'ajuste', 'apertura', 'cierre', 'reversion') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: tipo de asiento no valido.';

    END IF;



    IF p_ind_estado NOT IN ('borrador', 'aprobado', 'anulado') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de asiento no valido.';

    END IF;



    IF IFNULL(p_tot_debe, 0.00) <> IFNULL(p_tot_haber, 0.00) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el total debe debe ser igual al total haber.';

    END IF;



    UPDATE ga_asiento_contable

    SET

        num_asiento = p_num_asiento,

        cod_periodo = p_cod_periodo,

        cod_user = p_cod_user,

        fec_asiento = p_fec_asiento,

        des_asiento = p_des_asiento,

        tip_asiento = p_tip_asiento,

        tot_debe = IFNULL(p_tot_debe, 0.00),

        tot_haber = IFNULL(p_tot_haber, 0.00),

        ind_estado = p_ind_estado

    WHERE cod_asiento = p_cod_asiento;



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pa_ins_modulo_personas` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `pa_ins_modulo_personas`(

    IN p_dni VARCHAR(255),

    IN p_firstname VARCHAR(255),

    IN p_middlename VARCHAR(255),

    IN p_lastname VARCHAR(255),

    IN p_sex VARCHAR(1),

    IN p_ind_civil VARCHAR(1),

    IN p_age TINYINT,

    IN p_tip_person VARCHAR(1),

    IN p_usr_add VARCHAR(255),

    OUT p_cod_people_generado BIGINT

)
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        SET p_cod_people_generado = NULL;

    END;



    START TRANSACTION;



    IF p_sex NOT IN ('M', 'W', 'F', 'D') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: sexo no valido.';

    END IF;



    IF p_ind_civil NOT IN ('S', 'M', 'W') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado civil no valido.';

    END IF;



    IF p_tip_person NOT IN ('N', 'J') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: tipo de persona no valido.';

    END IF;



    IF p_age < 0 THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: la edad no puede ser negativa.';

    END IF;



    INSERT INTO pa_people (

        dni,

        firstname,

        middlename,

        lastname,

        sex,

        ind_civil,

        age,

        tip_person,

        usr_add,

        dat_add,

        ind_people

    ) VALUES (

        p_dni,

        p_firstname,

        p_middlename,

        p_lastname,

        p_sex,

        p_ind_civil,

        p_age,

        p_tip_person,

        IFNULL(p_usr_add, 'sistema'),

        NOW(),

        'activo'

    );



    SET p_cod_people_generado = LAST_INSERT_ID();



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pa_sel_modulo_personas` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `pa_sel_modulo_personas`(

    IN p_cod_people BIGINT,

    IN p_dni VARCHAR(255),

    IN p_ind_people VARCHAR(20)

)
BEGIN

    SELECT

        cod_people,

        dni,

        firstname,

        middlename,

        lastname,

        sex,

        ind_civil,

        age,

        tip_person,

        usr_add,

        dat_add,

        ind_people

    FROM pa_people

    WHERE (p_cod_people IS NULL OR cod_people = p_cod_people)

      AND (p_dni IS NULL OR dni = p_dni)

      AND (p_ind_people IS NULL OR ind_people = p_ind_people)

    ORDER BY cod_people;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pa_upd_modulo_personas` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `pa_upd_modulo_personas`(

    IN p_cod_people BIGINT,

    IN p_dni VARCHAR(255),

    IN p_firstname VARCHAR(255),

    IN p_middlename VARCHAR(255),

    IN p_lastname VARCHAR(255),

    IN p_sex VARCHAR(1),

    IN p_ind_civil VARCHAR(1),

    IN p_age TINYINT,

    IN p_tip_person VARCHAR(1),

    IN p_ind_people VARCHAR(20)

)
BEGIN

    DECLARE v_existe BIGINT;



    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

    END;



    START TRANSACTION;



    SELECT COUNT(*)

    INTO v_existe

    FROM pa_people

    WHERE cod_people = p_cod_people;



    IF v_existe = 0 THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: la persona no existe.';

    END IF;



    IF p_sex NOT IN ('M', 'W', 'F', 'D') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: sexo no valido.';

    END IF;



    IF p_ind_civil NOT IN ('S', 'M', 'W') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado civil no valido.';

    END IF;



    IF p_tip_person NOT IN ('N', 'J') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: tipo de persona no valido.';

    END IF;



    IF p_ind_people NOT IN ('activo', 'inactivo') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de persona no valido.';

    END IF;



    IF p_age < 0 THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: la edad no puede ser negativa.';

    END IF;



    UPDATE pa_people

    SET

        dni = p_dni,

        firstname = p_firstname,

        middlename = p_middlename,

        lastname = p_lastname,

        sex = p_sex,

        ind_civil = p_ind_civil,

        age = p_age,

        tip_person = p_tip_person,

        ind_people = p_ind_people

    WHERE cod_people = p_cod_people;



    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `rf_ins_modulo_reportes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `rf_ins_modulo_reportes`(

    IN p_cod_periodo BIGINT,

    IN p_cod_user BIGINT,

    IN p_tip_reporte VARCHAR(30),

    IN p_calcular_automaticamente BOOLEAN,

    IN p_tot_activo DECIMAL(14,2),

    IN p_tot_pasivo DECIMAL(14,2),

    IN p_tot_patrimonio DECIMAL(14,2),

    IN p_mon_utilidad_perdida DECIMAL(14,2),

    OUT p_cod_reporte_generado BIGINT,

    OUT p_mensaje VARCHAR(255)

)
BEGIN

    DECLARE v_tot_activo DECIMAL(14,2) DEFAULT 0.00;

    DECLARE v_tot_pasivo DECIMAL(14,2) DEFAULT 0.00;

    DECLARE v_tot_patrimonio DECIMAL(14,2) DEFAULT 0.00;

    DECLARE v_tot_ingreso DECIMAL(14,2) DEFAULT 0.00;

    DECLARE v_tot_gasto DECIMAL(14,2) DEFAULT 0.00;

    DECLARE v_utilidad DECIMAL(14,2) DEFAULT 0.00;



    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        SET p_cod_reporte_generado = NULL;

        SET p_mensaje = 'Error: no se pudo insertar el reporte financiero.';

    END;



    START TRANSACTION;



    IF NOT EXISTS (

        SELECT 1

        FROM ga_periodo_contable

        WHERE cod_periodo = p_cod_periodo

    ) THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el periodo contable no existe.';

    END IF;



    IF p_tip_reporte NOT IN ('balance_general', 'estado_resultados') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: tipo de reporte no valido.';

    END IF;



    IF p_calcular_automaticamente = TRUE THEN



        SELECT

            COALESCE(SUM(CASE WHEN tc.nom_tipo_cuenta = 'activo' THEN s.sal_final ELSE 0 END), 0),

            COALESCE(SUM(CASE WHEN tc.nom_tipo_cuenta = 'pasivo' THEN s.sal_final ELSE 0 END), 0),

            COALESCE(SUM(CASE WHEN tc.nom_tipo_cuenta = 'patrimonio' THEN s.sal_final ELSE 0 END), 0),

            COALESCE(SUM(CASE WHEN tc.nom_tipo_cuenta = 'ingreso' THEN s.sal_final ELSE 0 END), 0),

            COALESCE(SUM(CASE WHEN tc.nom_tipo_cuenta = 'gasto' THEN s.sal_final ELSE 0 END), 0)

        INTO

            v_tot_activo,

            v_tot_pasivo,

            v_tot_patrimonio,

            v_tot_ingreso,

            v_tot_gasto

        FROM cm_saldo_cuenta_periodo s

        INNER JOIN cc_catalogo_cuenta c

            ON s.cod_cuenta = c.cod_cuenta

        INNER JOIN cc_tipo_cuenta tc

            ON c.cod_tipo_cuenta = tc.cod_tipo_cuenta

        WHERE s.cod_periodo = p_cod_periodo

          AND c.ind_acepta_movimiento = 'si';



        SET v_utilidad = v_tot_ingreso - v_tot_gasto;



        IF p_tip_reporte = 'balance_general' THEN

            SET v_tot_patrimonio = v_tot_patrimonio + v_utilidad;

        END IF;



    ELSE

        SET v_tot_activo = IFNULL(p_tot_activo, 0.00);

        SET v_tot_pasivo = IFNULL(p_tot_pasivo, 0.00);

        SET v_tot_patrimonio = IFNULL(p_tot_patrimonio, 0.00);

        SET v_utilidad = IFNULL(p_mon_utilidad_perdida, 0.00);

    END IF;



    INSERT INTO rf_reporte_financiero (

        cod_periodo,

        cod_user,

        tip_reporte,

        fec_generacion,

        tot_activo,

        tot_pasivo,

        tot_patrimonio,

        mon_utilidad_perdida,

        ind_estado

    ) VALUES (

        p_cod_periodo,

        p_cod_user,

        p_tip_reporte,

        NOW(),

        v_tot_activo,

        v_tot_pasivo,

        v_tot_patrimonio,

        v_utilidad,

        'generado'

    );



    SET p_cod_reporte_generado = LAST_INSERT_ID();



    IF p_tip_reporte = 'balance_general' THEN



        INSERT INTO rf_detalle_reporte_financiero (

            cod_reporte,

            cod_cuenta,

            tip_grupo,

            nom_linea,

            mon_linea,

            num_orden,

            num_nivel_jerarquia

        )

        SELECT

            p_cod_reporte_generado,

            c.cod_cuenta,

            tc.nom_tipo_cuenta,

            c.nom_cuenta,

            s.sal_final,

            c.cod_cuenta,

            c.num_nivel_jerarquia

        FROM cm_saldo_cuenta_periodo s

        INNER JOIN cc_catalogo_cuenta c

            ON s.cod_cuenta = c.cod_cuenta

        INNER JOIN cc_tipo_cuenta tc

            ON c.cod_tipo_cuenta = tc.cod_tipo_cuenta

        WHERE s.cod_periodo = p_cod_periodo

          AND c.ind_acepta_movimiento = 'si'

          AND tc.nom_tipo_cuenta IN ('activo', 'pasivo', 'patrimonio')

          AND s.sal_final <> 0;



        INSERT INTO rf_detalle_reporte_financiero (

            cod_reporte,

            cod_cuenta,

            tip_grupo,

            nom_linea,

            mon_linea,

            num_orden,

            num_nivel_jerarquia

        ) VALUES (

            p_cod_reporte_generado,

            NULL,

            'resultado',

            IF(v_utilidad >= 0, 'utilidad del periodo', 'perdida del periodo'),

            ABS(v_utilidad),

            999,

            1

        );



    ELSEIF p_tip_reporte = 'estado_resultados' THEN



        INSERT INTO rf_detalle_reporte_financiero (

            cod_reporte,

            cod_cuenta,

            tip_grupo,

            nom_linea,

            mon_linea,

            num_orden,

            num_nivel_jerarquia

        )

        SELECT

            p_cod_reporte_generado,

            c.cod_cuenta,

            tc.nom_tipo_cuenta,

            c.nom_cuenta,

            s.sal_final,

            c.cod_cuenta,

            c.num_nivel_jerarquia

        FROM cm_saldo_cuenta_periodo s

        INNER JOIN cc_catalogo_cuenta c

            ON s.cod_cuenta = c.cod_cuenta

        INNER JOIN cc_tipo_cuenta tc

            ON c.cod_tipo_cuenta = tc.cod_tipo_cuenta

        WHERE s.cod_periodo = p_cod_periodo

          AND c.ind_acepta_movimiento = 'si'

          AND tc.nom_tipo_cuenta IN ('ingreso', 'gasto')

          AND s.sal_final <> 0;



        INSERT INTO rf_detalle_reporte_financiero (

            cod_reporte,

            cod_cuenta,

            tip_grupo,

            nom_linea,

            mon_linea,

            num_orden,

            num_nivel_jerarquia

        ) VALUES (

            p_cod_reporte_generado,

            NULL,

            'resultado',

            IF(v_utilidad >= 0, 'utilidad neta del periodo', 'perdida neta del periodo'),

            ABS(v_utilidad),

            999,

            1

        );



    END IF;



    COMMIT;



    SET p_mensaje = 'Reporte financiero insertado correctamente.';

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `rf_sel_modulo_reportes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `rf_sel_modulo_reportes`(

    IN p_cod_reporte BIGINT,

    IN p_cod_periodo BIGINT,

    IN p_tip_reporte VARCHAR(30),

    IN p_ind_estado VARCHAR(20),

    IN p_cod_user BIGINT,

    IN p_incluir_detalle BOOLEAN

)
BEGIN

    SELECT

        r.cod_reporte,

        r.cod_periodo,

        p.nom_periodo,

        r.cod_user,

        u.NAME AS nom_usuario,

        r.tip_reporte,

        r.fec_generacion,

        r.tot_activo,

        r.tot_pasivo,

        r.tot_patrimonio,

        r.mon_utilidad_perdida,

        r.ind_estado,

        CASE

            WHEN r.tip_reporte = 'balance_general'

                 AND r.tot_activo = (r.tot_pasivo + r.tot_patrimonio)

            THEN 'balance cuadrado'

            WHEN r.tip_reporte = 'balance_general'

                 AND r.tot_activo <> (r.tot_pasivo + r.tot_patrimonio)

            THEN 'balance descuadrado'

            WHEN r.tip_reporte = 'estado_resultados'

                 AND r.mon_utilidad_perdida >= 0

            THEN 'utilidad'

            WHEN r.tip_reporte = 'estado_resultados'

                 AND r.mon_utilidad_perdida < 0

            THEN 'perdida'

            ELSE 'sin validacion'

        END AS estado_validacion

    FROM rf_reporte_financiero r

    INNER JOIN ga_periodo_contable p

        ON r.cod_periodo = p.cod_periodo

    LEFT JOIN users u

        ON r.cod_user = u.COD_USER

    WHERE (p_cod_reporte IS NULL OR r.cod_reporte = p_cod_reporte)

      AND (p_cod_periodo IS NULL OR r.cod_periodo = p_cod_periodo)

      AND (p_tip_reporte IS NULL OR r.tip_reporte = p_tip_reporte)

      AND (p_ind_estado IS NULL OR r.ind_estado = p_ind_estado)

      AND (p_cod_user IS NULL OR r.cod_user = p_cod_user)

    ORDER BY r.cod_reporte DESC;



    IF p_incluir_detalle = TRUE AND p_cod_reporte IS NOT NULL THEN

        SELECT

            d.cod_detalle_reporte,

            d.cod_reporte,

            d.cod_cuenta,

            c.cod_num_cuenta,

            c.nom_cuenta AS nom_cuenta_original,

            d.tip_grupo,

            d.nom_linea,

            d.mon_linea,

            d.num_orden,

            d.num_nivel_jerarquia

        FROM rf_detalle_reporte_financiero d

        LEFT JOIN cc_catalogo_cuenta c

            ON d.cod_cuenta = c.cod_cuenta

        WHERE d.cod_reporte = p_cod_reporte

        ORDER BY d.num_orden;

    END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `rf_upd_modulo_reportes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `rf_upd_modulo_reportes`(

    IN p_cod_reporte BIGINT,

    IN p_ind_estado VARCHAR(20),

    IN p_tot_activo DECIMAL(14,2),

    IN p_tot_pasivo DECIMAL(14,2),

    IN p_tot_patrimonio DECIMAL(14,2),

    IN p_mon_utilidad_perdida DECIMAL(14,2),

    OUT p_mensaje VARCHAR(255)

)
BEGIN

    DECLARE v_estado_actual VARCHAR(20);



    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        SET p_mensaje = 'Error: no se pudo actualizar el reporte financiero.';

    END;



    START TRANSACTION;



    SELECT ind_estado

    INTO v_estado_actual

    FROM rf_reporte_financiero

    WHERE cod_reporte = p_cod_reporte;



    IF v_estado_actual IS NULL THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: el reporte financiero no existe.';

    END IF;



    IF v_estado_actual = 'anulado' THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: no se puede actualizar un reporte anulado.';

    END IF;



    IF p_ind_estado IS NOT NULL

       AND p_ind_estado NOT IN ('generado', 'confirmado', 'anulado') THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Error: estado de reporte no valido.';

    END IF;



    UPDATE rf_reporte_financiero

    SET

        ind_estado = IFNULL(p_ind_estado, ind_estado),

        tot_activo = IFNULL(p_tot_activo, tot_activo),

        tot_pasivo = IFNULL(p_tot_pasivo, tot_pasivo),

        tot_patrimonio = IFNULL(p_tot_patrimonio, tot_patrimonio),

        mon_utilidad_perdida = IFNULL(p_mon_utilidad_perdida, mon_utilidad_perdida)

    WHERE cod_reporte = p_cod_reporte;



    COMMIT;



    SET p_mensaje = 'Reporte financiero actualizado correctamente.';

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-20 15:54:47
