<?php

/**
 * Tags plugin for Pico CMS (@see https://github.com/picocms/Pico)
 *
 * Using this plugin, you can use the "Tags" and "Filter" headers in the page meta block
 * in order to filter the "pages" array on certain pages. This creates the possibility
 * to feature index pages which show only posts of a certain type.
 *
 * The "Tags" header accepts a comma-separated list of tags that apply to the current page.
 *
 * The "Filter" header also accepts a comma-separated list of tags, but instead specifies
 * which pages are included when filtering the pages array. The plugin makes a Twig filter
 * available, "apply_tag_filter", which you can use on the pages array in your templates to be
 * able to filter pages matching specific tags. On a page with no "Filter" header this is a no-op,
 * but on a page that specifies the header "Filter: foo, bar", `pages|apply_tag_filter` will
 * return only pages having at least one of those two tags.
 *
 * @author Pontus Horn
 * @link https://pontushorn.me
 * @repository https://github.com/PontusHorn/Pico-Tags
 * @license http://opensource.org/licenses/MIT
 */

class PicoTags extends AbstractPicoPlugin
{

    /**
     * All tags used in all pages.
     */
    protected $allTags = [];

    /**
     * Register the "Tags" and "Filter" meta header fields.
     *
     * @see    Pico::getMetaHeaders()
     * @param  array<string> &$headers list of known meta header fields
     * @return void
     */
    public function onMetaHeaders(&$headers)
    {
        $headers['tags'] = 'Tags';
        $headers['filter'] = 'Filter';
    }

    /**
     * Parse the current page's tags and/or filters into arrays.
     *
     * @see    Pico::getFileMeta()
     * @param  array &$meta parsed meta data
     * @return void
     */
    public function onMetaParsed(&$meta)
    {
        $meta['tags'] = PicoTags::parseTags($meta['tags']);
        $meta['filter'] = PicoTags::parseTags($meta['filter']);
    }

    /**
     * Collect all known tags in an instance variable
     */
    public function onPagesLoaded(&$pages, &$currentPage, &$previousPage, &$nextPage)
    {
        foreach ($pages as $page) {
            $tags = PicoTags::parseTags($page['meta']['tags']);
            if ($page && !empty($tags)) {
                $this->allTags = array_merge($this->allTags, $tags);
            }
        }

        $this->allTags = array_unique($this->allTags);
    }

    /**
     * Filter the input array to pages matching the tag filter specified
     * in the page
     *
     * @param  array $pages data of all known pages
     * @return array filtered pages
     */
    public function applyTagFilter($pages)
    {
        $currentPage = $this->getPico()->getCurrentPage();
        if ($currentPage && !empty($currentPage['meta']['filter'])) {
            $tagsToShow = $currentPage['meta']['filter'];
            $pages = array_filter($pages, function ($page) use ($tagsToShow) {
                $tags = PicoTags::parseTags($page['meta']['tags']);
                return count(array_intersect($tagsToShow, $tags)) > 0;
            });
        }

        return $pages;
    }

    /**
     * Register a Twig function to get all tags used on the site, as well as a Twig filter that
     * filters the passed array of pages to those with tags matching the current page's Filter
     * header.
     */
    public function onTwigRegistration()
    {
        $twig = $this->getPico()->getTwig();
        $twig->addFunction(new Twig_SimpleFunction('get_all_tags', array($this, 'getAllTags')));
        $twig->addFilter(new \Twig\TwigFilter('apply_tag_filter', array($this, 'applyTagFilter')));
    }

    public function getAllTags()
    {
        return $this->allTags;
    }

    /**
     * Get array of tags from metadata string.
     *
     * @param $tags
     * @return array
     */
    private static function parseTags($tags)
    {
        if (!is_string($tags) || mb_strlen($tags) <= 0) {
            return array();
        }

        $tags = explode(',', $tags);

        return is_array($tags) ? array_map('trim', $tags) : array();
    }
}
