<?php

/**
 * Pimcore
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace Pimcore\View\Helper;

use Pimcore\Model\Document;
use Pimcore\Cache as CacheManager;
use Pimcore\Model\Site;
use Pimcore\Navigation\Page\Uri;

class PimcoreNavigationController
{
    /**
     * @var string
     */
    protected $_htmlMenuIdPrefix;

    /**
     * @var string
     */
    protected $_pageClass = '\\Pimcore\\Navigation\\Page\\Uri';

    /**
     * @param $activeDocument
     * @param null $navigationRootDocument
     * @param null $htmlMenuIdPrefix
     * @param null $pageCallback
     * @param bool|string $cache
     * @return mixed|\Zend_Navigation
     * @throws \Exception
     * @throws \Zend_Navigation_Exception
     */
    public function getNavigation($activeDocument, $navigationRootDocument = null, $htmlMenuIdPrefix = null, $pageCallback = null, $cache = true)
    {
        $cacheEnabled = (bool) $cache;
        $this->_htmlMenuIdPrefix = $htmlMenuIdPrefix;

        if (!$navigationRootDocument) {
            $navigationRootDocument = Document::getById(1);
        }

        $cacheKeys = [];

        if (Site::isSiteRequest()) {
            $site = Site::getCurrentSite();
            $cacheKeys[] = "site__" . $site->getId();
        }


        $cacheKeys[] = "root_id__" . $navigationRootDocument->getId();
        if (is_string($cache)) {
            $cacheKeys[] = "custom__" . $cache;
        }

        if ($pageCallback instanceof \Closure) {
            $cacheKeys[] = "pageCallback_" . closureHash($pageCallback);
        }

        $cacheKey = "nav_" . md5(serialize($cacheKeys));
        $navigation = CacheManager::load($cacheKey);

        if (!$navigation || !$cacheEnabled) {
            $navigation = new \Zend_Navigation();

            if ($navigationRootDocument->hasChilds()) {
                $rootPage = $this->buildNextLevel($navigationRootDocument, true, $pageCallback);
                $navigation->addPages($rootPage);
            }

            // we need to force caching here, otherwise the active classes and other settings will be set and later
            // also written into cache (pass-by-reference) ... when serializing the data directly here, we don't have this problem
            if ($cacheEnabled) {
                CacheManager::save($navigation, $cacheKey, ["output", "navigation"], null, 999, true);
            }
        }

        // set active path
        $front = \Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        // try to find a page matching exactly the request uri
        $activePages = $navigation->findAllBy("uri", $request->getRequestUri());

        if (empty($activePages)) {
            // try to find a page matching the path info
            $activePages = $navigation->findAllBy("uri", $request->getPathInfo());
        }

        if (empty($activePages)) {
            // use the provided pimcore document
            $activePages = $navigation->findAllBy("realFullPath", $activeDocument->getRealFullPath());
        }

        if (empty($activePages)) {
            // find by link target
            $activePages = $navigation->findAllBy("uri", $activeDocument->getFullPath());
        }

        if (!empty($activePages)) {
            // we found an active document, so we can build the active trail by getting respectively the parent
            foreach ($activePages as $activePage) {
                $this->addActiveCssClasses($activePage, true);
            }
        } else {
            // we don't have an active document, so we try to build the trail on our own
            $allPages = $navigation->findAllBy("uri", "/.*/", true);

            foreach ($allPages as $page) {
                $activeTrail = false;

                if (strpos($activeDocument->getRealFullPath(), $page->getUri() . "/") === 0) {
                    $activeTrail = true;
                }

                if ($page instanceof Uri) {
                    if ($page->getDocumentType() == "link") {
                        if (strpos($activeDocument->getFullPath(), $page->getUri() . "/") === 0) {
                            $activeTrail = true;
                        }
                    }
                }

                if ($activeTrail) {
                    $page->setActive(true);
                    $page->setClass($page->getClass() . " active active-trail");
                }
            }
        }

        return $navigation;
    }

    /**
     * @param \Pimcore\Navigation\Page\Uri $page
     */
    protected function addActiveCssClasses($page, $isActive = false)
    {
        $page->setActive(true);

        $parent = $page->getParent();
        $isRoot = false;
        $classes = "";

        if ($parent instanceof \Pimcore\Navigation\Page\Uri) {
            $this->addActiveCssClasses($parent);
        } else {
            $isRoot = true;
        }

        $classes .= " active";

        if (!$isActive) {
            $classes .= " active-trail";
        }

        if ($isRoot && $isActive) {
            $classes .= " mainactive";
        }


        $page->setClass($page->getClass() . $classes);
    }

    /**
     * @param $pageClass
     * @return $this
     */
    public function setPageClass($pageClass)
    {
        $this->_pageClass = $pageClass;
        return $this;
    }

    /**
     * Returns the name of the pageclass
     *
     * @return String
     */
    public function getPageClass()
    {
        return $this->_pageClass;
    }


    /**
     * @param Document $parentDocument
     * @return Document[]
     */
    protected function getChilds($parentDocument)
    {
        return $parentDocument->getChilds();
    }

    /**
     * @param $parentDocument
     * @param bool $isRoot
     * @param callable $pageCallback
     * @return array
     */
    protected function buildNextLevel($parentDocument, $isRoot = false, $pageCallback = null)
    {
        $pages = array();

        $childs = $this->getChilds($parentDocument);
        if (is_array($childs)) {
            foreach ($childs as $child) {
                $classes = "";

                if ($child instanceof Document\Hardlink) {
                    $child = Document\Hardlink\Service::wrap($child);
                }

                if (($child instanceof Document\Page or $child instanceof Document\Link) and $child->getProperty("navigation_name")) {
                    $path = $child->getFullPath();
                    if ($child instanceof Document\Link) {
                        $path = $child->getHref();
                    }

                    $page = new $this->_pageClass();
                    $page->setUri($path . $child->getProperty("navigation_parameters") . $child->getProperty("navigation_anchor"));
                    $page->setLabel($child->getProperty("navigation_name"));
                    $page->setActive(false);
                    $page->setId($this->_htmlMenuIdPrefix . $child->getId());
                    $page->setClass($child->getProperty("navigation_class"));
                    $page->setTarget($child->getProperty("navigation_target"));
                    $page->setTitle($child->getProperty("navigation_title"));
                    $page->setAccesskey($child->getProperty("navigation_accesskey"));
                    $page->setTabindex($child->getProperty("navigation_tabindex"));
                    $page->setRelation($child->getProperty("navigation_relation"));
                    $page->setDocument($child);

                    if ($child->getProperty("navigation_exclude") || !$child->getPublished()) {
                        $page->setVisible(false);
                    }

                    if ($isRoot) {
                        $classes .= " main";
                    }

                    $page->setClass($page->getClass() . $classes);

                    if ($child->hasChilds()) {
                        $childPages = $this->buildNextLevel($child, false, $pageCallback);
                        $page->setPages($childPages);
                    }

                    if ($pageCallback instanceof \Closure) {
                        $pageCallback($page, $child);
                    }

                    $pages[] = $page;
                }
            }
        }

        return $pages;
    }
}
