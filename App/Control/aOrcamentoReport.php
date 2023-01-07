<?php
use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Date;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Database\Repository;
use Livro\Database\Criteria;

use Livro\Widgets\Wrapper\FormWrapper;
use Livro\Widgets\Container\Panel;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Relatório de contas
 */
class aOrcamentoReport extends Page
{
    private $form;   // formulário de entrada

    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Gera o relatório, baseado nos parâmetros do formulário
     */
    public function onGera()
    {
        $loader = new Twig_Loader_Filesystem('App/Resources');
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('orcamentos_report.html');

        $replaces = array();

        $dados = $_GET;

        try
        {
            // inicia transação com o banco 'livro'
            Transaction::open('livro');

            // instancia um repositório da classe Conta
            $repositorio = new Repository('Orcamento');

            // cria um critério de seleção por intervalo de datas
            $criterio = new Criteria;
            $criterio->setProperty('order', 'id');
            
            $criterio->add('id', '=', $dados['id']);
            
            // // lê todas contas que satisfazem ao critério
            $orcamento = $repositorio->load($criterio);
            // print_r($orcamento[0]->id);
            
            if ($orcamento)
            {
               $replaces['id'] = $orcamento[0]->id;
               $replaces['nome'] = $orcamento[0]->nome;
               $replaces['descricao'] = $orcamento[0]->descricao;
            }
            // finaliza a transação
            Transaction::close();
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
        $content = $template->render($replaces);
        $panel = new Panel('Orçamento');
        $panel->add($content);
        
        parent::add($panel);
        //gera pdf
        $html = $content;
        
        $options = new Options();
        $options->set('dpi', '128');

        // DomPDF converte o HTML para PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Escreve o arquivo e abre em tela
        $filename = 'tmp/orcamento.pdf';
        if (is_writable('tmp'))
        {
            file_put_contents($filename, $dompdf->output());
            echo "<script>window.open('{$filename}');</script>";
        }
        else
        {
            new Message('error', 'Permissão negada em: ' . $filename);
        }
    }
}
