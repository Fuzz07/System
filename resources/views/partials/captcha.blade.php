@php
    $recaptchaSiteKey = trim(env('RECAPTCHA_SITE_KEY', ''));
    
    // Detect if keys are empty or set to common development placeholders
    $isPlaceholder = empty($recaptchaSiteKey) || 
                     str_contains(strtolower($recaptchaSiteKey), 'your-google') || 
                     str_contains(strtolower($recaptchaSiteKey), 'your_actual') || 
                     str_contains(strtolower($recaptchaSiteKey), 'placeholder') || 
                     str_contains(strtolower($recaptchaSiteKey), 'your-key') ||
                     str_contains($recaptchaSiteKey, '6LdXXXXXXXX');

    $isAndroidApp = str_contains(request()->userAgent() ?? '', 'SSCStudentApp');
@endphp

@if($isAndroidApp)
    <!-- Mobile App: Completely bypass CAPTCHA (No visuals, auto-validated) -->
    <input type="hidden" name="captcha_verified_token" id="captcha_verified_token" value="local_verified_token" />
@elseif(!$isPlaceholder)
    <!-- Official Google reCAPTCHA v2 Widget (Automatic Render) -->
    <div class="captcha-wrapper mb-4 d-flex justify-content-center">
        <div class="g-recaptcha" 
             data-sitekey="{{ $recaptchaSiteKey }}" 
             data-callback="onRecaptchaSuccess" 
             data-expired-callback="onRecaptchaExpired"></div>
    </div>
    
    <input type="hidden" name="captcha_verified_token" id="captcha_verified_token" value="" />

    <!-- Google reCAPTCHA v2 Script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onRecaptchaSuccess(token) {
            document.getElementById('captcha_verified_token').value = token;
        }
        function onRecaptchaExpired() {
            document.getElementById('captcha_verified_token').value = "";
        }
    </script>
@else
    <!-- Fallback Custom CAPTCHA (Only for web browsers when Google keys are missing) -->
    <div class="captcha-wrapper mb-4 d-flex justify-content-center">
      <div class="captcha-box d-flex align-items-center justify-content-between p-3" style="width: 300px; border: 1px solid #d1d5db; border-radius: var(--radius-sm); background: #f9fafb;">
        <div class="d-flex align-items-center gap-3">
          <!-- Animated Checkbox Button -->
          <div id="captcha-checkbox-btn" class="d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border: 2px solid #c3c7cb; border-radius: 4px; background: #ffffff; cursor: pointer; transition: all 0.2s;">
            <i id="captcha-check-icon" class="bi bi-check" style="font-size: 1.5rem; color: #10b981; display: none;"></i>
            <div id="captcha-spinner" class="spinner-border text-primary" role="status" style="width: 1rem; height: 1rem; border-width: 0.15em; display: none;"></div>
          </div>
          <span style="font-size: 0.88rem; font-weight: 500; color: #4b5563; user-select: none;">I am not a robot</span>
        </div>
        <div class="text-center" style="line-height: 1;">
          <img src="{{ asset('assets/images/ssc_logo.png') }}" alt="logo" style="width: 32px; height: 32px; opacity: 0.85;">
          <div style="font-size: 0.6rem; color: #9ca3af; margin-top: 4px; font-weight: 600;">Lite CAPTCHA</div>
        </div>
      </div>
    </div>

    <input type="hidden" name="captcha_verified_token" id="captcha_verified_token" value="" />

    <script>
        (() => {
          const checkboxBtn = document.getElementById('captcha-checkbox-btn');
          const checkIcon = document.getElementById('captcha-check-icon');
          const spinner = document.getElementById('captcha-spinner');
          const verifiedTokenInput = document.getElementById('captcha_verified_token');
          const form = document.getElementById('loginForm') || document.getElementById('registerForm');

          let verified = false;

          checkboxBtn.addEventListener('click', () => {
            if (verified) return;

            checkboxBtn.style.borderColor = "#3b82f6";
            spinner.style.display = "block";

            setTimeout(() => {
              spinner.style.display = "none";
              checkIcon.style.display = "block";
              checkboxBtn.style.background = "#f0fdf4";
              checkboxBtn.style.borderColor = "#10b981";
              verified = true;
              verifiedTokenInput.value = "local_verified_token";
            }, 500);
          });

          if (form) {
            form.addEventListener('submit', (e) => {
              if (!verifiedTokenInput.value) {
                e.preventDefault();
                alert('Please check the "I am not a robot" box to proceed.');
              }
            });
          }
        })();
    </script>
@endif
