<?php

class EgQuotationQuoteModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $id_customer = (int) $this->context->customer->id;
        $quotations = EgQuotationClass::getQuotations($id_customer);
        
        $this->context->smarty->assign([
            'quotations' => $quotations,
        ]);

        $this->setTemplate($this->getTemplatePath('form.tpl'));
    }
    
    /**
     * getTemplatePath
     *
     * @param  mixed $tpl
     * @return void
     */
    public function getTemplatePath($tpl)
    {
        return "module:" . $this->module->name . '/views/templates/front/'.$tpl;
    }

    public function postProcess()
    {
        if (Tools::getValue('process') == 'remove') {
            $this->displayAjaxDeleteQuote();
        } elseif (Tools::getValue('process') == 'SubmitQuote') {
            $this->displayAjaxSubmitQuote();
        } elseif (Tools::getValue('process') == 'check') {
            $this->displayAjaxCheckQuote();
        }
    }

    public function displayAjaxSubmitQuote()
    {
        $context = Context::getContext();

        $productId = (int) Tools::getValue('productId');
        $id_product_attribute = Tools::getValue('id_product_attribute');

        $id_customer = (int) $context->customer->id;
        $customer = new Customer($id_customer);
        $email_customer = (string) $customer->email;

        if (isset($productId) && !empty($productId)) {

            if ($context->customer->isLogged()) {

                $exist_quotation = EgQuotationClass::getOne($id_customer, $productId, $id_product_attribute);

                if ($exist_quotation == 0) {

                    $product = new Product($productId, $id_product_attribute, $context->language->id);
                    $productName = $product->getProductName($productId, $id_product_attribute = null, $context->language->id);

                    $quote = new EgQuotationClass();
                    $quote->id_customer = (int) $id_customer;
                    $quote->email_customer = (string) $email_customer;
                    $quote->product_name = (string) $productName;
                    $quote->id_product = (int) $productId;
                    $quote->id_product_attribute = (int) $id_product_attribute;
                    $quote->id_shop = $context->shop->id;

                    if ($quote->add() !== false) {
                        $quotation_count = EgQuotationClass::getCount($id_customer);
                        if ($exist_quotation > 0) {
                            $exist_quotation = 'disabled';
                        } else {
                            $exist_quotation = '';
                        }
                        $response = [
                            'success' => true,
                            'quotes' => $quotation_count,
                            'product_name' => $productName,
                            'disable_btn' => 'disabled',
                            'message' => $this->trans('Quotation registered', [], 'Modules.Egquotation.Shop'),
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => $this->trans('Quotation has Error', [], 'Modules.Egquotation.Shop'),
                        ];
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => $this->trans('Quotation Already exist', [], 'Modules.Egquotation.Shop'),
                    ];
                }
            } else {
                $context = Context::getContext();

                $quotation_sesion = $context->cookie->__get('quotation_sesion');

                if (!$quotation_sesion) {
                    $context->cookie->__set('quotation_sesion', rand());
                    $quotation_sesion = $context->cookie->__get('quotation_sesion');
                } else {
                    $exist_quotation = EgQuotationClass::getOneSession($quotation_sesion, $productId, $id_product_attribute);

                    if ($exist_quotation == 0) {


                        $product = new Product($productId, $id_product_attribute, $context->language->id);
                        $productName = $product->getProductName($productId, $id_product_attribute = null, $context->language->id);
                        $quote = new EgQuotationClass();
                        $quote->session = $quotation_sesion;
                        $quote->id_product = (int) $productId;
                        $quote->id_product_attribute = (int) $id_product_attribute;
                        $quote->product_name =  $productName;
                        $quote->id_shop = $context->shop->id;

                        if ($quote->add() !== false) {
                            $count_quotation = EgQuotationClass::getCountSession($quotation_sesion);

                            $response = [
                                'success' => true,
                                'product_name' => $productName,
                                'disable_btn' => 'disabled',
                                'quotes' => $count_quotation,
                                'message' => $this->trans('Quotation registered', [], 'Modules.Egquotation.Shop'),
                            ];
                        } else {
                            $response = [
                                'success' => false,
                                'message' => $this->trans('Quotation has Error', [], 'Modules.Egquotation.Shop'),
                            ];
                        }
                    } else {
                        $response = [
                            'success' => false,
                            'message' => $this->trans('Quotation Already exist', [], 'Modules.Egquotation.Shop'),
                        ];
                    }
                }
            }
        }
        die(json_encode($response));
    }


    public function displayAjaxCheckQuote()
    {
    }

    public function displayAjaxDeleteQuote()
    {
    }
}
