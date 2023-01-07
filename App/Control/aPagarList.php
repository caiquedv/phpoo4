<?php

use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Container\VBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;

use Livro\Traits\DeleteTrait;
use Livro\Traits\ReloadTrait;

use Livro\Widgets\Wrapper\DatagridWrapper;
use Livro\Widgets\Wrapper\FormWrapper;

/**
 * Página de produtos
 */
class aPagarList extends Page
{
    private $form;
    private $datagrid;
    private $loaded;
    private $connection;
    private $activeRecord;
    private $filters;

    use DeleteTrait;
    use ReloadTrait {
        onReload as onReloadTrait;
    }

    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        // Define o Active Record
        $this->activeRecord = 'Pagar';
        $this->connection   = 'livro';

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_busca_pagar'));
        $this->form->setTitle('Pagar');

        // cria os campos do formulário
        $empresa = new Entry('empresa');

        $this->form->addField('Empresa',   $empresa, '100%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Cadastrar', new Action(array(new aPagarForm, 'onEdit')));

        // instancia objeto Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $emissao   = new DatagridColumn('emissao', 'Emissão', 'left', '25%');
        $vencimento  = new DatagridColumn('vencimento', 'Vencimento', 'left',   '25%');
        $empresa  = new DatagridColumn('empresa', 'Empresa.', 'left', '25%');
        $valor  = new DatagridColumn('valor', 'Valor.', 'left', '25%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($emissao);
        $this->datagrid->addColumn($vencimento);
        $this->datagrid->addColumn($empresa);
        $this->datagrid->addColumn($valor);

        // $this->datagrid->addAction('PDF',  new Action([new aOrcamentoReport, 'onGera']), 'id', 'fa fa-print fa-lg blue');
        $this->datagrid->addAction('PDF',  new Action([new aPagarForm, 'onEdit']), 'id', 'fa fa-edit fa-lg blue');
        $this->datagrid->addAction('Excluir', new Action([$this, 'onDelete']),          'id', 'fa fa-trash fa-lg red');
        // monta a página através de uma caixa
        $box = new VBox;
        $box->style = 'display:block';
        $box->add($this->form);
        $box->add($this->datagrid);

        parent::add($box);
    }

    public function onReload()
    {
        // obtém os dados do formulário de buscas
        $dados = $this->form->getData();

        // verifica se o usuário preencheu o formulário
        if ($dados->empresa) {
            // filtra pela descrição do produto
            $this->filters[] = ['empresa', 'like', "%{$dados->empresa}%", 'and'];
        }

        $this->onReloadTrait();   // usa o trait
        $this->loaded = true;
    }

    /**
     * Exibe a página
     */
    public function show()
    {
        // se a listagem ainda não foi carregada
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show();
    }
}
