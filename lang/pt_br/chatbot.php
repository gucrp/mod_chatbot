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
 * Portuguese (Brazil) strings for mod_chatbot.
 *
 * @package   mod_chatbot
 * @copyright 2025 Your Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General Plugin Info
$string['pluginname'] = 'Chatbot AI';
$string['modulename'] = 'Chatbot AI';
$string['modulenameplural'] = 'Chatbots AI';
$string['pluginadministration'] = 'Administração do Chatbot';

// Capabilities / Permissions
$string['chatbot:addinstance'] = 'Adicionar uma nova atividade de Chatbot';
$string['chatbot:view'] = 'Visualizar atividade do Chatbot';

// Settings Form (mod_form.php)
$string['settings_header'] = 'Configuração da Inteligência Artificial';
$string['settings_api_url'] = 'URL da API (Endpoint)';
$string['settings_api_url_help'] = 'Insira a URL completa da API do LLM externo (ex: http://127.0.0.1:5000/api/v1/chat).';

// Chat Interface (view.php / script.js)
$string['chat_title_default'] = 'Assistente Virtual';
$string['chat_welcome'] = 'Olá {$a}, como posso ajudar você hoje?';
$string['chat_placeholder'] = 'Digite sua mensagem aqui...';

// Errors (Optional, if you decide to convert hardcoded errors in the future)
$string['error_not_logged_in'] = 'Você precisa estar logado para usar o chat.';
$string['error_connection'] = 'Erro ao conectar com o servidor.';