const SESSION_KEY = 'ticketapp_session';

document.addEventListener('DOMContentLoaded', () => {
  const path = window.location.pathname;
  const protected = ['/dashboard', '/tickets'];

  if (protected.some(p => path.startsWith(p)) && !localStorage.getItem(SESSION_KEY)) {
    window.location = '/auth/login';
  }
});

function login(email, password) {
  if (email === 'user@example.com' && password === 'password') {
    localStorage.setItem(SESSION_KEY, 'mock-jwt-123');
    window.location = '/dashboard';
  } else {
    showToast('Invalid credentials', 'error');
  }
}

function logout() {
  localStorage.removeItem(SESSION_KEY);
  window.location = '/';
}