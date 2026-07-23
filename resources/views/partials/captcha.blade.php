@php
    $recaptchaSiteKey = trim(env('RECAPTCHA_SITE_KEY', ''));
    
    // Detect if keys are empty or set to common development placeholders
    $isPlaceholder = empty($recaptchaSiteKey) || 
                     str_contains(strtolower($recaptchaSiteKey), 'your-google') || 
                     str_contains(strtolower($recaptchaSiteKey), 'your_actual') || 
                     str_contains(strtolower($recaptchaSiteKey), 'placeholder') || 
                     str_contains(strtolower($recaptchaSiteKey), 'your-key') ||
                     str_contains($recaptchaSiteKey, '6LdXXXXXXXX');
@endphp

@if(!$isPlaceholder)
    <!-- Official Google reCAPTCHA v2 Widget (Automatic Render) -->
    <div class="captcha-wrapper mb-4 d-flex justify-content-center">
        <div class="g-recaptcha" 
             data-sitekey="{{ $recaptchaSiteKey }}" 
             data-callback="onRecaptchaSuccess" 
             data-expired-callback="onRecaptchaExpired"></div>
    </div>
    
    <input type="hidden" name="captcha_verified_token" id="captcha_verified_token" value="" />

    <!-- Google reCAPTCHA JavaScript API -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onRecaptchaSuccess(token) {
            const tokenInput = document.getElementById('captcha_verified_token');
            if (tokenInput) {
                tokenInput.value = token;
            }
        }
        function onRecaptchaExpired() {
            const tokenInput = document.getElementById('captcha_verified_token');
            if (tokenInput) {
                tokenInput.value = "";
            }
        }

        // Prevent form submission if reCAPTCHA checkbox is unchecked
        document.addEventListener('DOMContentLoaded', () => {
            const tokenInput = document.getElementById('captcha_verified_token');
            const form = tokenInput ? tokenInput.closest('form') : document.querySelector('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (tokenInput && !tokenInput.value) {
                        e.preventDefault();
                        
                        // Turn off the fullscreen loading overlay if it got triggered
                        const loader = document.getElementById('login-loading-overlay');
                        if (loader) {
                            loader.style.display = 'none';
                        }
                        
                        alert('Please check the "I am not a robot" box to proceed.');
                    }
                });
            }
        });
    </script>
@else
    <!-- Elegant Fallback "I am not a robot" Custom CAPTCHA -->
    <div class="captcha-container mb-4" style="background: rgba(15, 23, 42, 0.03); border: 1.5px solid #cbd5e1; border-radius: var(--radius-sm, 10px); padding: 16px;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="position-relative" style="width: 32px; height: 32px;">
            <input type="checkbox" id="robot_checkbox" class="form-check-input border-secondary-subtle" style="width: 32px; height: 32px; cursor: pointer; border-radius: 6px; border: 2px solid #cbd5e1; background-color: #ffffff !important; accent-color: #4f46e5; margin-top: 0;" />
            <div id="captcha_spinner" class="spinner-border text-primary position-absolute d-none" role="status" style="width: 24px; height: 24px; top: 4px; left: 4px; border-width: 2px;">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div id="captcha_success_icon" class="text-success position-absolute d-none" style="top: 0; left: 0; font-size: 28px; line-height: 1;">
              <i class="bi bi-check-circle-fill"></i>
            </div>
          </div>
          <div>
            <label for="robot_checkbox" class="form-check-label fw-semibold text-slate-700" style="font-size: 0.95rem; cursor: pointer; user-select: none;">I am not a robot</label>
          </div>
        </div>
        <div class="d-flex flex-column align-items-center" style="user-select: none;">
          <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="reCAPTCHA" style="width: 28px; height: 28px; opacity: 0.8;" />
          <span class="text-muted" style="font-size: 0.65rem; font-weight: 500;">reCAPTCHA Lite</span>
        </div>
      </div>
      
      <input type="hidden" name="captcha_verified_token" id="captcha_verified_token" value="" />
      <div id="captcha_feedback" class="mt-2 text-danger fw-semibold d-none" style="font-size: 0.8rem;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
          const robotCheckbox = document.getElementById('robot_checkbox');
          const captchaSpinner = document.getElementById('captcha_spinner');
          const successIcon = document.getElementById('captcha_success_icon');
          const captchaFeedback = document.getElementById('captcha_feedback');
          const verifiedTokenInput = document.getElementById('captcha_verified_token');
          const container = robotCheckbox ? robotCheckbox.closest('.captcha-container') : null;
          const form = container ? container.closest('form') : document.querySelector('form');

          if (!robotCheckbox) return;

          robotCheckbox.addEventListener('change', async () => {
            if (robotCheckbox.checked) {
              robotCheckbox.style.opacity = '0';
              robotCheckbox.disabled = true;
              captchaSpinner.classList.remove('d-none');
              
              setTimeout(async () => {
                try {
                  const response = await fetch("{{ route('captcha.verify') }}");
                  const data = await response.json();

                  captchaSpinner.classList.add('d-none');
                  
                  if (data.success) {
                    robotCheckbox.checked = true;
                    robotCheckbox.style.opacity = '1';
                    successIcon.classList.remove('d-none');
                    verifiedTokenInput.value = data.token;
                    captchaFeedback.classList.add('d-none');
                  } else {
                    robotCheckbox.checked = false;
                    robotCheckbox.style.opacity = '1';
                    robotCheckbox.disabled = false;
                    captchaFeedback.textContent = data.message || 'Verification failed. Please try again.';
                    captchaFeedback.classList.remove('d-none');
                  }
                } catch (error) {
                  console.error(error);
                  captchaSpinner.classList.add('d-none');
                  robotCheckbox.checked = false;
                  robotCheckbox.style.opacity = '1';
                  robotCheckbox.disabled = false;
                  captchaFeedback.textContent = 'Network error occurred. Please try again.';
                  captchaFeedback.classList.remove('d-none');
                }
              }, 1200);
            }
          });

          if (form) {
            form.addEventListener('submit', (e) => {
              if (!verifiedTokenInput.value) {
                e.preventDefault();
                alert('Please check the "I am not a robot" box to proceed.');
              }
            });
          }
        });
    </script>
@endif
