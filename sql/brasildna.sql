-- ============================================================
--  Brasil DNA — Estrutura do Banco de Dados
--  Banco: brasildna | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `brasildna`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `brasildna`;

-- ------------------------------------------------------------
--  Tabela: admins
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`                      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `nome`                    VARCHAR(120)     NOT NULL,
  `email`                   VARCHAR(180)     NOT NULL,
  `senha`                   VARCHAR(255)     NOT NULL,
  `tipo`                    ENUM('admin','super_admin') NOT NULL DEFAULT 'admin',
  `reset_token`             VARCHAR(64)          NULL DEFAULT NULL,
  `reset_token_expires_at`  DATETIME             NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email`       (`email`),
  UNIQUE KEY `uq_admins_reset_token` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Para bancos já existentes:
-- ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `tipo` ENUM('admin','super_admin') NOT NULL DEFAULT 'admin';
-- ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(64) NULL DEFAULT NULL;
-- ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `reset_token_expires_at` DATETIME NULL DEFAULT NULL;
-- ALTER TABLE `admins` ADD UNIQUE KEY `uq_admins_reset_token` (`reset_token`);

-- ------------------------------------------------------------
--  Tabela: banners
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `banners` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `parceiro_id`   INT UNSIGNED         NULL,
  `nome_parceiro` VARCHAR(150)     NOT NULL,
  `logo_url`      VARCHAR(500)         NULL,
  `imagem_url`    VARCHAR(500)         NULL,
  `titulo`        VARCHAR(200)         NULL,
  `subtexto`      VARCHAR(300)         NULL,
  `botao_texto`   VARCHAR(80)      NOT NULL DEFAULT 'Saiba mais',
  `link_url`      VARCHAR(500)         NULL,
  `ativo`         TINYINT(1)       NOT NULL DEFAULT 0,
  `ordem`         INT              NOT NULL DEFAULT 0,
  `visualizacoes` INT UNSIGNED     NOT NULL DEFAULT 0,
  `cliques`       INT UNSIGNED     NOT NULL DEFAULT 0,
  `criado_em`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_banners_parceiro` (`parceiro_id`),
  KEY `idx_banners_ativo`    (`ativo`),
  CONSTRAINT `fk_banners_parceiro`
    FOREIGN KEY (`parceiro_id`) REFERENCES `parceiros` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabela: posts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
  `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `titulo`           VARCHAR(250)     NOT NULL,
  `slug`             VARCHAR(250)         NULL,
  `resumo`           TEXT                 NULL,
  `conteudo`         LONGTEXT             NULL,
  `regiao`           ENUM('Norte','Nordeste','Centro-Oeste','Sudeste','Sul') NULL,
  `status`           ENUM('rascunho','publicado') NOT NULL DEFAULT 'rascunho',
  `data_publicacao`  DATE                 NULL,
  `imagem`           VARCHAR(500)         NULL,
  `criado_em`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_posts_slug` (`slug`),
  KEY `idx_posts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Para bancos já existentes:
-- ALTER TABLE `posts` ADD COLUMN `slug` VARCHAR(250) NULL AFTER `titulo`, ADD UNIQUE KEY `uniq_posts_slug` (`slug`);

-- ------------------------------------------------------------
--  Tabela: clientes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `titulo`      VARCHAR(200)  NOT NULL,
  `tipo`        ENUM('destino','parceiro') NOT NULL DEFAULT 'destino',
  `regiao`      VARCHAR(100)      NULL,
  `logo`        VARCHAR(500)      NULL,
  `descricao`   TEXT              NULL,
  `iframe`      TEXT              NULL,
  `imagem_fundo` VARCHAR(500)     NULL,
  `facebook`    VARCHAR(500)      NULL,
  `instagram`   VARCHAR(500)      NULL,
  `linkedin`    VARCHAR(500)      NULL,
  `site`        VARCHAR(500)      NULL,
  `youtube`     VARCHAR(500)      NULL,
  `link_guia`   VARCHAR(500)      NULL,
  `criado_em`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Para bancos já existentes (adicionar tipo e regiao):
-- ALTER TABLE `clientes` ADD COLUMN `tipo` ENUM('destino','parceiro') NOT NULL DEFAULT 'destino' AFTER `titulo`, ADD COLUMN `regiao` VARCHAR(100) NULL AFTER `tipo`;

-- Para bancos já existentes (renomear video → imagem_fundo):
-- ALTER TABLE `clientes` CHANGE `video` `imagem_fundo` VARCHAR(500) NULL;

-- ------------------------------------------------------------
--  Tabela: stats_diario  (analytics diário de banners, clientes e posts)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stats_diario` (
  `tipo`          ENUM('banner','cliente','post') NOT NULL,
  `referencia_id` INT UNSIGNED             NOT NULL,
  `data`          DATE                     NOT NULL,
  `visualizacoes` INT UNSIGNED             NOT NULL DEFAULT 0,
  `cliques`       INT UNSIGNED             NOT NULL DEFAULT 0,
  PRIMARY KEY (`tipo`, `referencia_id`, `data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Para bancos já existentes:
-- ALTER TABLE `stats_diario` MODIFY COLUMN `tipo` ENUM('banner','cliente','post') NOT NULL;