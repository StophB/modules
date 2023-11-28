<?php

/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . "egblockcategories/classes/EgBlockCategoriesClass.php");


class EgBlockCategories extends Module
{
    protected $templateFile;
    protected $domain;
    protected $img_path;

    public function __construct()
    {
        $this->name = 'egblockcategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'MST';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->domain = 'Modules.Egblockcategories.Egblockcategories';
        $this->displayName = $this->trans('Eg Block Categories', [], $this->domain);
        $this->description = $this->l('Egio Block categories Module');

        $this->img_path = $this->_path . 'views/img/';
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];

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

    public function install()
    {
        Configuration::updateValue('EG_CATEGORIE_LIMIT', '');
        Configuration::updateValue('EG_CATEGORIE_ACTIVE', '');

        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->createTabs()
            && $this->registerHook('header')
            &&  $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName('EG_CATEGORIE_LIMIT');
        Configuration::deleteByName('EG_CATEGORIE_ACTIVE');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        $this->removeTabs('AdminEgBlockCategories');
        $this->removeTabs('AdminEgBlockCategoriesGeneral');
        $this->removeTabs('AdminEgConfBlockCategories');

        return parent::uninstall();
    }

        /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function hookDisplayHome()
    {
        $limit = Configuration::get('EG_CATEGORIE_LIMIT');
        $active = Configuration::get('EG_CATEGORIE_ACTIVE');
        $categories = EgBlockCategoriesClass::getCategories();

        $this->context->smarty->assign([
            'categories' => $categories,
            'uri' => $this->img_path,
            'active' => $active,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/egblockcategories.tpl');
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitEgblockcategoriesModule')) == true) {
            $this->postProcess();
        }

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEgblockcategoriesModule';
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
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'EG_CATEGORIE_ACTIVE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            ]
                        ],
                    ],
                    [
                        'type' => 'number',
                        'desc' => $this->l('Enter a limit'),
                        'name' => 'EG_CATEGORIE_LIMIT',
                        'label' => $this->l('Limit'),
                        'required' => true
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'EG_CATEGORIE_LIMIT' => Configuration::get('EG_CATEGORIE_LIMIT'),
            'EG_CATEGORIE_ACTIVE' => Configuration::get('EG_CATEGORIE_ACTIVE')
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }
}
