<div class="btn-group">
  <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fas fa-list"></i> {l s='Quotations' d="Modules.Egquotation.Shop"} (<span class="count-quotation">{$count_quotation}</span>)
  </button>
  <div class="dropdown-menu">
    {foreach from=$quotations item=quotation}
      <a class="dropdown-item" href="#">{$quotation.product_name}</a>
    {/foreach}

    
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="{$link}">View Quotations</a>
  </div>
</div>
