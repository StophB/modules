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

require_once(_PS_MODULE_DIR_ . "egquotation/classes/EgQuotationClass.php");


class EgQuotation extends Module
{
    protected $domain;
    protected $product;

    public function __construct()
    {
        $this->name = 'egquotation';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'MST';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->domain = 'Modules.Egquotation.Egquotation';
        $this->displayName = $this->l('Eg Quotation');
        $this->description = $this->l('Egio Quotation Module');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function createTabs()
    {
        $idParent = (int) Tab::getIdFromClassName('AdminEgDigital');
        if (empty($idParent)) {
            $parent_tab = new Tab();
            $parent_tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $parent_tab->name[$lang['id_lang']] = $this->trans('EGIO Modules', [], $this->domain);
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
            $tab->name[$lang['id_lang']] = $this->trans('EG Quotation', [], $this->domain);
        }
        $tab->class_name = 'AdminEgQuotationGeneral';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminEgDigital');
        $tab->module = $this->name;
        $tab->icon = 'library_books';
        $tab->add();

        // Manage Quotation
        $tab = new Tab();
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Manage Quotation', [], $this->domain);
        }
        $tab->class_name = 'AdminEgQuotation';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminEgQuotationGeneral');
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
        if ($tab_id = (int) Tab::getIdFromClassName($class_name)) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }

        return true;
    }

    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->createTabs()
            && $this->registerHooks();
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        $this->removeTabs('AdminEgQuotation');
        $this->removeTabs('AdminEgQuotationGeneral');

        return parent::uninstall();
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    private function registerHooks()
    {
        return $this->registerHook('header')
            && $this->registerHook('displayNavFullWidth')
            && $this->registerHook('displayProductListReviews')
            && $this->registerHook('displayProductActions');
    }

    public function hookDisplayNavFullWidth($params)
    {
        $id_customer = (int) $this->context->customer->id;
        $count_quotation = EgQuotationClass::getCount($id_customer);
        $quotations = EgQuotationClass::getQuotations($id_customer);
        $this->context->smarty->assign(array(
            'count_quotation' => $count_quotation,
            'quotations' => $quotations,
            'link' => $this->context->link->getModuleLink($this->name, 'quote')
        ));
        return $this->display(__FILE__, 'views/templates/front/quotation_label.tpl');
    }

    public function hookDisplayProductListReviews($params)
    {
        $context = Context::getContext();

        $product = $params["product"];
        $id_customer = (int) $context->customer->id;

        $exist_quotation = EgQuotationClass::getOne($id_customer, $product->id_product, $product->id_product_attribute);
        if ($exist_quotation > 0) {
            $exist = 'black';
        } else {
            $exist = 'primary';
        }
        $this->context->smarty->assign(array(
            'product' => $params['product'],
            'exist_quotation' => $exist
        ));

        return $this->display(__FILE__, 'views/templates/hook/add_to_quote.tpl');
    }

    public function hookDisplayProductActions($params)
    {
        $context = Context::getContext();

        $product = $params["product"];
        $id_customer = (int) $context->customer->id;

        $exist_quotation = EgQuotationClass::getOne($id_customer, $product->id_product, $product->id_product_attribute);
        $exist_quotation = ($exist_quotation > 0) ? 'disabled' : '';

        $this->context->smarty->assign([
            'product' => $product,
            'exist_quotation' => $exist_quotation,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/add_to_quote_prt.tpl');
    }
}
