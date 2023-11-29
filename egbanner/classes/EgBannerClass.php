<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

/**
 * Class EgBannerClass.
 */
class EgBannerClass extends ObjectModel
{
    /** @var int EdBannerID */
    public $id_eg_banner;

  /** @var int Category ID */
    public $id_category;

 	/** @var string title Manufacture */
	public $title;

	/** @var string hook */
	public $hook;

    /** @var  int width image banner */
    public $width;

    /** @var  int height image banner */
    public $height;

    /** @var  int sport position */
    public $position;

    /** @var  string Long description Manufacture*/
    public $description;

    /** @var string image  */
    public $image;

    /** @var string image_mobile  */
    public $image_mobile;

    /** @var string alt image  */
    public $alt;

    /** @var string link image */
    public $link;

    /** @var bool Status for display Banner*/
    public $active = true;


	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'eg_banner',
		'primary' => 'id_eg_banner',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'id_category' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'position' =>           array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'active' =>             array('type' => self::TYPE_BOOL),

            /* Lang fields Banner*/
            'image' =>              array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'image_mobile' =>       array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'alt' =>                array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'link' =>               array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'title' =>              array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'description' =>        array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
        ),
	);

    /**
     * Adds current sport as a new Object to the database
     *
     * @param bool $autoDate    Automatically set `date_upd` and `date_add` columns
     * @param bool $nullValues Whether we want to use NULL values instead of empty quotes values
     *
     * @return bool Indicates whether the Banner has been successfully added
     * @throws
     * @throws
     */
    public function add($autoDate = true, $nullValues = false)
    {
        $this->position = (int) $this->getMaxPosition() + 1;
        return parent::add($autoDate, $nullValues);
    }

    /**
     * @return int MAX Position Banner
     */
    public static function getMaxPosition()
    {
        $query = new DbQuery();
        $query->select('MAX(position)');
        $query->from('eg_banner', 'eg');

        $response = Db::getInstance()->getRow($query);

        if ($response['MAX(position)'] == null){
            return -1;
        }
        return $response['MAX(position)'];
    }

    /**
     * @param $way int
     * @param $position int Position Banner
     * @return bool
     * @throws
     */
    public function updatePosition($way, $position)
    {
        $query = new DbQuery();
        $query->select('eg.`id_eg_banner`, eg.`position`');
        $query->from('eg_banner', 'eg');
        $query->orderBy('eg.`position` ASC');
        $tabs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$tabs ) {
            return false;
        }

        foreach ($tabs as $tab) {
            if ((int) $tab['id_eg_banner'] == (int) $this->id) {
                $moved_tab = $tab;
            }
        }

        if (!isset($moved_tab) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'eg_banner`
            SET `position`= `position` '.($way ? '- 1' : '+ 1').'
            WHERE `position`
            '.($way
                    ? '> '.(int)$moved_tab['position'].' AND `position` <= '.(int)$position
                    : '< '.(int)$moved_tab['position'].' AND `position` >= '.(int)$position
                ))
            && Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'eg_banner`
            SET `position` = '.(int)$position.'
            WHERE `id_eg_banner` = '.(int)$moved_tab['id_eg_banner']));
    }

    /**
     * @param $value string image Banner
     * @return string src
     */
    public static function showBanner($value)
    {
        $src = __PS_BASE_URI__. 'modules/egbanner/views/img/'.$value;
        return $value ? '<img src="'.$src.'" width="80" height="40px" class="img img-thumbnail"/>' : '-';
    }

    /**
     * @param $idBannerPos int ID BannerPos
     * @return string hook name
     */
    public static function getNameHook($idBannerPos)
    {
        $query = new DbQuery();
        $query->select('ebp.hook');
        $query->from('eg_banner_pos', 'ebp');
        $query->where('ebp.`id_eg_banner_pos` =  '.(int) $idBannerPos);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * @param $limit int
     * @return array list banner by hook
     * @throws
     */
    public static function getBannerFromHook($limit = null)
    {
        $idLang = Context::getContext()->language->id;

        $query = new DbQuery();
        $query->select('eg.*, egl.*');
        $query->from('eg_banner', 'eg');
        $query->leftJoin('eg_banner_lang', 'egl', 'eg.`id_eg_banner` = egl.`id_eg_banner`'.Shop::addSqlRestrictionOnLang('egl'));
        $query->where('eg.`active` =  1 AND egl.`id_lang` =  '.(int) $idLang);
        if ($limit) {
            $query->limit((int) $limit);
        }
        $query->orderBy('eg.`position` ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public static function getCategorySelectedById($idEgBanner)
    {
        $query = new DbQuery();
        $query->select('b.`id_category`');
        $query->from('eg_banner', 'b');
        $query->where('b.`id_eg_banner` =  '.(int) $idEgBanner);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * @param $IdCategory int Category ID
     * @return string name category
     */
    public static function getNameCategoryById($IdCategory)
    {
        $idLang = (int) Context::getContext()->language->id;
        $query = new DbQuery();
        $query->select('name');
        $query->from('category_lang', 'cl');
        $query->leftJoin('eg_banner', 'cb', 'cb.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl'));
        $query->where('cb.`id_category` =  '.(int) $IdCategory.' AND cl.`id_lang` =  '.$idLang);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public static function updateEgBannerImag($champ, $imgValue)
    {
        $res = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'eg_banner_lang` SET '.$champ.' = Null  WHERE '.$champ.' = "'.$imgValue.'"');
        if ($res && file_exists(__PS_BASE_URI__. 'modules/egbanner/views/img/'.$imgValue)) {
            @unlink(__PS_BASE_URI__. 'modules/egbanner/views/img/'.$$imgValue);
        }
    }
}
