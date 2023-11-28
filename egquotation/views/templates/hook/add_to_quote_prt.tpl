<input type="button" class="btn btn-primary add-to-quote pos" value="{l s='Add To Quote' d="Modules.Egquotation.Shop"}"
 data-product="{$product.id_product}"
 data-product-attribute="{$product.id_product_attribute}" data-url="{url entity='module' name='egquotation' controller='quote' params=['process' => 'SubmitQuote']}" {$exist_quotation}>
