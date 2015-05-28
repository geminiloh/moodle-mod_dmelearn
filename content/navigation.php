<?php

namespace mod_dmelearn\navigation;

// This file is part of moodle-mod_dmelearn for Moodle - http://moodle.org/
//
// moodle-mod_dmelearn is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// moodle-mod_dmelearn is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with moodle-mod_dmelearn. If not, see <http://www.gnu.org/licenses/>.

/**
 * Class Navigation
 *
 * Generates the navigation template for ELMO
 *
 * @package    mod_dmelearn\navigation
 * @author     Chris Barton, Digital Media e-learning
 * @copyright  2015 Chris Barton, Digital Media e-learning
 * @version    1.0.1
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class Navigation {

    /**
     * Constructor to set config variables.
     *
     * @param $config
     */
    public function __construct($config) {
        $this->show_summary     = $config['show_summary'];
        $this->navigation       = $config['navigation'];
        $this->site_url         = $config['site_url'];
        $this->course           = $config['course'];
        $this->module           = $config['module'];
        $this->page             = $config['page'];
    }

    protected $show_summary;
    protected $navigation;
    protected $site_url;
    protected $course;
    protected $module;
    protected $page;

    /**
     * Make the navigation for a DMELEARN course
     *
     * @return string Returns a HTML string with the navigation layout ready to be injected into a view
     */
    public function make() {
        // BC: changed to use Moodle ULR in naviagtion.
        global $lmscontenturl;

        // If 'show_summary' is 'true' then append it to the navigation.
        // This was a dynamic thing in ELMO where elements were added to the navigation ad-hoc.
        // Some really old courses relied on this and its become a legacy support requirement.

        if (strtolower($this->show_summary) == 'true' ) {
            $this->navigation['assessment_summary'] = array(
                'title' => 'Assessment summary',
                'pages' => array('assessment' => 'Assessment Summary'));
        }

        $navigation = $this->navigation; // A decoded navigation.json array
        $site_url   = $this->site_url; // A string of the url of the Site
        $course     = $this->course; // Current Course
        $module     = $this->module; // Current module
        $page       = $this->page; // Current page

        $return_string = "<div class='course_nav'><ul class='modules nav nav-list'>";

        // We need to get current - page && module.
        $keys = array_keys($navigation);
        // Need to work out where and how to get the current navigation highlighted.
        foreach ($navigation as $module_key => $module_data) {
            // If there is only one page in the module then it links straight to the page.
            if (count($module_data['pages']) == 1) {
                // The page name.
                $page_key = key($module_data['pages']);
                $return_string .= "<li class='module";
                // The module is active.
                if ($module_key == $module) {
                    $return_string .= " active";
                }
                $return_string .= "'>"
                    . "<a href='"
                    . $lmscontenturl."&module="
                    . $module_key."&page="
                    . $page_key
                    . "'>"
                    . $module_data['title']
                    . "</a></li>";
            } else {
                $page_key = key($module_data['pages']);
                // More than 1 page in the module.
                $return_string .= "<li class='module ";
                if ($module_key === $module) {
                    $return_string .= "active";
                }
                $return_string .= "'>"
                    . "<a href='"
                    . $lmscontenturl."&module="
                    . $module_key."&page="
                    . $page_key
                    . "'>"
                    . $module_data['title']
                    . "</a>";
                
                // The module is active.
                if ($module_key === $module) {
                // Inner dropdown.
                $return_string .= "<ul class='pages in'>";

                foreach ($module_data['pages'] as $page_key => $page_title) {
                    $return_string .= "<li class='page";

                    if (($module_key === $module) && ($page_key === $page)) {
                        $return_string .= " active";
                    }
                    $return_string .= "'>"
                        . "<a href='"
                        . $lmscontenturl."&module="
                        . $module_key
                        . "&page="
                        . $page_key
                        . "'>"
                        . $page_title
                        . "</a>";
                    
                    $return_string .= "</li>";
                }
                $return_string .= "</ul></li>";
                }
            }
        }
         $return_string .= "</ul></div>";
         return $return_string;
    }
}