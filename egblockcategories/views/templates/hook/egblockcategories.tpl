{if isset($categories) && !empty($categories)}
    <div class="row">
        {foreach from=$categories item=category}
            <div class="col-xs-6 col-md-3">
                {if isset($category.image) && !empty($category.image)}
                    <img
                        class="replace-2x img-responsive"
                        src="{$uri}{$category.image|escape:'html':'UTF-8'}"
                        width="100px;"
                    />
                {/if}
                {if isset($category.title) && !empty($category.title)}
                    <h3>
                        {$category.title}
                    </h3>
                {/if}
                {if isset($category.subtitle) && !empty($category.subtitle)}
                    <p>
                        {$category.subtitle}
                    </p>
                {/if}
            </div>
        {/foreach}
    </div>
    {/if}