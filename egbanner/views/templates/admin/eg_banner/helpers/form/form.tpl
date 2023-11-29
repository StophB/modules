{**
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

{extends file="helpers/form/form.tpl"}
{block name="input"}
    {if $input.name == "link_rewrite"}
        <script type="text/javascript">
            {if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
            var PS_ALLOW_ACCENTED_CHARS_URL = 1;
            {else}
            var PS_ALLOW_ACCENTED_CHARS_URL = 0;
            {/if}
        </script>
        {$smarty.block.parent}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name="script"}
    $(document).ready(function() {

        $('#active_on').bind('click', function(){
            toggleDraftWarning(false);
        });
        $('#active_off').bind('click', function(){
            toggleDraftWarning(true);
        });

        {*$('#id_ed_team').clone().attr('id', '').insertAfter('#id_eg_bloc');*}

        $('#page-header-desc-ed_team-new').click(function(e){

            jAlert('{l s='There is 1 warning .<br> You must save this page before adding Team.' js=1 d='Admin.Advparameters.Notification'}');

        });
        if ($('#id_ed_team').val() != '' && $('table.ed_member tbody tr').length == 0){
            initForm();

            $('#ed_galerie_form').hide();

            $('#page-header-desc-ed_galerie-new').click(function(e){
                initForm();
                $('#ed_team_form').slideToggle();
                $('#ed_member_form').slideToggle();
                $('#ed_galerie_form').slideToggle();
                $('html, body').animate({ scrollTop: 0 }, "slow");
                return false;
            });
        }

       
    });
    function loadEdMember(id_eg_bloc)
    {
        var currentIndexWithToken = 'index.php?controller=AdminEdMember&token={getAdminToken tab='AdminEdMember'}&&updateeg_bloc&id_eg_bloc='+id_eg_bloc ;
        window.location.href = currentIndexWithToken;

    }

    function initForm()
    {
        $('#id_eg_bloc').val('');
        $('#link_video').val('');
        $('#bloc_text').val('');
        $('#bloc_alt').val('');
        $('#position_text').val('');
    }
{/block}

{block name="leadin"}
    <div style="{if $active}display:none{/if}">
        <p class="alert alert-warning">
            {l s='Your page will be saved as a draft' d='Admin.Design.Notification'}
        </p>
    </div>
{/block}

{block name="input"}
    {if $input.type == 'select_category'}
        <select name="{$input.name}">
            {$input.options.html}
        </select>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name="field"}
    {if $input.type == 'file_lang'}
        <div class="col-lg-9">
            {foreach from=$languages item=language}
            {if $languages|count > 1}
                <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                    {/if}
                    <div class="form-group">
                        <div class="col-lg-6">
                            <input id="{$input.name}_{$language.id_lang}" type="file" name="{$input.name}_{$language.id_lang}" class="hide" />
                            <div class="dummyfile input-group">
                                <span class="input-group-addon"><i class="icon-file"></i></span>
                                <input id="{$input.name}_{$language.id_lang}-name" type="text" class="disabled" name="filename" readonly />
                                <span class="input-group-btn">
                                <button id="{$input.name}_{$language.id_lang}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
                                    <i class="icon-folder-open"></i> {l s='Choose a file' d='Modules.Banner.Shop'}
                                </button>
                            </span>
                            </div>
                        </div>
                        {if $languages|count > 1}
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                    {$language.iso_code}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    {foreach from=$languages item=lang}
                                        <li><a href="javascript:hideOtherLanguage({$lang.id_lang});" tabindex="-1">{$lang.name}</a></li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </div>
                    <div class="form-group">
                        {if isset($fields_value[$input.name][$language.id_lang]) && $fields_value[$input.name][$language.id_lang] != ''}
                            <div id="{$input.name}-{$language.id_lang}-images-thumbnails" class="col-lg-12">
                                <img src="{$uri}views/img/{$fields_value[$input.name][$language.id_lang]}" class="img-thumbnail"/>
                            </div>
                            <div class="clearfix">&nbsp;</div>
                            {if isset($input.delete_url)}
                                {assign var="url" value="`$input.delete_url`&image=`$fields_value[$input.name][$language.id_lang]`"}
                                <p>
                                    <a class="btn btn-default" href="{$url}">
                                        <i class="icon-trash"></i> {l s='Delete' mod='egBanner'}
                                    </a>
                                </p>
                            {/if}
                        {/if}
                    </div>
                    {if $languages|count > 1}
                </div>
            {/if}
                <script>
                    $(document).ready(function(){
                        $('#{$input.name}_{$language.id_lang}-selectbutton').click(function(e){
                            $('#{$input.name}_{$language.id_lang}').trigger('click');
                        });
                        $('#{$input.name}_{$language.id_lang}').change(function(e){
                            var val = $(this).val();
                            var file = val.split(/[\\/]/);
                            $('#{$input.name}_{$language.id_lang}-name').val(file[file.length-1]);
                        });
                    });
                </script>
            {/foreach}
            {if isset($input.desc) && !empty($input.desc)}
                <p class="help-block">
                    {$input.desc}
                </p>
            {/if}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

