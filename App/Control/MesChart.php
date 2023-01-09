<?php

use Livro\Control\Page;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Widgets\Container\Panel;

/**
 * Vendas por mês
 */
class MesChart extends Page
{
    /**
     * método construtor
     */
    public function __construct($class = '')
    {
        if (isset($_GET['rec'])) {
            $class = $_GET['rec'];
        }

        parent::__construct();

        $loader = new Twig_Loader_Filesystem('App/Resources');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate($class == 'Pagar' ? 'pagar_mes.html' : 'vendas_mes.html');

        try {
            // inicia transação com o banco 'livro'
            Transaction::open('livro');
            $vendas = $class::getVendasMes();
            Transaction::close(); // finaliza a transação
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }

        // vetor de parâmetros para o template
        $replaces = array();
        $replaces['title'] = "{$class} por mês";
        $replaces['labels'] = json_encode(array_keys($vendas));
        $replaces['data']  = json_encode(array_values($vendas));

        $content = $template->render($replaces);

        // cria um painél para conter o formulário
        $panel = new Panel("$class/mês");
        $panel->add($content);

        parent::add($panel);
    }
}
