<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package       mod_dmelearn
 * @author        Kien Vu, AJ Dunn, CJ Faulkner
 * @copyright     2015 BrightCookie (http://www.brightcookie.com.au), Digital Media e-learning
 * @version       1.0.0
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT); // Course.

if (!$course = $DB->get_record("course", array("id" => $id))) {
    print_error("Course ID is incorrect");
}

require_course_login($course);

header("location: {$CFG->wwwroot}/mod/dmelearn/content/?id={$elmo->id}");

// Header.
$strelmos = get_string('modulenameplural', 'dmelearn');
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/dmelearn/index.php', array('id' => $id));
$PAGE->navbar->add($strelmos);
$PAGE->set_title($strelmos);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($strelmos);

if (!$elmos = get_all_instances_in_course('dmelearn', $course)) {
    notice(get_string('thereareno',
        'moodle',
        get_string('modulenameplural', 'dmelearn')),
        "../../course/view.php?id=$course->id"
    );
    die;
}

// Sections.
$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

$timenow = time();

// Table data.
$table = new html_table();

$table->head = array();
$table->align = array();
if ($usesections) {
    $table->head[] = get_string('sectionname', 'format_' . $course->format);
    $table->align[] = 'center';
}

$table->head[] = get_string('name');
$table->align[] = 'left';
$table->head[] = get_string('description');
$table->align[] = 'left';

$currentsection = '';
$i = 0;
foreach ($elmos as $elmo) {
    $context = get_context_instance(CONTEXT_MODULE, $elmo->coursemodule);

    // Section.
    $printsection = '';
    if ($elmo->section !== $currentsection) {
        if ($elmo->section) {
            $printsection = get_section_name($course, $sections[$elmo->section]);
        }
        if ($currentsection !== '') {
            $table->data[$i] = 'hr';
            $i++;
        }
        $currentsection = $elmo->section;
    }
    if ($usesections) {
        $table->data[$i][] = $printsection;
    }

    // Link.
    if (!$elmo->visible) {
        // Show dimmed if the mod is hidden.
        $table->data[$i][] = "<a class=\"dimmed\" href=\"view.php?id=$elmo->coursemodule\">"
            . format_string($elmo->name, true) . "</a>";
    } else {
        // Show normal if the mod is visible.
        $table->data[$i][] = "<a href=\"view.php?id=$elmo->coursemodule\">"
            . format_string($elmo->name, true) . "</a>";
    }

    // Description.
    $table->data[$i][] = format_text($elmo->intro, $elmo->introformat);

    // Entries info.
    if (!empty($managersomewhere)) {
        $table->data[$i][] = "";
    }

    $i++;
}

echo "<br />";

echo html_writer::table($table);

// Use the new Moodle 2.7+ $event->trigger() for logging.
$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_dmelearn\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

echo $OUTPUT->footer();
