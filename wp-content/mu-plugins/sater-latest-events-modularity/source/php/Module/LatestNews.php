<?php

namespace ModularityLatestNewsEvents\Module;

/**
 * Class LatestNews
 * @package LatestNews\Module
 */
class LatestNews extends \Modularity\Module
{
    public $slug = 'latest-news';
    public $supports = array();

    /**
     * Modularity wraps module output in a fragment cache (wp object cache; Redis here).
     * The parent default is 7 days (\Modularity\Module::$cacheTtl), so Blade changes
     * do not appear until the entry expires or Redis is flushed.
     */
    public $cacheTtl = 0;

    public function init()
    {
        $this->nameSingular = __('Senaste evenemang', 'modularity-latest-news');
        $this->namePlural = __('Senaste evenemang', 'modularity-latest-news');
        $this->description = __('Visar kommande evenemang. Antal och kolumner styrs i modulens inställningar.', 'modularity-latest-news');
    }

    /**
     * Data array
     * @return array $data
     */
    public function data(): array
    {
        $latest_news = array();

        //Append field config
        $latest_news = array_merge($latest_news, (array) \Modularity\Helper\FormatObject::camelCase(
            $this->getFields()
        ));

        $latest_news['objectType'] = $latest_news['objectType'] ?? 'events'; // deprecated: dropdown is removed but is used in the backend to only show events as of now.
        $latest_news['filteringByCategorie'] = $latest_news['filteringByCategorie'];
        $latest_news['newscategory'] = $latest_news['newscategory'];
        $latest_news['eventcategory'] = $latest_news['eventcategory'];
        $latest_news['numberOfItems'] = $latest_news['numberOfItems'];
        $latest_news['numberOfColumns'] = $latest_news['numberOfColumns'];
        return $latest_news;
    }

    /**
     * Blade Template
     * @return string
     */
    public function template(): string
    {
        return "latest-news.blade.php";
    }

    /**
     * Style - Register & adding css
     * @return void
     */
    public function style()
    {
        
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
