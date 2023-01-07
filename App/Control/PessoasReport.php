<?php

use Livro\Control\Page;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Widgets\Container\Panel;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Relatório de vendas
 */
class PessoasReport extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $loader = new Twig_Loader_Filesystem('App/Resources');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('pessoas_report.html');

        // vetor de parâmetros para o template
        $replaces = array();

        try {
            // inicia transação com o banco 'livro'
            Transaction::open('livro');
            $replaces['pessoas'] = ViewSaldoPessoa::all();
            Transaction::close(); // finaliza a transação
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }

        $content = $template->render($replaces);

        //gerar pdf
        $options = new Options;
        $options->set('dpi', 128); // exemplo do que pode definir, dpi é pontos por polegadas 

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($content); // converte o html $content em pdf
        $dompdf->setPaper('A4', 'portrait'); // formato do pdf
        $dompdf->render(); // gera o pdf em memoria

        $filename = 'tmp/pessoas.pdf';

        if (is_writable('tmp')) {
            file_put_contents($filename, $dompdf->output()); //escreve no no path, gera o pdf
            echo "<script> window.open('{$filename}'); </script>"; // abre o arq via js
        } else {
            new Message('error', 'Permissão negada em: ' . $filename);
        }

        // cria um painél para conter o formulário
        $panel = new Panel('Pessoas');
        $panel->add($content);

        parent::add($panel);
    }
}
