/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50733
 Source Host           : localhost:3306
 Source Schema         : bodega_juan_xxiii

 Target Server Type    : MySQL
 Target Server Version : 50733
 File Encoding         : 65001

 Date: 21/02/2026 10:59:58
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for configuracion
-- ----------------------------
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tienda` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `google_maps_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `whatsapp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `horarios` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `redes_sociales` json NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of configuracion
-- ----------------------------
INSERT INTO `configuracion` VALUES (1, 'Bodega Juan XXIII', 'Las 40 Calle 9 frente AGEL', 'https://maps.google.com/?q=Las+40+Calle+9+frente+AGEL', '0412-1614868', 'felixlares@gmail.com', '24 horas', '{\"tiktok\": \"\", \"facebook\": \"\", \"instagram\": \"\"}');

-- ----------------------------
-- Table structure for productos
-- ----------------------------
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `precio` decimal(10, 2) NOT NULL,
  `precio_anterior` decimal(10, 2) NULL DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `categoria` enum('ropa','accesorios','hogar','electricidad','ferreteria','viveres') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen_principal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `galeria` json NULL,
  `video_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `stock` int(11) NULL DEFAULT 0,
  `activo` tinyint(1) NULL DEFAULT 1,
  `destacado` tinyint(1) NULL DEFAULT 0,
  `tags` json NULL,
  `peso` decimal(10, 2) NULL DEFAULT NULL,
  `dimensiones_alto` decimal(10, 2) NULL DEFAULT NULL,
  `dimensiones_ancho` decimal(10, 2) NULL DEFAULT NULL,
  `dimensiones_profundidad` decimal(10, 2) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `slug`(`slug`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of productos
-- ----------------------------
INSERT INTO `productos` VALUES (1, 'Cerradura Pomo Libaby', 'cerradura-pomo-libaby', 9.50, 15.50, 'Cerradura de pomo para puerta entamborada', 'ferreteria', './assets/images/productos/cerradura-pomo-libaby.jpeg', NULL, NULL, '000001', 10, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-02-20 22:04:14', '2026-02-20 22:19:18');
INSERT INTO `productos` VALUES (2, 'Cerradura Pomo Firebird', 'cerradura-pomo-firebird', 8.84, 14.00, 'Cerradura de pomo para puerta entamborada b', 'ferreteria', './assets/images/productos/cerradura-pomo-firebird.jpeg', NULL, NULL, '000002', 10, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-02-20 22:18:03', '2026-02-20 22:19:22');
INSERT INTO `productos` VALUES (3, 'Cinta de embalar util', 'cinta-de-embalar-util', 1.83, 2.30, 'Cinta de embalar util todo uso', 'ferreteria', './assets/images/productos/cinta-de-embalar-util.jpeg', NULL, NULL, '000003', 10, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-02-21 08:44:56', '2026-02-21 08:44:56');
INSERT INTO `productos` VALUES (4, 'Cuchillos con cacha de madera N#4', 'cuchillos-con-cacha-de-madera-n-4', 8.84, 10.50, 'Cuchillos con cacha de madera N#4.\r\nLa caja contiene 12 Unidades.\r\nEspeciales para el hogar muy filosos.', 'hogar', './assets/images/productos/cuchillos-cacha-madera-caja.jpeg', NULL, NULL, '000004', 2, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-02-21 10:39:23', '2026-02-21 10:41:55');

-- ----------------------------
-- Table structure for usuarios_admin
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_admin`;
CREATE TABLE `usuarios_admin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios_admin
-- ----------------------------
INSERT INTO `usuarios_admin` VALUES (1, 'admin', '$2y$10$Kkjx6aHensnQETPGa9wYg.DeG92ISykHrAQa29rbDuYkD9987lEUS', '2026-02-20 21:16:41');

SET FOREIGN_KEY_CHECKS = 1;
