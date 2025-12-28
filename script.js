document.addEventListener('DOMContentLoaded', function () {
    // Get all chatbot containers on the page
    const chatbotContainers = document.querySelectorAll('.chatbot-container');

    chatbotContainers.forEach(container => {
        // Extract unique identifiers. The container ID is "chatbot-container-{cmid}" based on view.php
        const instanceId = container.id.split('-').pop(); 
        const cmid = container.dataset.cmid; 

        // Get UI Elements
        const messagesContainer = document.getElementById(`chatbot-messages-${instanceId}`);
        const inputField = document.getElementById(`chatbot-input-${instanceId}`);
        const sendButton = document.getElementById(`chatbot-send-${instanceId}`);

        // Get Hidden Data Inputs (Context for the LLM)
        const courseData = document.getElementById(`chatbot-coursedata-${instanceId}`).value;
        const sessionKeyVal = document.getElementById(`sesskey-${instanceId}`).value;

        // Apply Placeholder Text
        const placeholderString = inputField.getAttribute('placeholder_str');
        if (placeholderString) {
            inputField.setAttribute('placeholder', placeholderString.trim());
        }

        /**
         * Appends a message to the chatbot conversation.
         * Handles Markdown parsing for Bot messages.
         * @param {string} text The message text.
         * @param {string} sender 'user' or 'bot'.
         */
        function appendMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('chatbot-message', sender);

            const messageSpan = document.createElement('div');
            messageSpan.classList.add('chatbot-text');

            if (sender === 'bot') {
                // --- MARKDOWN RENDERING LOGIC ---

                if (text.includes('typing-dots')) {
                    messageSpan.innerHTML = text; // Render raw HTML immediately
                } else {

                // 1. Clean up "lazy" markdown from LLMs (fix missing spaces in lists)
                let cleanText = text.replace(/^\*(\S)/gm, '* $1'); 
                cleanText = cleanText.replace(/^\*\*(\S)/gm, '** $1');

                // 2. Check if marked library is loaded
                if (typeof marked !== 'undefined') {
                    messageSpan.innerHTML = marked.parse(cleanText, { breaks: true });
                } else if (window.marked) {
                    messageSpan.innerHTML = window.marked.parse(cleanText, { breaks: true });
                } else {
                    // Fallback if library failed to load
                    console.warn('Marked library not loaded.');
                    messageSpan.innerText = cleanText;
                }
            }
            } else {
                // User messages are always plain text (security)
                messageSpan.innerText = text;
            }

            messageDiv.appendChild(messageSpan);
            messagesContainer.appendChild(messageDiv);

            // Scroll to the bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        /**
         * Sends a message to the Moodle Server (Proxy), which forwards it to Python.
         * @param {string} message The user's message.
         */
        async function sendMessageToAPI(message) {
            // Point to the local Moodle PHP script
            const proxyUrl = M.cfg.wwwroot + '/mod/chatbot/ajax.php';

            const formData = new URLSearchParams();
            
            //security
            formData.append('sesskey', sessionKeyVal);
            formData.append('cmid', cmid);
            
            // Logic
            formData.append('message', message);
            
            // Passthrough Context
            formData.append('coursedata', courseData);

            try {
                // Lock UI
                inputField.disabled = true;
                sendButton.disabled = true;
                const loadingHTML = 'Pensando <span class="typing-dots"><span></span><span></span><span></span></span>';
                appendMessage(loadingHTML, 'bot');

                const response = await fetch(proxyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                // Remove the "Thinking..." message
                if (messagesContainer.lastChild && messagesContainer.lastChild.textContent.startsWith("Pensando")) {
                    messagesContainer.lastChild.remove();
                }

                // Check for PHP/Server errors
                if (data.error) {
                    appendMessage("System Error: " + data.error, 'bot');
                } else {
                    // Get reply or fallback
                    const botReply = data.reply || "I'm sorry, I received an empty response.";
                    appendMessage(botReply, 'bot');
                }

            } catch (error) {
                console.error('Error sending message:', error);
                
                // Remove the "Thinking..." message if it's still there
                if (messagesContainer.lastChild && messagesContainer.lastChild.textContent.startsWith("Pensando")) {
                    messagesContainer.lastChild.remove();
                }
                appendMessage("Error: Could not connect to the server.", 'bot');
            } finally {
                // Unlock UI
                inputField.disabled = false;
                sendButton.disabled = false;
                inputField.focus();
            }
        }

        /**
         * Handles sending a message when the send button is clicked or Enter is pressed.
         */
        function handleSendMessage() {
            const message = inputField.value.trim();
            if (message) {
                appendMessage(message, 'user');
                inputField.value = ''; // Clear input field
                sendMessageToAPI(message);
            }
        }

        // --- Event Listeners ---
        
        if (sendButton) {
            sendButton.addEventListener('click', handleSendMessage);
        }

        if (inputField) {
            inputField.addEventListener('keypress', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Prevent new line
                    handleSendMessage();
                }
            });
            // Initial focus
            inputField.focus();
        }
    });
});