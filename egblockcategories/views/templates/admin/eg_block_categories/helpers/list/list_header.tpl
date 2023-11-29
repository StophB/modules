{*
 * 2020  (c)  Egio digital
 *
 * MODULE EgBlockCategories
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */
*}

{extends file="helpers/list/list_header.tpl"}
{block name='override_header'}
    <div class="eg-page-head with-tabs">
        <div class="eg-page-head-tabs" id="head_tabs">
            <ul class="nav">
                <li>
                    <a href="{$link->getAdminLink('AdminEgConfBlockCategories')}" id="AdminEgConfBlockCategories">
                        <i class="icon-cogs"></i>
                        {l s='Configuration' mod='egblockcategories'}
                        <span class="notification-container">
                        <span class="notification-counter"></span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{$link->getAdminLink('AdminEgBlockCategories')}" id="AdminEgBlockCategories" class="current">
                        <i class="icon-cogs"></i>
                        {l s='Manage categories' mod='egblockcategories'}
                        <span class="notification-container">
                        <span class="notification-counter"></span>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
{/block}
