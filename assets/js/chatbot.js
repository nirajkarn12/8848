document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.chatbot-toggle');
    const chatWidget = document.querySelector('.chatbot-widget');
    const closeBtn = document.querySelector('.chatbot-close');
    const sendBtn = document.querySelector('.chatbot-send');
    const chatInput = document.querySelector('.chatbot-input input');
    const chatMessages = document.querySelector('.chatbot-messages');
    
    // Generate a unique session ID
    const sessionId = 'chat-' + Math.random().toString(36).substr(2, 9);
    
    // Toggle chat widget
    toggleBtn.addEventListener('click', function() {
        chatWidget.classList.toggle('active');
    });
    
    // Close chat widget
    closeBtn.addEventListener('click', function() {
        chatWidget.classList.remove('active');
    });
    
    // Send message
    async function sendMessage() {
        const message = chatInput.value.trim();
        if (message) {
            // Add user message
            addMessage(message, 'user');
            chatInput.value = '';
            
            // Show typing indicator
            showTypingIndicator();
            
            try {
                // Get bot response from server
                const response = await fetchBotResponse(message);
                addMessage(response, 'bot');
            } catch (error) {
                console.error('Chatbot error:', error);
                addMessage("I'm having trouble connecting. Please try again later.", 'bot');
            }
        }
    }
    
    // Fetch bot response from server
    async function fetchBotResponse(message) {
        const response = await fetch('includes/chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                sessionId: sessionId
            })
        });
        
        const data = await response.json();
        return data.response;
    }
    
    // Add message to chat
    function addMessage(text, sender) {
        // Remove typing indicator if present
        const typingIndicator = chatMessages.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
        
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('chatbot-message', sender);
        messageDiv.innerHTML = `<p>${text}</p>`;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Show typing indicator
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.classList.add('typing-indicator');
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Send message on button click or Enter key
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Add initial bot greeting
    setTimeout(() => {
        addMessage("Hi there! 👋 How can I help you with your crafting needs today?", 'bot');
    }, 1000);
});