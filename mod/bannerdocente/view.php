<?php

require('../../config.php');
require_once('lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$b  = optional_param('b', 0, PARAM_INT);  // Banner instance ID

if ($id) {
    $cm         = get_coursemodule_from_id('bannerdocente', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $bannerdocente  = $DB->get_record('bannerdocente', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($b) {
    $bannerdocente  = $DB->get_record('bannerdocente', array('id' => $b), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $bannerdocente->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('bannerdocente', $bannerdocente->id, $course->id, false, MUST_EXIST);
} else {
    print_error('mustspecifycourse', 'bannerdocente');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Trigger view event
// $event = \mod_bannerdocente\event\course_module_viewed::create(array(
//     'objectid' => $bannerdocente->id,
//     'context' => $context,
// ));
// $event->add_record_snapshot('course', $course);
// $event->add_record_snapshot('bannerdocente', $bannerdocente);
// $event->trigger();

$PAGE->set_url('/mod/bannerdocente/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bannerdocente->name));
$PAGE->set_heading(format_string($course->fullname));

// --- Output ---
echo $OUTPUT->header();

// Fetch image
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_bannerdocente', 'bannerimage', 0, 'sortorder DESC, id ASC', false);
$imageurl = '';
if (!empty($files)) {
    $file = reset($files);
    $imageurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
}

// Render Banner
$style = "background-color: " . s($bannerdocente->backgroundcolor) . ";";
if ($imageurl) {
    $style .= " background-image: url('$imageurl'); background-size: cover; background-position: center;";
}

echo '<div class="bannerdocente-container" style="padding: 2rem; border-radius: 8px; color: white; text-align: center; ' . $style . '">';
echo '<h2 style="margin: 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">' . format_string($bannerdocente->bannertext ? $bannerdocente->bannertext : $bannerdocente->name) . '</h2>';
echo '</div>';

// Intro (Description)
if ($bannerdocente->intro) {
    echo '<div class="bannerdocente-intro box py-3">';
    echo format_module_intro('bannerdocente', $bannerdocente, $cm->id);
    echo '</div>';
}

echo $OUTPUT->footer();
