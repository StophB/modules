<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBlockCategories
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

/**
 * Class EgBlockCategoriesClass.
 */
class EgBlockCategoriesClass extends ObjectModel
{
    /** @var int EgBlockCategories ID */
    public $id_eg_blockcategory;

    /** @var string title Manufacture */
    public $title;

    /** @var string subtitle Manufacture */
    public $subtitle;

    /** @var string hook */
    public $hook;

    /** @var  int sport position */
    public $position;

    /** @var string image  */
    public $image;

    /** @var string alt image  */
    public $alt;

    /** @var string link image */
    public $link;

    /** @var bool Status for display Categoriy*/
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

            /* Lang fields */
            'image' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'link' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'alt' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'title' =>  ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'subtitle' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
        ],
    ];

    /**
     * Adds current sport as a new Object to the database
     *
     * @param bool $autoDate    Automatically set `date_upd` and `date_add` columns
     * @param bool $nullValues Whether we want to use NULL values instead of empty quotes values
     *
     * @return bool Indicates whether the Category has been successfully added
     * @throws
     * @throws
     */
    public function add($autoDate = true, $nullValues = false)
    {
        $this->position = (int) $this->getMaxPosition() + 1;
        return parent::add($autoDate, $nullValues);
    }

    /**
     * @return int MAX Position Category
     */
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

    /**
     * @param $way int
     * @param $position int Position Caregory
     * @return bool
     * @throws
     */
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

    public static function showCategory($value)
    {
        $src = __PS_BASE_URI__ . 'modules/egblockcategories/views/img/' . $value;
        return $value ? '<img src="' . $src . '" width="80" height="40px" class="img img-thumbnail"/>' : '-';
    }

    /**
     * @param $idCategoryPos int ID CategoriyPos
     * @return string hook name
     */
    public static function getNameHook($idCategoryPos)
    {
        $query = new DbQuery();
        $query->select('ebp.hook');
        $query->from('eg_category_pos', 'ebp');
        $query->where('ebp.`id_eg_category_pos` =  '.(int) $idCategoryPos);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * @param $limit int
     * @return array list Category by hook
     * @throws
     */
    public static function getCategoryFromHook($limit = null)
    {
        $idLang = Context::getContext()->language->id;

        $query = new DbQuery();
        $query->select('eg.*, egl.*');
        $query->from('eg_block_categories', 'eg');
        $query->leftJoin('eg_block_categories_lang', 'egl', 'eg.`id_eg_block_categories` = egl.`id_eg_block_categories`'.Shop::addSqlRestrictionOnLang('egl'));
        $query->where('eg.`active` =  1 AND egl.`id_lang` =  '.(int) $idLang);
        if ($limit) {
            $query->limit((int) $limit);
        }
        $query->orderBy('eg.`position` ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public static function updateEgCategorieImage($champ, $imgValue)
    {
        $res = Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'eg_block_categories_lang` SET ' . $champ . ' = Null  WHERE ' . $champ . ' = "' . $imgValue . '"'
        );
        if ($res && file_exists(__PS_BASE_URI__ . 'modules/egblockcategories/views/img/' . $imgValue)) {
            @unlink(__PS_BASE_URI__ . 'modules/egblockcategories/views/img/' . $$imgValue);
        }
    }
}
