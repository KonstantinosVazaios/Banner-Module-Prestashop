{*
* 2007-2022 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2022 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
	
{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'file_lang'}
		<div class="col-lg-9">
			{foreach from=$languages item=language}
				{if $languages|count > 1}
					<div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
				{/if}
				<div class="form-group">
					<div class="col-lg-6">
						<input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="file" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="hide" />
						<div class="dummyfile input-group">
							<span class="input-group-addon"><i class="icon-file"></i></span>
							<input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-name" type="text" class="disabled" name="filename" readonly />
							<span class="input-group-btn">
								<button id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
									<i class="icon-folder-open"></i> {l s='Choose a file' mod='sleedbanner'}
								</button>
							</span>
						</div>
					</div>
					{if $languages|count > 1}
						<div class="col-lg-2">
							<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
								{$language.iso_code|escape:'htmlall':'UTF-8'}
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach from=$languages item=lang}
								<li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
								{/foreach}
							</ul>
						</div>
					{/if}
				</div>
				<div class="form-group">
					{if isset($fields_value[$input.name][$language.id_lang]) && $fields_value[$input.name][$language.id_lang] != ''}
					<div id="{$input.name|escape:'htmlall':'UTF-8'}-{$language.id_lang|escape:'htmlall':'UTF-8'}-images-thumbnails" class="col-lg-12">
						<img src="{$uri|escape:'htmlall':'UTF-8'}img/{$fields_value[$input.name][$language.id_lang]|escape:'htmlall':'UTF-8'}" class="img-thumbnail"/>
					</div>
					{/if}
				</div>
				{if $languages|count > 1}
					</div>
				{/if}
				<script>
				$(document).ready(function(){
					$('#{$input.name}_{$language.id_lang}-selectbutton').click(function(e){
						$('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').trigger('click');
					});
					$('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').change(function(e){
						var val = $(this).val();
						var file = val.split(/[\\/]/);
						$('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}-name').val(file[file.length-1]);
					});
				});
			</script>
			{/foreach}
			{if isset($input.desc) && !empty($input.desc)}
				<p class="help-block">
					{$input.desc|escape:'htmlall':'UTF-8'}
				</p>
			{/if}
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
