<?php
use Livro\Database\Record;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Database\Transaction;

class Conta extends Record
{
    const TABLENAME = 'conta';
	private $cliente;
	
    public function get_cliente()
    {
        if (empty($this->cliente))
        {
            $this->cliente = new Pessoa($this->id_cliente);
        }
        
        // Retorna o objeto instanciado
        return $this->cliente;
    }
    
	public static function getByPessoa($id_pessoa)
	{
	    $criteria = new Criteria;
	    $criteria->add('paga', '<>', 'S');
	    $criteria->add('id_cliente', '=', $id_pessoa);
	    
	    $repo = new Repository('Conta');
	    return $repo->load($criteria);
	}
	
	public static function debitosPorPessoa($id_pessoa)
	{
	    $total = 0;
	    $contas = self::getByPessoa($id_pessoa);
	    if ($contas)
	    {
	        foreach ($contas as $conta)
	        {
	            $total += $conta->valor;
	        }
	    }
	    return $total;
	}
	
	public static function geraParcelas($id_cliente, $delay, $valor, $parcelas)
	{	// o quanto é hoje mais tantos dias (delay)
	    $date = new DateTime(date('Y-m-d')); // pega a data atual
	    $date->add(new DateInterval('P'.$delay.'D')); // soma (p) o delay em dias (D)
	    
	    for ($n=1; $n<=$parcelas; $n++)
	    {
	        $conta = new self;
	        $conta->id_cliente = $id_cliente;
	        $conta->dt_emissao = date('Y-m-d');
	        $conta->dt_vencimento = $date->format('Y-m-d');
	        $conta->valor = $valor / $parcelas;
	        $conta->paga = 'N';
	        $conta->store();
	        
	        $date->add(new DateInterval('P1M')); // adiciona um mes (PLUS1MONTH)
	    }
	}

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
        $result = $conn->query("select strftime('%m', dt_vencimento) as mes, sum(valor) as valor from conta group by 1"); 
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
