<?php
require_once('../../config.php');

// Get the Course Module ID
$id = required_param('id', PARAM_INT);

// Load Course Module, Course, and Chatbot Instance
$cm = get_coursemodule_from_id('chatbot', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$chatbot = $DB->get_record('chatbot', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

// Add this line to enforce permissions defined in access.php
require_capability('mod/chatbot:view', \context_module::instance($cm->id));

// Trigger Viewed Event
$event = \mod_chatbot\event\course_module_viewed::create(array(
    'objectid' => $chatbot->id,
    'context' => context_module::instance($cm->id)
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('chatbot', $chatbot);
$event->trigger();

// Setup Page
$PAGE->set_url('/mod/chatbot/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($chatbot->name));
$PAGE->set_heading(format_string($course->fullname));

// Load CSS and JS
$PAGE->requires->css(new moodle_url('/mod/chatbot/style.css'));
$PAGE->requires->js(new moodle_url('/mod/chatbot/script.js'));

echo $OUTPUT->header();

// --- Output Logic (Adapted from Block logic) ---

// User Data
$firstname = $USER->firstname;
$course_content_data = "ow yeah"; // opcional Example custom data

// API URL from Database
$apiUrl = $chatbot->api_url;

$chat_title = !empty($chatbot->name) ? format_string($chatbot->name) : get_string('chat_title_default', 'mod_chatbot');
$text_placeholder = get_string('chat_placeholder', 'mod_chatbot');
$welcome_msg = get_string('chat_welcome', 'mod_chatbot', $firstname);

// Note: We use $cm->id for unique IDs in the DOM to avoid conflicts if used elsewhere
?>

<div id="chatbot-container-<?php echo $cm->id; ?>" 
     class="chatbot-container" 
     data-cmid="<?php echo $cm->id; ?>">

    <div class="chatbot-header">
        <?php echo $chat_title; ?>
    </div>
    
    <div id="chatbot-messages-<?php echo $cm->id; ?>" class="chatbot-messages">
        <div class="chatbot-message bot">
            <span class="chatbot-text"><?php echo $welcome_msg; ?></span>
        </div>
    </div>
    
    <div class="chatbot-input-area">
        <input type="text" id="chatbot-input-<?php echo $cm->id; ?>" class="chatbot-input" placeholder_str=" <?php echo $text_placeholder; ?> ">
        <button id="chatbot-send-<?php echo $cm->id; ?>" class="chatbot-send">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
    </div>

    <!-- Hidden Inputs for JS Context -->
    <input type="hidden" id="chatbot-coursedata-<?php echo $cm->id; ?>" value="<?php echo s($course_content_data); ?>">
    <input type="hidden" id="sesskey-<?php echo $cm->id; ?>" value="<?php echo sesskey(); ?>">

</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    // Hack: Ensure marked is available globally even if Moodle tries to hide it
    if (typeof marked === 'undefined' && typeof require === 'function') {
        require(['https://cdn.jsdelivr.net/npm/marked/marked.min.js'], function(m) {
            window.marked = m;
        });
    }
</script>

<?php
echo $OUTPUT->footer();