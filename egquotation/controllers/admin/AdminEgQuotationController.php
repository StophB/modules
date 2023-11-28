<?php

class AdminEgQuotationController extends ModuleAdminController
{
    protected $position_identifier = 'id_eg_quotation';
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'eg_quotation';
        $this->className = 'EgQuotationClass';
        $this->_defaultOrderBy = 'position';
        $this->identifier = 'id_eg_quotation';
        $this->toolbar_btn = null;
        $this->_defaultOrderWay = 'ASC';
        $this->list_no_link = true;

        $this->lang = false;

        $this->addRowAction('show');
        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        parent::__construct();

        $this->bulk_actions = array(

            'delete' => array(
                'text' => $this->trans('Delete selected', array(), 'Modules.EgQuotation.Admin'),
                'confirm' => $this->trans('Delete selected items?', array(), 'Modules.Egquotation.Admin'),
                'image' => 'image-trash'
            ),
        );

        $this->fields_list = array(
            'id_eg_quotation' => array(
                'title' => $this->trans('Id', array(), 'Modules.Egquotation.Admin'),
                'align' => 'center',
            ),
            'id_product' => array(
                'title' => $this->trans('Product', array(), 'Modules.Egquotation.Admin'),
                'filter_key' => 'b!title',
                'align' => 'center',
                'callback' => 'getProductClean'
            ),
            'id_customer' => array(
                'title' => $this->trans('Customer Name', array(), 'Modules.Egquotation.Admin'),
                'filter_key' => 'b!sub_title',
                'align' => 'center',
                'callback' => 'getClientClean'
            ),
            'id_customer' => array(
                'title' => $this->trans('Email Customer', array(), 'Modules.Egquotation.Admin'),
                'align' => 'center',
                'callback' => 'getEmailClean'
            ),
            'session' => array(
                'title' => $this->trans('Session', array(), 'Modules.Egquotation.Admin'),
                'align' => 'center',
                'callback' => 'getDescriptionClean'
            ),
            'active' => array(
                'title' => $this->trans('Displayed', array(), 'Modules.Egquotation.Admin'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'orderby' => false
            ),
            'position' => array(
                'title' => $this->trans('Position', array(), 'Modules.Egquotation.Admin'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ),
        );
    }


    public function init()
    {
        parent::init();

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND b.`id_shop` = ' . (int)Context::getContext()->shop->id;
        }
    }

    public static function getDescriptionClean($description)
    {
        return Tools::getDescriptionClean($description);
    }

    public static function getEmailClean($id_client)
    {
        $client = new Customer($id_client);
        return $client->email;
    }

    public static function getClientClean($id_client)
    {
        $client = new Customer($id_client);
        return $client->firstname . ' ' . $client->lastname;
    }

    public static function getProductClean($id_product)
    {
        $product = new Product($id_product);
        $currentLanguage = Context::getContext()->language->id;
        $productNameInCurrentLanguage = $product->name[$currentLanguage];
        return $productNameInCurrentLanguage;
    }

    /**
     * Update Positions
     */
    public function ajaxProcessUpdatePositions()
    {
        $way = (int)(Tools::getValue('way'));
        $id_eg_quotation = (int)(Tools::getValue('id'));
        $positions = Tools::getValue($this->table);

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int)$pos[2] === $id_eg_quotation) {
                if ($information = new EgQuotationClass((int)$pos[2])) {
                    if (isset($position) && $information->updatePosition($way, $position)) {
                        echo 'ok position ' . (int)$position . ' for tab ' . (int)$pos[1] . '\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update tab ' . (int)$id_eg_quotation . ' to position ' . (int)$position . ' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This tab (' . (int)$id_eg_quotation . ') can t be loaded"}';
                }
                break;
            }
        }
    }
}
