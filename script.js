// Auth modal functionality
const authModal = document.getElementById('authModal');
const closeModalButton = document.getElementById('closeModalButton');
const loginTab = document.getElementById('loginTab');
const signupTab = document.getElementById('signupTab');
const loginFormContainer = document.getElementById('loginFormContainer');
const signupFormContainer = document.getElementById('signupFormContainer');
const switchToSignup = document.getElementById('switchToSignup');
const switchToLogin = document.getElementById('switchToLogin');

// Open auth modal when clicking login/register buttons
document.getElementById('login-modal-btn').addEventListener('click', (e) => {
    e.preventDefault();
    authModal.style.display = 'flex';
    showLoginForm();
});

document.getElementById('register-modal-btn').addEventListener('click', (e) => {
    e.preventDefault();
    authModal.style.display = 'flex';
    showSignupForm();
});

// Close modal
closeModalButton.addEventListener('click', () => {
    authModal.style.display = 'none';
});

// Tab switching
loginTab.addEventListener('click', showLoginForm);
signupTab.addEventListener('click', showSignupForm);
switchToSignup.addEventListener('click', (e) => {
    e.preventDefault();
    showSignupForm();
});
switchToLogin.addEventListener('click', (e) => {
    e.preventDefault();
    showLoginForm();
});

function showLoginForm() {
    loginTab.classList.add('active');
    signupTab.classList.remove('active');
    loginFormContainer.classList.add('active');
    signupFormContainer.classList.remove('active');
}

function showSignupForm() {
    signupTab.classList.add('active');
    loginTab.classList.remove('active');
    signupFormContainer.classList.add('active');
    loginFormContainer.classList.remove('active');
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === authModal) {
        authModal.style.display = 'none';
    }
});

// Remove the old form submission handlers since forms will submit to PHP