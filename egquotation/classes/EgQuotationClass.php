<?php

class EgQuotationClass extends ObjectModel
{
    /** @var int id_eg_quotation */
    public $id_eg_quotation;

    /** @var int id_product */
    public $id_product;

    public $product_name;

    /** @var int id_product_attribute */
    public $id_product_attribute;

    /** @var string id_customer */
    public $id_customer;

    /** @var string email_customer */
    public $email_customer;

    /** @var string session */
    public $session;

    /** @var  int sport position */
    public $position;

    /** @var bool Status*/
    public $active = true;

    public $id_shop;
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'eg_quotation',
        'primary' => 'id_eg_quotation',
        'multilang' => false,
        'multishop' => true,
        'fields' => array(
            'id_product' =>   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_product_attribute' =>   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_customer' =>   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'email_customer' =>  array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 2000),
            'product_name' =>  array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 2000),
            'session' =>  array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 2000),
            'position' =>   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'active' =>  array('type' => self::TYPE_BOOL),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
        ),
    );
    /**
     * Adds current sport as a new Object to the database
     *
     * @param bool $autoDate    Automatically set `date_upd` and `date_add` columns
     * @param bool $nullValues Whether we want to use NULL values instead of empty quotes values
     *
     * @return bool Indicates whether the Information has been successfully added
     * @throws
     * @throws
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        Shop::addTableAssociation('eg_quotation', array('type' => 'shop'));
        parent::__construct($id, $id_lang, $id_shop);
    }
    /**
     * Adds current sport as a new Object to the database
     *
     * @param bool $autoDate    Automatically set `date_upd` and `date_add` columns
     * @param bool $nullValues Whether we want to use NULL values instead of empty quotes values
     *
     * @return bool Indicates whether the Information has been successfully added
     * @throws
     * @throws
     */
    public function add($autoDate = true, $nullValues = false)
    {
        $this->position = (int) $this->getMaxPosition() + 1;
        return parent::add($autoDate, $nullValues);
    }

    /**
     * @return int MAX Position Information
     */
    public static function getMaxPosition()
    {
        $query = new DbQuery();
        $query->select('MAX(position)');
        $query->from('eg_quotation', 'eg');

        $response = Db::getInstance()->getRow($query);

        if ($response['MAX(position)'] == null) {
            return -1;
        }
        return $response['MAX(position)'];
    }

    public static function getQuotations($id_customer)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('eg_quotation', 'eg');
        $sql->where('eg.id_customer = ' . (int) $id_customer . ' AND eg.`active` = 1');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getOne($id_customer, $id_product, $id_product_attribute = null)
    {
        $sql = new DbQuery();
        $sql->select('count(eg.id_customer)');
        $sql->from('eg_quotation', 'eg');
        if ($id_product_attribute) {
            $id_product_attribute_sql = ' AND eg.id_product_attribute = ' . (int) $id_product_attribute;
        }
        $sql->where('eg.id_customer = ' . (int) $id_customer . ' AND eg.id_product = ' . (int) $id_product . $id_product_attribute_sql . ' AND eg.`active` = 1');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public static function getOneSession($session, $id_product, $id_product_attribute = null)
    {
        $sql = new DbQuery();
        $sql->select('count(eg.session)');
        $sql->from('eg_quotation', 'eg');
        if ($id_product_attribute) {
            $id_product_attribute_sql = ' AND eg.id_product_attribute = ' . (int) $id_product_attribute;
        }
        $sql->where('eg.session = ' . (int) $session . ' AND eg.id_product = ' . (int) $id_product . $id_product_attribute_sql . ' AND eg.`active` = 1');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public static function AddIdCustumerBySession($session)
    {
        $context = Context::getContext();

        $id_customer = (int) $context->customer->id;
        $customer = new Customer($id_customer);
        $email_customer = (string) $customer->email;

        return  Db::getInstance()->update(
            'eg_quotation',
            [
                'id_customer' => $id_customer,
                'email_customer' => $email_customer,
            ],
            '`session` = ' . $session
        );
    }

    public static function getCount($id_customer)
    {
        $sql = new DbQuery();
        $sql->select('count(eg.id_eg_quotation)');
        $sql->from('eg_quotation', 'eg');
        $sql->where('eg.id_customer = ' . (int) $id_customer . ' AND eg.`active` = 1');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public static function getCountSession($session)
    {
        $sql = new DbQuery();
        $sql->select('count(eg.id_eg_quotation)');
        $sql->from('eg_quotation', 'eg');
        $sql->where('eg.session = ' . (int) $session . ' AND eg.`active` = 1');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    public function updatePosition($way, $position)
    {
        $query = new DbQuery();
        $query->select('eg.`eg_quotation`, eg.`position`');
        $query->from('eg_quotation', 'eg');
        $query->orderBy('eg.`position` ASC');
        $tabs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$tabs) {
            return false;
        }

        foreach ($tabs as $tab) {
            if ((int) $tab['id_eg_quotation'] == (int) $this->id) {
                $moved_tab = $tab;
            }
        }

        if (!isset($moved_tab) || !isset($position)) {
            return false;
        }
        return (Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'eg_quotation`
            SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE `position`
            ' . ($way
            ? '> ' . (int)$moved_tab['position'] . ' AND `position` <= ' . (int)$position
            : '< ' . (int)$moved_tab['position'] . ' AND `position` >= ' . (int)$position
        ))
            && Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'eg_quotation`
            SET `position` = ' . (int)$position . '
            WHERE `id_eg_quotation` = ' . (int)$moved_tab['id_eg_quotation']));
    }
}
