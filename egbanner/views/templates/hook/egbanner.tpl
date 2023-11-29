{*
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */
*}

{if isset($banners) && !empty($banners) && $status == 1}
    <div id="" class="container">
        <div class="row">
            <div class="block-title">
                <h2 class="title-light">
                    {l s='Title' d='Modules.Egbanner.Egbanner'}
                </h2>
                <p class="subtitle-bold">
                    {l s='Sub title' d='Modules.Egbanner.Egbanner'}
                </p>
            </div>
            <div class="col-md-12 pd0">
                {foreach from=$banners item=banner}
                <div class="col-md-4">
                    <div class="">
                        {if isset($banner.image) && !empty($banner.image)}
                            <img
                                class="replace-2x img-responsive"
                                src="{$uri}{$banner.image|escape:'html':'UTF-8'}"
                                alt="{if $banner.alt}{$banner.alt|escape:'htmlall':'UTF-8'}{/if}"
                                title="{if $banner.title}{$banner.title|escape:'htmlall':'UTF-8'}{/if}"
                                width="100%;" />
                        {/if}
                    </div>
                    <div class="">
                        {if isset($banner.id_category) && !empty($banner.id_category)}
                            <div class="eg-banner-button">
                                <a
                                    class="button whitetored medium-regular"
                                    href="{$link->getCategoryLink($banner.id_category)|escape:'html':'UTF-8'}">
                                    {$banner.category_name|escape:'htmlall':'UTF-8'}
                                </a>
                            </div>
                        {/if}
                        {if isset($banner.description) && !empty($banner.description)}
                            <div class="eg-banner-desc">
                                {$banner.description nofilter}
                            </div>
                        {/if}

                    </div>
                </div>
            {/foreach}
            </div>
        </div>
    </div>
{/if}
