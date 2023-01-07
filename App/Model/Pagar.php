<?php
use Livro\Database\Transaction;
use Livro\Database\Record;

class Pagar extends Record
{
    const TABLENAME = 'pagar';

    public static function getVendasMes()
    {
        $meses = array();
        $meses[1] = 'Janeiro';
        $meses[2] = 'Fevereiro';
        $meses[3] = 'Março';
        $meses[4] = 'Abril';
        $meses[5] = 'Maio';
        $meses[6] = 'Junho';
        $meses[7] = 'Julho';
        $meses[8] = 'Agosto';
        $meses[9] = 'Setembro';
        $meses[10] = 'Outubro';
        $meses[11] = 'Novembro';
        $meses[12] = 'Dezembro';
        
        $conn = Transaction::get();
        $result = $conn->query("select strftime('%m', vencimento) as mes, sum(valor) as valor from pagar group by 1"); 
        //alterar a query p pegar vencimento
        
        $dataset = [];
        foreach ($result as $row)
        {
            $mes = $meses[ (int) $row['mes'] ];
            $dataset[ $mes ] = $row['valor'];
        }
        
        return $dataset;
    }
}
