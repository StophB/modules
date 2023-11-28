<?php

class EgBlockCategoriesClass extends ObjectModel
{
    public $id_eg_blockcategory;
    public $title;
    public $subtitle;
    public $url;
    public $position;
    public $image;
    public $active = true;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'eg_block_categories',
        'primary' => 'id_eg_block_categories',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL],
            'url' =>  ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'image' =>  ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],

            /* Lang fields */
            'title' =>  ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'subtitle' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
        ],
    ];

    public function add($autoDate = true, $nullValues = false)
    {
        $this->position = (int) $this->getMaxPosition() + 1;
        return parent::add($autoDate, $nullValues);
    }

    public static function showImage($value)
    {
        $src = __PS_BASE_URI__ . 'modules/egblockcategories/views/img/' . $value;
        return $value ? '<img src="' . $src . '" width="80" height="40px" class="img img-thumbnail"/>' : '-';
    }

    public static function getMaxPosition()
    {
        $query = new DbQuery();
        $query->select('MAX(position)');
        $query->from('eg_block_categories', 'eg');

        $response = Db::getInstance()->getRow($query);

        if ($response['MAX(position)'] == null) {
            return -1;
        }
        return $response['MAX(position)'];
    }

    public function updatePosition($way, $position)
    {
        $query = new DbQuery();
        $query->select('eg.`id_eg_block_categories`, eg.`position`');
        $query->from('eg_block_categories', 'eg');
        $query->orderBy('eg.`position` ASC');
        $tabs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$tabs) {
            return false;
        }

        foreach ($tabs as $tab) {
            if ((int) $tab['id_eg_block_categories'] == (int) $this->id) {
                $moved_tab = $tab;
            }
        }

        if (!isset($moved_tab) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return
            Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'eg_block_categories`
            SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE `position`
            ' . ($way
                ? '> ' . (int)$moved_tab['position'] . ' AND `position` <= ' . (int)$position
                : '< ' . (int)$moved_tab['position'] . ' AND `position` >= ' . (int)$position
            ))
            && Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'eg_block_categories`
            SET `position` = ' . (int)$position . '
            WHERE `id_eg_block_categories` = ' . (int)$moved_tab['id_eg_block_categories']);
    }

    public static function updateEgCategorieImage($champ, $imgValue)
    {
        $res = Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'eg_block_categories` SET ' . $champ . ' = Null  WHERE ' . $champ . ' = "' . $imgValue . '"'
        );
        if ($res && file_exists(__PS_BASE_URI__ . 'modules/egblockcategories/views/img/' . $imgValue)) {
            @unlink(__PS_BASE_URI__ . 'modules/egblockcategories/views/img/' . $$imgValue);
        }
    }

    public static function getCategories($limit = null)
    {
        $idLang = Context::getContext()->language->id;

        $query = new DbQuery();
        $query->select('eg.*, egl.*');
        $query->from('eg_block_categories', 'eg');
        $query->leftJoin('eg_block_categories_lang', 'egl', 'eg.`id_eg_block_categories` = egl.`id_eg_block_categories`' . Shop::addSqlRestrictionOnLang('egl'));
        $query->where('eg.`active` =  1 AND egl.`id_lang` =  ' . (int) $idLang);

        if ($limit) {
            $query->limit((int) $limit);
        }
        $query->orderBy('eg.`position` ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
}
