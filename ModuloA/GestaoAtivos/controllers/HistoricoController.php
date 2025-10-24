<?php
class HistoricoController {
    public function listarHistorico($ativo_id) {
        // Exemplo estático, substitua por consulta ao banco de dados conforme necessário
        if ($ativo_id == 1) {
            return [
                [
                    'data' => '10/06/2024',
                    'tipo' => 'Corretiva',
                    'custo' => 500,
                    'tecnico' => 'João',
                    'descricao' => 'Troca de peça'
                ],
                [
                    'data' => '05/06/2024',
                    'tipo' => 'Preventiva',
                    'custo' => 200,
                    'tecnico' => 'Maria',
                    'descricao' => 'Lubrificação'
                ]
            ];
        } elseif ($ativo_id == 2) {
            return [
                [
                    'data' => '08/06/2024',
                    'tipo' => 'Corretiva',
                    'custo' => 300,
                    'tecnico' => 'Carlos',
                    'descricao' => 'Reparo elétrico'
                ]
            ];
        }
        return [];
    }
}
