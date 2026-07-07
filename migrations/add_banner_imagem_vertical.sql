-- Migration: adiciona coluna imagem_vertical_url na tabela banners
-- Execute uma única vez no banco de produção

ALTER TABLE banners
  ADD COLUMN IF NOT EXISTS imagem_vertical_url VARCHAR(500) NULL DEFAULT NULL
  AFTER imagem_url;
