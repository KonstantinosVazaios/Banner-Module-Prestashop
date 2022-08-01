<?php

/**
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class SleedBanner extends Module 
{
    public $default_image_desktop = "default-banner-desktop.png";
    public $default_image_mobile = "default-banner-mobile.jpg";

    public function __construct()
    {
        $this->name = 'sleedbanner';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Sleed';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Create a banner and display to the front-office');
        $this->description = $this->l('This banner consists of an image and multilingual alt text');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() 
            && $this->installConfig()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayTopColumn')
            && $this->registerHook('displayBanner');
    }

    public function uninstall()
    {
        return parent::uninstall() 
            && $this->removeUploadedImgs()
            && $this->uninstallConfig();      
    }

    public function installConfig()
    {   
        $languages = Language::getLanguages(false);

		foreach ($languages as $lang) {

            $values_per_lang['SLEEDBANNER_IMG_DESKTOP'][$lang['id_lang']] = '';
            $values_per_lang['SLEEDBANNER_IMG_MOBILE'][$lang['id_lang']] = '';
            $values_per_lang['SLEEDBANNER_ALT'][$lang['id_lang']] = '';

            Configuration::updateValue('SLEEDBANNER_IMG_DESKTOP', $values_per_lang['SLEEDBANNER_IMG_DESKTOP']);
            Configuration::updateValue('SLEEDBANNER_IMG_MOBILE', $values_per_lang['SLEEDBANNER_IMG_MOBILE']);
            Configuration::updateValue('SLEEDBANNER_ALT', $values_per_lang['SLEEDBANNER_ALT']);

        }
		return true;
    }

    public function uninstallConfig()
    {
        Configuration::deleteByName('SLEEDBANNER_IMG_DESKTOP');
        Configuration::deleteByName('SLEEDBANNER_IMG_MOBILE');
		Configuration::deleteByName('SLEEDBANNER_ALT');
        return true;
    }

    public function removeUploadedImgs()
    {
        $languages = Language::getLanguages(false);

		foreach ($languages as $lang) {
            Configuration::get('SLEEDBANNER_IMG_DESKTOP', $lang['id_lang']) && @unlink(dirname(__FILE__).'/views/img/'.Configuration::get('SLEEDBANNER_IMG_DESKTOP', $lang['id_lang']));
            Configuration::get('SLEEDBANNER_IMG_MOBILE', $lang['id_lang']) && @unlink(dirname(__FILE__).'/views/img/'.Configuration::get('SLEEDBANNER_IMG_MOBILE', $lang['id_lang']));
        }

        return true;
    }

    public function getContent()
	{
		return $this->postProcess().$this->renderForm();
	}

    public function postProcess()
	{
        if (!Tools::isSubmit('submitBannerConfig')) return '';
        
        $languages = Language::getLanguages(false);
        $values_per_lang = array();

        foreach ($languages as $lang)
        {
            $imageManager = new ImageManager;

            $reqHasImageDesktop = $this->handleIssetImage('banner_img_desktop_', $imageManager, $lang); 

            if ($reqHasImageDesktop) { 
                
                // Delete old image desktop
                if (Configuration::get('SLEEDBANNER_IMG_DESKTOP', $lang['id_lang'])) 
                    @unlink(dirname(__FILE__).'/views/img/'.Configuration::get('SLEEDBANNER_IMG_DESKTOP', $lang['id_lang']));

                $values_per_lang['SLEEDBANNER_IMG_DESKTOP'][$lang['id_lang']] = $this->handleUploadImage('banner_img_desktop_', $imageManager, $lang, $values_per_lang);
            } 

            $reqHasImageMobile = $this->handleIssetImage('banner_img_mobile_', $imageManager, $lang);

            if ($reqHasImageMobile) { 
                
                // Delete old image mobile
                if (Configuration::get('SLEEDBANNER_IMG_MOBILE', $lang['id_lang'])) 
                    @unlink(dirname(__FILE__).'/views/img/'.Configuration::get('SLEEDBANNER_IMG_MOBILE', $lang['id_lang']));

                $values_per_lang['SLEEDBANNER_IMG_MOBILE'][$lang['id_lang']] = $this->handleUploadImage('banner_img_mobile_', $imageManager, $lang, $values_per_lang);
            } 

            $values_per_lang['SLEEDBANNER_ALT'][$lang['id_lang']] = Tools::getValue('banner_alt_'.$lang['id_lang']);
        }

        Configuration::updateValue('SLEEDBANNER_IMG_DESKTOP', $values_per_lang['SLEEDBANNER_IMG_DESKTOP']);
        Configuration::updateValue('SLEEDBANNER_IMG_MOBILE', $values_per_lang['SLEEDBANNER_IMG_MOBILE']);
        Configuration::updateValue('SLEEDBANNER_ALT', $values_per_lang['SLEEDBANNER_ALT']);

        return $this->displayConfirmation($this->l('The banner has been updated!'));
	}

	public function renderForm()
	{
        $language = new Language(Configuration::get('PS_LANG_DEFAULT'));

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Banner Configuration'),
            ),
            'input' => array(
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Banner Image Desktop'),
                    'name' => 'banner_img_desktop',
                    'required' => true,
                    'lang' => true
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Banner Image Mobile'),
                    'name' => 'banner_img_mobile',
                    'required' => true,
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Alt text'),
                    'name' => 'banner_alt',
                    'size' => 20,
                    'required' => true,
                    'lang' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );
        
        $helper = new HelperForm();
        
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        
        $helper->default_form_language = $language->id;
        $helper->allow_employee_form_lang = $language->id;
        
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;     
        $helper->toolbar_scroll = true;  
        $helper->submit_action = 'submitBannerConfig';
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            )
        );
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri().'views/',
			'fields_value' => $this->getFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

        return $helper->generateForm($fields_form);
	}

    public function getFieldsValues()
    {
        $fields = array();

		$languages = Language::getLanguages(false);

		foreach ($languages as $lang)
		{
			$fields['banner_img_desktop'][$lang['id_lang']] = Tools::getValue('banner_img_desktop_'.(int)$lang['id_lang'], Configuration::get('SLEEDBANNER_IMG_DESKTOP', $lang['id_lang']) ? Configuration::get('SLEEDBANNER_IMG_DESKTOP', $lang['id_lang']) : $this->default_image_desktop);
			$fields['banner_img_mobile'][$lang['id_lang']] = Tools::getValue('banner_img_mobile_'.(int)$lang['id_lang'], Configuration::get('SLEEDBANNER_IMG_MOBILE', $lang['id_lang']) ? Configuration::get('SLEEDBANNER_IMG_MOBILE', $lang['id_lang']) : $this->default_image_mobile);
            $fields['banner_alt'][$lang['id_lang']] = Tools::getValue('banner_alt_'.(int)$lang['id_lang'], Configuration::get('SLEEDBANNER_ALT', $lang['id_lang']));
		}

		return $fields;
    }

    public function handleIssetImage($req_file_input, $imageManager, $lang)
    {
        if (
            isset($_FILES[$req_file_input . $lang['id_lang']]) &&
            isset($_FILES[$req_file_input . $lang['id_lang']]['tmp_name']) &&
            $imageManager->isCorrectImageFileExt($_FILES[$req_file_input.$lang['id_lang']]['name']) &&
            !empty(@getimagesize($_FILES[$req_file_input . $lang['id_lang']]['tmp_name']))
        ) return true;

        return false;
    }

    public function handleUploadImage($req_file_input, $imageManager, $lang, $values_per_lang)
    {       
        $type = Tools::strtolower(Tools::substr(strrchr($_FILES[$req_file_input.$lang['id_lang']]['name'], '.'), 1)); // Used for image reize method
        $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
        $salt = sha1(microtime());
        
        if ($error = ImageManager::validateUpload($_FILES[$req_file_input.$lang['id_lang']])) {
            $errors[] = $error;
        }
        
        elseif (!$temp_name || !move_uploaded_file($_FILES[$req_file_input.$lang['id_lang']]['tmp_name'], $temp_name)) {
            return false;
        }
        
        elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/'.$salt.'_'.$_FILES[$req_file_input.$lang['id_lang']]['name'], null, null, $type)) {
            $errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
        }
        
        if (isset($temp_name)) @unlink($temp_name);
        
        return $salt.'_'.$_FILES[$req_file_input.$lang['id_lang']]['name'];

    }
    

    // FRONT-OFFICE

    public function hookDisplayHeader()
    {
        if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'index') return;
		
        $this->context->controller->addCSS($this->_path . 'views/css/sleedbanner.css');
    }   

    public function hookDisplayTopColumn()
    {
        if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'index')
			return;
            
        $image_desktop = Configuration::get('SLEEDBANNER_IMG_DESKTOP', $this->context->language->id) ? Configuration::get('SLEEDBANNER_IMG_DESKTOP', $this->context->language->id) : $this->default_image_desktop;
        $image_path_desktop = $this->getPathUri().'/views/img/'.$image_desktop;

        $image_mobile = Configuration::get('SLEEDBANNER_IMG_MOBILE', $this->context->language->id) ? Configuration::get('SLEEDBANNER_IMG_MOBILE', $this->context->language->id) : $this->default_image_mobile;
        $image_path_mobile = $this->getPathUri().'/views/img/'.$image_mobile;

        $this->context->smarty->assign(array(
            'image_path_desktop' => $image_path_desktop,
            'image_path_mobile' => $image_path_mobile,
            'image_alt' => Configuration::get('SLEEDBANNER_ALT', $this->context->language->id)
        ));

        return $this->display(__FILE__, 'views/templates/hook/sleedbanner.tpl');
    }


    // CUSTOM HOOK
    // public function hookDisplayBanner()
    // {
    //     return $this->display(__FILE__, 'sleedbanner.tpl');
    // }

}