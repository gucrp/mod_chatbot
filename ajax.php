<?php
define('AJAX_SCRIPT', true);
require_once('../../config.php');

// 1. Receive Form Data
$sesskey   = required_param('sesskey', PARAM_ALPHANUM);
$cmid      = required_param('cmid', PARAM_INT);
$message   = required_param('message', PARAM_RAW);
//$sessionId = required_param('sessionId', PARAM_TEXT);

// Passthrough Data
$courseData = optional_param('coursedata', '', PARAM_RAW);

// 2. Load Course Module & Login Check
// We MUST load the course module ($cm) first to know where we are.
if (!$cm = get_coursemodule_from_id('chatbot', $cmid)) {
    http_response_code(404);
    die(json_encode(['error' => 'Activity ID not found']));
}

// Get the real Course data from the moodle Database
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

// Access the current logged-in User
global $USER; 

require_login($cm->course, false, $cm); // Login check based on the Course
require_sesskey(); // Security check

#generate session key
$sessionId = "user-{$USER->id}-course-{$course->id}-instance-{$cm->id}";

// 3. Load Chatbot Settings
$chatbot = $DB->get_record('chatbot', ['id' => $cm->instance], '*', MUST_EXIST);
$apiUrl = $chatbot->api_url;
if (empty($apiUrl)) {
    $apiUrl = 'http://127.0.0.1:5000/api/chatbot';
}

// 4. Send to Python
$flaskPayload = [
    'message'    => $message,
    'mode'       => 'chat',
    'sessionId'  => $sessionId,
    'coursedata' => !empty($courseData) ? $courseData : strip_tags($course->summary),
    'courseid'   => $course->id,              
    'coursename' => $course->fullname,        
    'userid'     => $USER->id,               
    'firstname'  => $USER->firstname,   
    'lastname'   => $USER->lastname,
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($flaskPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch); // GET THE TEXT ERROR
$curlErrNo = curl_errno($ch); // GET THE ERROR NUMBER
curl_close($ch);

header('Content-Type: application/json');
if ($httpCode >= 200 && $httpCode < 300 && $response) {
    echo $response;
} else {
    //echo json_encode(['reply' => "Error connecting to AI (Code: $httpCode)"]);
    echo json_encode([
        'reply' => "Connection Error: ($curlErrNo) $curlError. HTTP Code: $httpCode"
    ]);
}