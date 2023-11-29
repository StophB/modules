<?php

/**
 * 2023  (c)  Egio digital
 *
 * MODULE EgBlockGategories
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__).'/classes/EgBlockCategoriesClass.php');

class EgBlockCategories extends Module
{
    protected $_html = '';
    protected $templateFile;
    protected $domain;

    public function __construct()
    {
        $this->name = 'egblockcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'egio digital';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->domain = 'Modules.Egblockcategories.Egblockcategories';
        $this->displayName = $this->trans('Eg Block Categories', [], $this->domain);
        $this->description = $this->l('Display Block categories in home page', [], $this->domain);

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], $this->domain);
        $this->img_path = $this->_path.'views/img/';
        $this->templateFile = 'module:egblockcategories/views/templates/hook/egblockcategories.tpl';
    }

    /**
     * @see  CREATE TAB module in Dashboard
     */
    public function createTabs()
    {
        $idParent = (int) Tab::getIdFromClassName('AdminEgDigital');
        if (empty($idParent)) {
            $parent_tab = new Tab();
            $parent_tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $parent_tab->name[$lang['id_lang']] = $this->trans('Modules EGIO', [], $this->domain);
            }
            $parent_tab->class_name = 'AdminEgDigital';
            $parent_tab->id_parent = 0;
            $parent_tab->module = $this->name;
            $parent_tab->icon = 'library_books';
            $parent_tab->add();
        }

        $tab = new Tab();
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('EG Block Categories', [], $this->domain);
        }
        $tab->class_name = 'AdminEgBlockCategoriesGeneral';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminEgDigital');
        $tab->module = $this->name;
        $tab->icon = 'library_books';
        $tab->add();

        // Manage Module
        $tab = new Tab();
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Config', [], $this->domain);
        }
        $tab->class_name = 'AdminEgConfBlockCategories';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminEgBlockCategoriesGeneral');
        $tab->module = $this->name;
        $tab->add();

        // Manage Block Categories
        $tab = new Tab();
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Manage Block Categories', [], $this->domain);
        }
        $tab->class_name = 'AdminEgBlockCategories';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminEgBlockCategoriesGeneral');
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

    /**
     * Remove Tabs module in Dashboard
     * @param $class_name string name Tab
     * @return bool
     * @throws
     * @throws
     */
    public function removeTabs($class_name)
    {
        if ($tab_id = (int)Tab::getIdFromClassName($class_name)) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->createTabs()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            &&  $this->registerHook('displayHome');
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        $this->removeTabs('AdminEgBlockCategories');
        $this->removeTabs('AdminEgBlockCategoriesGeneral');
        $this->removeTabs('AdminEgConfBlockCategories');

        return parent::uninstall();
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function renderList()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminModules');
        $idEmployee = (int) $this->context->employee->id;
        $token = Tools::getAdminToken('AdminModules'.$idTab.$idEmployee);
        $this->context->smarty->assign(
            array(
                'linkConfigBlockCategories' => $this->context->link->getAdminLink('AdminEgConfBlockCategories'),
                'linkManageBlockCategories' => $this->context->link->getAdminLink('AdminEgBlockCategories'),
            )
        );
        $template = _PS_MODULE_DIR_ . $this->name .'/views/templates/admin/_configure/helpers/list/list_header.tpl';
        return $this->context->smarty->fetch($template);
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }

    public function clearCache()
    {
        $this->_clearCache($this->templateFile);
    }

    public function hookDisplayHome()
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('egblockcategories'))) {
            $count = (int)Configuration::get('EG_COUNT_CATEGORY');
            $limit = isset($count) ? $count : null;
            $status = Configuration::get('EG_CATEGORY_STATUS');
            $categories = EgBlockCategoriesClass::getCategoriesFromHook($limit);
            $this->context->smarty->assign(array(
                'categories' => $categories,
                'status' => $status,
                'uri' => $this->img_path,
            ));
        }
        return $this->fetch($this->templateFile, $this->getCacheId('egblockcategories'));
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitModule')) {
            Configuration::updateValue('EG_COUNT_CATEGORY', Tools::getValue('EG_COUNT_CATEGORY'));
            Configuration::updateValue('EG_CATEGORY_STATUS', Tools::getValue('EG_CATEGORY_STATUS'));
        }

        $this->_html .= $this->renderList();
        $this->_html .= $this->renderForm();
        return $this->_html;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        return array(
            'EG_COUNT_CATEGORY' => Configuration::get('EG_COUNT_CATEGORY'),
            'EG_CATEGORY_STATUS' => Configuration::get('EG_CATEGORY_STATUS'),
        );
    }

    /**
     * @return array
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'tinymce' => true,
                'legend' => [
                    'title' => $this->trans('Configure Block Categories', [], $this->domain),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Number of categories to be displayed', [], $this->domain),
                        'name' => 'EG_COUNT_CATEGORY',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Displayed', [], $this->domain),
                        'name' => 'EG_CATEGORY_STATUS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], $this->domain)
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], $this->domain)
                            ]
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], $this->domain),
                ],
            ],
        ];
    }
}
