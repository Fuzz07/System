<!-- Elegant Logout Confirmation Modal -->
<div id="logoutConfirmModal" class="logout-confirm-overlay">
  <div class="logout-confirm-card">
    <div class="logout-confirm-icon">
      <i class="bi bi-box-arrow-left"></i>
    </div>
    <h3 class="logout-confirm-title">Sign Out?</h3>
    <p class="logout-confirm-text">Are you sure you want to sign out of the Supreme Student Council Portal?</p>
    <div class="logout-confirm-actions">
      <button type="button" class="btn-logout-cancel" id="btnCancelLogout">Cancel</button>
      <button type="button" class="btn-logout-confirm" id="btnConfirmLogout">Sign Out</button>
    </div>
  </div>
</div>

<style>
  .logout-confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 150000; /* Extremely high to overlay headers and sidebars */
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.25s ease;
    padding: 20px;
  }
  .logout-confirm-overlay.show {
    display: flex;
    opacity: 1;
  }
  .logout-confirm-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 24px;
    padding: 32px 24px;
    width: 100%;
    max-width: 360px;
    text-align: center;
    box-shadow: 0 20px 48px rgba(15, 23, 42, 0.16);
    transform: scale(0.9);
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  .logout-confirm-overlay.show .logout-confirm-card {
    transform: scale(1);
  }
  .logout-confirm-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin: 0 auto 16px;
  }
  .logout-confirm-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
  }
  .logout-confirm-text {
    font-size: 0.88rem;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 24px;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
  }
  .logout-confirm-actions {
    display: flex;
    gap: 12px;
  }
  .btn-logout-cancel {
    flex: 1;
    background: #f1f5f9;
    color: #475569;
    border: none;
    padding: 12px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
  }
  .btn-logout-cancel:hover {
    background: #e2e8f0;
    color: #334155;
  }
  .btn-logout-confirm {
    flex: 1;
    background: #ef4444;
    color: #ffffff;
    border: none;
    padding: 12px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
  }
  .btn-logout-confirm:hover {
    background: #dc2626;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('logoutConfirmModal');
    const btnCancel = document.getElementById('btnCancelLogout');
    const btnConfirm = document.getElementById('btnConfirmLogout');
    let activeForm = null;

    // Intercept all forms submitting to the logout route
    document.addEventListener('submit', (e) => {
      const action = e.target.getAttribute('action') || '';
      if (action.includes('logout')) {
        // Stop initial submission
        e.preventDefault();
        activeForm = e.target;
        showModal();
      }
    });

    // Also support click intercept on element buttons/links that might trigger sign out
    document.addEventListener('click', (e) => {
      const logoutBtn = e.target.closest('.logout-btn') || e.target.closest('#tab-logout');
      if (logoutBtn) {
        const form = logoutBtn.closest('form');
        if (form) {
          e.preventDefault();
          activeForm = form;
          showModal();
        }
      }
    });

    function showModal() {
      modal.style.display = 'flex';
      // Force reflow
      modal.offsetHeight;
      modal.classList.add('show');
    }

    function hideModal() {
      modal.classList.remove('show');
      setTimeout(() => {
        modal.style.display = 'none';
        activeForm = null;
      }, 250);
    }

    btnCancel.addEventListener('click', hideModal);

    btnConfirm.addEventListener('click', () => {
      if (activeForm) {
        activeForm.submit();
      }
    });

    // Close on overlay click
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        hideModal();
      }
    });
  });
</script>
