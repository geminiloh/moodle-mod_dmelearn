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
 * @version    1.0.0
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

        $return_string = "<ul class='modules nav nav-list' >";

        // We need to get current - page && module.
        $keys = array_keys($navigation);

        // Need to work out where and how to get the current navigation highlighted.
        foreach ($navigation as $module_key => $module_data) {
            if (count($module_data['pages']) == 1) {
                $page_key = key($module_data['pages']);

                $return_string .= "<li class='module accordion-group ";
                if ($module_key == $module) {
                    $return_string .= " active 1";
                }
                $return_string .= "'>"
                    . "<span class='accordion-heading'><a class='accordion-toggle' data-parent='#accordion_menu' href='"
                    . $lmscontenturl."&module="
                    . $module_key."&page="
                    . $page_key
                    . "'>"
                    . $module_data['title']
                    . "</a>"
                    . "</span>";
            } else {
                // More than 1 page in the module.
                $return_string .= "<li class='module  accordion-group ";
                if ($module_key == $module) {
                    $return_string .= "active 2a";
                }
                $return_string .= "'>"
                    . "<span class='accordion-heading'>"
                    . "<a href='----Tobereplaced----' data-target='#"
                    . $module_key."' class='accordion-toggle' data-parent='#accordion_menu'>"
                    . "".$module_data['title']."</a>"
                    . "</span>";
                // Inner list.
                $return_string .= "<ul class='pages accordion-body collapse";

                if ($module_key == $module) {
                    $return_string .= " in ";
                }

                $return_string .= "' id='" . $module_key . "'>";

                $i = 0;
                $moduleurl = "#";
                foreach ($module_data['pages'] as $page_key => $page_title) {
                    $return_string .= "<li class='page ";

                    if (($module_key === $module) && ($page_key === $page)) {
                        $return_string .= "active 2";
                    }
                    $return_string .= "'>"
                        . "<a href='"
                        .$lmscontenturl."&module="
                        . $module_key
                        . "&page="
                        . $page_key
                        . "' class='accordion-toggle' data-parent='#accordion_menu'>"
                        . ""
                        . $page_title
                        ."</a>";

                    $i++;
                    if ($i == 1) {
                        $moduleurl = $lmscontenturl . "&module=" . $module_key . "&page=" . $page_key;
                    }
                    $return_string .= "</li>";
                }
                $return_string .= "</ul>";
                // BC: Replace module href with first subitem link.
                $return_string = str_replace("----Tobereplaced----", $moduleurl, $return_string);
                $return_string .= "</li>";
            }
        }
        $return_string .= "</ul>";
        return $return_string;
    }

    /**
     * Generates the URL for a previous button or any other HTML element you want to use
     *
     * @return array|null
     */
    public function previous_url() {
        // If the array pointer cannot go previous then the pointer head is
        // at the beginning make a clone of the navigation.
        $nav = $this->navigation;

        if ($this->module == 'assessment_summary') {
            $keys = array_keys($nav);
            $end_module = end($keys);
            $n = end($nav);
            $page = array_keys($n['pages']);
            return array('module' => $end_module, 'page' => end($page));
        }

        // Move the internal pointer to its current position and see if we can move it back.
        while( key($nav) !== $this->module) {
            next($nav);
        }

        $current_module = current($nav);

        // Move the internal pointer in the current_module.
        while(key($current_module['pages']) !== $this->page ){
            next($current_module['pages']);
        }

        $current_page = current($current_module['pages']);
        $previous_page = prev($current_module['pages']);

        if (!$previous_page) {
            // Move the module page.
            $previous_module = prev($nav);

            if (!$previous_module) {
                // No previous page or module.
                return null;
            } else {
                // Get the last page in the previous module
                // by pushing the internal module pointer to the end.
                end($previous_module['pages']);
                return array('module' => key($nav), 'page' => key($previous_module['pages']) );
            }
        } else {
            // All good.
            return array('module' => key($nav), 'page' => key($current_module['pages']));
        }
    }

    /**
     * Generates the URL for a next button or any other HTML element
     * you want to use
     *
     * @return array|null
     */
    public function next_url() {
        // Determine where the next pointer should go navigate between the modules
        // if the array pointer cannot go previous then the pointer head is
        // at the beginning.
        if ($this->module == 'assessment_summary') {
            return null;
        }
        // Make a clone of the navigation.
        $nav = $this->navigation;
        
        // Move the internal pointer to its current position and see if we
        // can move it back.
        while (key($nav) !== $this->module) {
            next($nav);
        }

        $current_module = current($nav);
        
        // Move the internal pointer in the current_module.
        while (key($current_module['pages']) !== $this->page) {
            next($current_module['pages']);
        }

        $current_page = current($current_module['pages']);
        $next_page = next($current_module['pages']);

        if (!$next_page) {
            // Move the module page.
            $next_module = next($nav);
            if (!$next_module) {
                // No next page or module.
                // Return null unless there is a summary to show (dynamic page).
                if ($this->show_summary) {
                    return array('module' => 'assessment_summary', 'page' => 'assessment');
                }
                return null;
            } else {
                // Get the first page in the next module
                // by pushing the internal module pointer to the beginning
                // (usually happens by default but don't risk it here and push it to the
                // start by reseting the pointer).
                reset($next_module['pages']);
                return array('module' => key($nav), 'page' => key($next_module['pages']) );
            }
        } else {
            // All good.
            return array('module' => key($nav), 'page' => key($current_module['pages']));
        }
    }
}