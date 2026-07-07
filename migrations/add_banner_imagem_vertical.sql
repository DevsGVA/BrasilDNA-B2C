-- Migration: adiciona coluna imagem_vertical_url na tabela banners
-- Compatível com MySQL 5.7+ e MariaDB
-- Execute uma única vez no banco de produção

SET @dbname = DATABASE();
SET @colname = 'imagem_vertical_url';
SET @tblname = 'banners';

SET @query = IF(
  NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME   = @tblname
      AND COLUMN_NAME  = @colname
  ),
  CONCAT(
    'ALTER TABLE `', @tblname, '`',
    ' ADD COLUMN `imagem_vertical_url` VARCHAR(500) NULL DEFAULT NULL',
    ' AFTER `imagem_url`'
  ),
  'SELECT ''Coluna já existe, nenhuma alteração feita.'''
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
