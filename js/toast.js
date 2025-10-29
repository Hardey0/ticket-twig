function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.textContent = message;
  toast.style.cssText = `
    position: fixed; top: 1rem; right: 1rem; z-index: 1000;
    padding: 1rem 1.5rem; border-radius: 8px; color: white;
    font-weight: bold; animation: slideIn 0.3s ease;
    ${type === 'error' ? 'background: #ef4444;' : 'background: #10b981;'}
  `;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }
`;
document.head.appendChild(style);