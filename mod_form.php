<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_chatbot_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;

        $mform = $this->_form;

        // --- General Section ---
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name of the activity
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Intro Editor (Description)
        $this->standard_intro_elements();

        // --- API Settings Section ---
        $mform->addElement('header', 'apisettings', get_string('settings_header', 'mod_chatbot'));

        // API URL
        $mform->addElement('text', 'api_url', get_string('settings_api_url', 'mod_chatbot'), array('size' => '64'));
        $mform->setType('api_url', PARAM_URL);
        $mform->addHelpButton('api_url', 'settings_api_url', 'mod_chatbot');
        
        // Standard Moodle Course Module Elements (Visible, ID, Groups, etc.)
        $this->standard_coursemodule_elements();

        // Submit buttons
        $this->add_action_buttons();
    }
}