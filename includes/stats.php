<?php
/**
 * Registra ou incrementa visualizações/cliques na tabela stats_diario.
 *
 * @param PDO    $pdo
 * @param string $tipo          'banner' ou 'cliente'
 * @param int    $referencia_id ID do banner ou cliente
 * @param string $campo         'visualizacoes' ou 'cliques'
 */
function registrarStat(PDO $pdo, string $tipo, int $referencia_id, string $campo): void
{
    $hoje = date('Y-m-d');
    $sql = "INSERT INTO stats_diario (tipo, referencia_id, data, {$campo})
            VALUES (:tipo, :id, :data, 1)
            ON DUPLICATE KEY UPDATE {$campo} = {$campo} + 1";
    $pdo->prepare($sql)->execute([':tipo' => $tipo, ':id' => $referencia_id, ':data' => $hoje]);
}
