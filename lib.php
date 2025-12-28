<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Adds a new chatbot instance.
 *
 * @param stdClass $chatbot An object from the form in mod_form.php.
 * @return int The id of the newly inserted chatbot record.
 */
function chatbot_add_instance($chatbot) {
    global $DB;

    $chatbot->timecreated = time();
    $chatbot->timemodified = time();

    // Insert into the custom table we defined in install.xml
    return $DB->insert_record('chatbot', $chatbot);
}

/**
 * Updates an existing chatbot instance.
 *
 * @param stdClass $chatbot An object from the form in mod_form.php.
 * @return bool true
 */
function chatbot_update_instance($chatbot) {
    global $DB;

    $chatbot->timemodified = time();
    $chatbot->id = $chatbot->instance;

    return $DB->update_record('chatbot', $chatbot);
}

/**
 * Deletes a chatbot instance.
 *
 * @param int $id The chatbot instance ID.
 * @return bool true
 */
function chatbot_delete_instance($id) {
    global $DB;

    if (!$chatbot = $DB->get_record('chatbot', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('chatbot', array('id' => $chatbot->id));
    return true;
}

/**
 * Supports the activity chooser.
 */
function chatbot_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;
        case FEATURE_BACKUP_MOODLE2: return true;
        default: return null;
    }
}