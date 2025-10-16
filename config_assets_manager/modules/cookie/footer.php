<!-- Cookie Consent Banner -->
<div id="cookie-consent-banner" class="fixed bottom-4 right-4 bg-gray-800 text-white p-4 rounded-lg shadow-lg max-w-sm" style="display: none;">
    <h3 class="font-bold text-lg"><?php echo t('cookie_consent_title'); ?></h3>
    <p class="text-sm mt-2"><?php echo t('cookie_consent_text'); ?></p>
    <p class="text-xs text-gray-400 mt-2"><?php echo t('cookie_consent_info'); ?></p>
    <div class="mt-4 flex items-center">
        <input type="checkbox" id="accept-cookies" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="accept-cookies" class="ml-2 block text-sm text-gray-300">
            <?php echo t('cookie_consent_accept'); ?>
        </label>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const banner = document.getElementById('cookie-consent-banner');
        const acceptCheckbox = document.getElementById('accept-cookies');

        // Check if the user has already accepted cookies
        if (!localStorage.getItem('cookies-accepted')) {
            banner.style.display = 'block';
        }

        acceptCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Store the acceptance in localStorage
                localStorage.setItem('cookies-accepted', 'true');
                // Hide the banner with a fade-out effect
                banner.classList.add('transition', 'opacity-0', 'duration-500');
                setTimeout(() => {
                    banner.style.display = 'none';
                }, 500);
            }
        });
    });
</script>