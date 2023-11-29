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
 * @property EgBannerClass $object
 */
class AdminEgBannerController extends ModuleAdminController
{
    protected $position_identifier = 'id_eg_banner';
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'eg_banner';
        $this->className = 'EgBannerClass';
        $this->identifier = 'id_eg_banner';
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';
        $this->toolbar_btn = null;
        $this->list_no_link = true;
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->fields_list = array(
            'id_eg_banner' => array(
                'title' => $this->l('Id')
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'type' => 'text',
                'callback' => 'showBanner',
                'callback_object' => 'EgBannerClass',
                'class' => 'fixed-width-xxl',
                'search' => false,
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'filter_key' => 'b!title',
            ),
            'id_category' => array(
                'title' => $this->l('Category'),
                'callback' => 'getNameCategoryById',
                'callback_object' => 'EgBannerClass',
                'width' => 100,
                'type' => 'text',
            ),
            'active' => array(
                'title' => $this->l('Displayed'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'orderby' => false
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ),
        );
    }

    /**
     * @param $description
     * @return string Content without html
     */
    public static function getDescriptionClean($description)
    {
        return Tools::getDescriptionClean($description);
    }

    /**
     * AdminController::init() override
     * @see AdminController::init()
     */
    public function init()
    {
        parent::init();

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND b.`id_shop` = '.(int)Context::getContext()->shop->id;
        }
    }

    /**
     * @see AdminController::initPageHeaderToolbar()
     */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_banner'] = array(
                'href' => self::$currentIndex.'&addeg_banner&token='.$this->token,
                'desc' => $this->l('Add new banner'),
                'icon' => 'process-icon-new'
            );
        }
        parent::initPageHeaderToolbar();
    }

    /**
     * @param $item
     * @return array
     */
    protected function stUploadImage($item)
    {
        $result = array(
            'error' => array(),
            'image' => '',
        );
        $types = array('gif', 'jpg', 'jpeg', 'jpe', 'png', 'svg');
        if (isset($_FILES[$item]) && isset($_FILES[$item]['tmp_name']) && !empty($_FILES[$item]['tmp_name'])) {
            $name = str_replace(strrchr($_FILES[$item]['name'], '.'), '', $_FILES[$item]['name']);

            $imageSize = @getimagesize($_FILES[$item]['tmp_name']);
            if (!empty($imageSize) &&
                ImageManager::isCorrectImageFileExt($_FILES[$item]['name'], $types)) {
                $imageName = explode('.', $_FILES[$item]['name']);
                $imageExt = $imageName[1];
                $tempName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $coverImageName = $name .'-'.rand(0, 1000).'.'.$imageExt;
                if ($upload_error = ImageManager::validateUpload($_FILES[$item])) {
                    $result['error'][] = $upload_error;
                } elseif (!$tempName || !move_uploaded_file($_FILES[$item]['tmp_name'], $tempName)) {
                    $result['error'][] = $this->l('An error occurred during move image.');
                } else {
                    $destinationFile = _PS_MODULE_DIR_ . $this->module->name.'/views/img/'.$coverImageName;
                    if (!ImageManager::resize($tempName, $destinationFile, null, null, $imageExt)){
                        $result['error'][] = $this->l('An error occurred during the image upload.');
                    }
                }
                if (isset($tempName)) {
                    @unlink($tempName);
                }

                if (!count($result['error'])) {
                    $result['image'] = $coverImageName;
                    $result['width'] = $imageSize[0];
                    $result['height'] = $imageSize[1];
                }
                return $result;
            }
        } else {
            return $result;
        }
    }

    /**
     * AdminController::postProcess() override
     * @see AdminController::postProcess()
     */
    public function postProcess()
    {
        // Upload FILES EG Banner
        if ($this->action && $this->action == 'save') {
            foreach (Language::getLanguages(true) as $lang) {
                $image = $this->stUploadImage('image_'.$lang['id_lang']);
                $image_mobile = $this->stUploadImage('image_mobile_'.$lang['id_lang']);
                if (isset($image['image']) && !empty($image['image'] )) {
                    $_POST['image_'.$lang['id_lang']]= $image['image'];
                }
                if (isset($image_mobile['image']) && !empty($image_mobile['image'] )) {
                    $_POST['image_mobile_'.$lang['id_lang']]= $image_mobile['image'];
                }
            }
        }
        // Delete Images EG Banner
        if (Tools::isSubmit('forcedeleteImage') || Tools::getValue('deleteImage')) {
            $champ = Tools::getValue('champ');
            $imgValue = Tools::getValue('image');
            EgBannerClass::updateEgBannerImag($champ, $imgValue);
            if (Tools::isSubmit('forcedeleteImage')) {
                Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminEgBanner'));
            }
        }

        return parent::postProcess();
    }

    /**
     * @see AdminController::initProcess()
     */
    public function initProcess()
    {
        $this->context->smarty->assign(array(
            'uri' => $this->module->getPathUri()
        ));
        parent::initProcess();
    }

    public function getHookList()
    {
        $hooks = array();
        foreach ($this->myHook as $key => $hook)
        {
            $hooks[$key]['key'] = $hook;
            $hooks[$key]['name'] = $hook;
        }
        return $hooks;
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        if ($this->display == 'edit') {
            $idSelected = (int) EgBannerClass::getCategorySelectedById((int) $obj->id);
        } else {
            $idSelected = 0;
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Page'),
                'icon' => 'icon-folder-close'
            ),
            // custom template
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title:'),
                    'name' => 'title',
                    'lang' => true,
                    'desc' => $this->l('Please enter a title for the banner.'),
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Image Desktop:'),
                    'name' => 'image',
                    'lang' => true,
                    'delete_url' => self::$currentIndex.'&'.$this->identifier .'='.$obj->id.'&token='.$this->token.'&champ=image&deleteImage=1',
                    'desc' => $this->l('Upload an image for your top banner. The recommended dimensions are 556 x 555px if you are using the default theme.')
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Image Mobile:'),
                    'name' => 'image_mobile',
                    'lang' => true,
                    'delete_url' => self::$currentIndex.'&'.$this->identifier .'='.$obj->id.'&token='.$this->token.'&champ=image_mobile&deleteImage=1',
                    'desc' => $this->l('Upload an image for your top banner. The recommended dimensions are 384 x 366px if you are using the default theme.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Alt:'),
                    'name' => 'alt',
                    'lang' => true,
                    'desc' => $this->l('Please enter an alternate text for the banner.')

                ),
                array(
                    'type' => 'categories',
                    'label' => $this->l('Tree categories'),
                    'name' => 'id_category',
                    'tree' => array(
                        'root_category' => 1,
                        'id' => 'id_category',
                        'name' => 'name_category',
                        'selected_categories' => array($idSelected),
                    ),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description'),
                    'name' => 'description',
                    'autoload_rte' => true,
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}',
                    'desc' => $this->l('Please enter a description for the banner.')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                ),
            ),
             'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );


        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            );
        }

        return parent::renderForm();
    }

    /**
     * Update Positions Banner
     */
    public function ajaxProcessUpdatePositions()
    {
        $way = (int)(Tools::getValue('way'));
        $idEgBanner = (int)(Tools::getValue('id'));
        $positions = Tools::getValue($this->table);

        foreach ($positions as $position => $value){
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int)$pos[2] === $idEgBanner){
                if ($banner = new EgBannerClass((int)$pos[2])){
                    if (isset($position) && $banner->updatePosition($way, $position)){
                        echo 'ok position '.(int)$position.' for tab '.(int)$pos[1].'\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update tab '.(int)$idEgBanner.' to position '.(int)$position.' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This tab ('.(int)$idEgBanner.') can t be loaded"}';
                }

                break;
            }
        }
    }
}
