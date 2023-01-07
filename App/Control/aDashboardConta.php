<?php
use Livro\Control\Page;
use Livro\Widgets\Container\HBox;

/**
 * Vendas por mês
 */
class aDashboardConta extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();
        
        $hbox = new HBox;
        $hbox->add( new MesChart('Pagar') )->style.=';width:48%;';
        $hbox->add( new MesChart('Conta') )->style.=';width:48%;';
        
        parent::add($hbox);
    }
}
