<?php
namespace Config;

/**
 * @be-config-name 系统
 */
class System
{

    /**
     * @be-config-item-driver \Phpbe\System\App\ConfigItem\ConfigItemString
     * @be-config-item-name 网站名称
     */
    public $siteName = 'BE';

    /**
     * 默认首页
     */
    public $homeParams = [ 'controller'=>'Article', 'action'=>'home'];

    /**
     * @be-config-item-driver \Phpbe\System\App\ConfigItem\ConfigItemString
     * @be-config-item-name 首页的标题
     */
    public $homeTitle = '首页';

    /**
     * @be-config-item-driver \Phpbe\System\App\ConfigItem\ConfigItemString
     * @be-config-item-name 首页的 meta keywords
     */
    public $homeMetaKeywords = 'Be easy';

    /**
     * @be-config-item-driver \Phpbe\System\App\ConfigItem\ConfigItemString
     * @be-config-item-name 首页的 meta description
     */
    public $homeMetaDescription = 'Be easy';

    /**
     * @be-config-item-driver \Phpbe\System\App\ConfigItem\ConfigItemString
     * @be-config-item-name 时区
     */
    public $timezone = 'Asia/Shanghai';
}
