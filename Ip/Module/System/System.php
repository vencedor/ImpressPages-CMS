<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Module\System;


class System{
    private static $error404;
    private static $enableError404Output = true;
    
    public function __construct() {
        self::$error404 = false;
    }

    public function init(){
        global $site;
        global $dispatcher;

        if ($site->managementState()) {
            $site->addJavascript(\Ip\Config::coreModuleUrl('System/public/system.js'), 0);
        }

        $dispatcher->bind('site.error404', __NAMESPACE__ .'\System::catchError404');
        $dispatcher->bind(\Ip\Event\UrlChanged::URL_CHANGED, __NAMESPACE__ .'\System::urlChanged');
    }
    
    /**
     * 
     * Enable or disable error404 message display on 'main' content block. 
     * @param bool $value - enabled / disabled
     */
    public static function enableError404Output($value){
        self::$enableError404Output = $value;
    }


    public static function urlChanged (\Ip\Event\UrlChanged $event)
    {
        \DbSystem::replaceUrls($event->getOldUrl(), $event->getNewUrl());
    }
    
    public static function catchError404 (\Ip\Event $event) {
        global $parametersMod;
        global $log;
        global $site;
        global $dispatcher;
        
        $log->log("system", "error404", $site->getCurrentUrl()." ".self::error404Message());
        
        self::$error404 = true;
        $dispatcher->bind('site.generateBlock', __NAMESPACE__ .'\System::generateError404Content');

        if(
            $parametersMod->getValue('Config.send_to_main_page')
            &&
           ($site->languageUrl != '' || $site->zoneUrl != '' || sizeof($site->getUrlVars()) > 0 || sizeof($site->getGetVars()) > 0 )
        ){
            \Ip\Response::redirect(\Ip\Config::baseUrl(''));

            // TODOX make it not necessary
            $site->setOutput(null);
        }else{
            \Ip\Response::pageNotFound();
        }
    }
    
    public static function generateError404Content(\Ip\Event $event) {
        global $site;
        global $parametersMod;
        
        $blockName = $event->getValue('blockName');
        if ($blockName != 'main' || !self::$enableError404Output) {
            return;
        }
        
        $data = array(
            'title' => $parametersMod->getVAlue('Config.error_title'),
            'text' => self::error404Message()
        );
        $content = \Ip\View::create(\Ip\Config::coreModuleFile('Config/view/error404.php'), $data)->render();
        $event->setValue('content', $content );
        $event->addProcessed();
        
        
    }
    

    /**
     * Find the reason why the user come to non-existent URL
     * @return string error message
     */
    private static function error404Message(){
        global $parametersMod;
        global $site;

        //find reason
        $message = '';
        if(!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == ''){
            $message = __('Config.error_mistyped_url', 'ipPublic');
        }else{
            if(strpos($_SERVER['HTTP_REFERER'], \Ip\Config::baseUrl('')) < 5 && strpos($_SERVER['HTTP_REFERER'], \Ip\Config::baseUrl('')) !== false){
                $message = '<p>' . __('Config.error_broken_link_inside', 'ipPublic') . '</p>';
            } elseif(strpos($_SERVER['HTTP_REFERER'], \Ip\Config::baseUrl('')) === false) {
                $message = '<p>' . __('Config.error_broken_link_outside', 'ipPublic') . '</p>';
            }
        }
        //end find reason
        return $message;
    }



}

