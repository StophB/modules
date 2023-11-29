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
 * @property EgBlockCategoriesClass $object
 */
class AdminEgBlockCategoriesController extends ModuleAdminController
{
    protected $position_identifier = 'id_eg_block_categories';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'eg_block_categories';
        $this->className = 'EgBlockCategoriesClass';
        $this->identifier = 'id_eg_block_categories';
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';
        $this->toolbar_btn = null;
        $this->list_no_link = true;
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        Shop::addTableAssociation($this->table, ['type' => 'shop']);

        parent::__construct();

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            ]
        ];

        $this->fields_list = [
            'id_eg_block_categories' => [
                'title' => $this->l('Id')
            ],
            'image' => [
                'title' => $this->l('Image'),
                'type' => 'text',
                'callback' => 'showCategory',
                'callback_object' => 'EgBlockCategoriesClass',
                'class' => 'fixed-width-xxl',
                'search' => false,
            ],
            'title' => [
                'title' => $this->l('Title'),
                'filter_key' => 'b!title',
            ],
            'subtitle' => [
                'title' => $this->l('Subtitle'),
                'filter_key' => 'b!subtitle',
            ],
            'active' => [
                'title' => $this->l('Displayed'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'orderby' => false
            ],
            'position' => [
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ],
        ];
    }

    /**
     * AdminController::init() override
     * @see AdminController::init()
     */
    public function init()
    {
        parent::init();

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND b.`id_shop` = ' . (int)Context::getContext()->shop->id;
        }
    }

    /**
     * @see AdminController::initPageHeaderToolbar()
     */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_category'] = array(
                'href' => self::$currentIndex.'&addeg_block_categories&token='.$this->token,
                'desc' => $this->l('Add new category'),
                'icon' => 'process-icon-new'
            );
        }
        parent::initPageHeaderToolbar();
    }

    protected function stUploadImage($item)
    {
        $result = [
            'error' => [],
            'image' => '',
        ];
        $types = ['gif', 'jpg', 'jpeg', 'jpe', 'png', 'svg'];
        if (isset($_FILES[$item]) && isset($_FILES[$item]['tmp_name']) && !empty($_FILES[$item]['tmp_name'])) {
            $name = str_replace(strrchr($_FILES[$item]['name'], '.'), '', $_FILES[$item]['name']);

            $imageSize = @getimagesize($_FILES[$item]['tmp_name']);
            if (
                !empty($imageSize) &&
                ImageManager::isCorrectImageFileExt($_FILES[$item]['name'], $types)
            ) {
                $imageName = explode('.', $_FILES[$item]['name']);
                $imageExt = $imageName[1];
                $tempName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $coverImageName = $name . '-' . rand(0, 1000) . '.' . $imageExt;
                if ($upload_error = ImageManager::validateUpload($_FILES[$item])) {
                    $result['error'][] = $upload_error;
                } elseif (!$tempName || !move_uploaded_file($_FILES[$item]['tmp_name'], $tempName)) {
                    $result['error'][] = $this->l('An error occurred during move image.');
                } else {
                    $destinationFile = _PS_MODULE_DIR_ . $this->module->name . '/views/img/' . $coverImageName;
                    if (!ImageManager::resize($tempName, $destinationFile, null, null, $imageExt)) {
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
        if ($this->action && $this->action == 'save') {
            foreach (Language::getLanguages(true) as $lang) {
                $image = $this->stUploadImage('image_'.$lang['id_lang']);
                if (isset($image['image']) && !empty($image['image'] )) {
                    $_POST['image_'.$lang['id_lang']]= $image['image'];
                }
            }
        }

        // Delete Images EG Block Categories
        if (Tools::isSubmit('forcedeleteImage') || Tools::getValue('deleteImage')) {
            $champ = Tools::getValue('champ');
            $imgValue = Tools::getValue('image');
            EgBlockCategoriesClass::updateEgCategorieImage($champ, $imgValue);
            if (Tools::isSubmit('forcedeleteImage')) {
                Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminEgBlockCategories'));
            }
        }

        return parent::postProcess();
    }

    /**
     * @see AdminController::initProcess()
     */
    public function initProcess()
    {
        $this->context->smarty->assign([
            'uri' => $this->module->getPathUri()
        ]);
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

        $this->fields_form = [
            'tinymce' => true,
            'legend' => [
                'title' => $this->l('Page'),
                'icon' => 'icon-folder-close'
            ],
            // custom template
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Title:'),
                    'name' => 'title',
                    'lang' => true,
                    'desc' => $this->l('Please enter a title for the category.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Subtitle:'),
                    'name' => 'subtitle',
                    'lang' => true,
                    'desc' => $this->l('Please enter a subtitle for the category.'),
                ],
                [
                    'type' => 'file_lang',
                    'label' => $this->l('Image:'),
                    'name' => 'image',
                    'delete_url' => self::$currentIndex . '&' . $this->identifier . '=' . $obj->id . '&token=' . $this->token . '&champ=image&deleteImage=1',
                    'desc' => $this->l('Upload an image for the category.')
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Alt:'),
                    'name' => 'alt',
                    'desc' => $this->l('Please enter an alternate text for the category.'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Display'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        ]
                    ]
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];


        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = [
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            ];
        }

        return parent::renderForm();
    }

    /**
     * Update Positions Category
     */
    public function ajaxProcessUpdatePositions()
    {
        $way = (int)(Tools::getValue('way'));
        $idEgCategorie = (int)(Tools::getValue('id'));
        $positions = Tools::getValue($this->table);

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int)$pos[2] === $idEgCategorie) {
                if ($categorie = new EgBlockCategoriesClass((int)$pos[2])) {
                    if (isset($position) && $categorie->updatePosition($way, $position)) {
                        echo 'ok position ' . (int)$position . ' for tab ' . (int)$pos[1] . '\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update tab ' . (int)$idEgCategorie . ' to position ' . (int)$position . ' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This tab (' . (int)$idEgCategorie . ') can t be loaded"}';
                }

                break;
            }
        }
    }
}
