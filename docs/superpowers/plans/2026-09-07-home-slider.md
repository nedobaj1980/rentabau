# Homepage slider implementation plan

Approved scope: keep the current orange/black hero typography and fixed content, add manual arrows/dots/swipe, and an admin editor. Optional autoplay must default off and respect reduced motion. Existing PHP version remains the active site.

1. Add tests for defaults, server validation, invalid links/styles, empty slides and persistence. Run them failing first.
2. Add `includes/slider.php` for validated configuration, atomic JSON storage and rendering through `includes/hero-slider.php`. Keep slider settings separate from catalogue settings.
3. Add `assets/css/slider.css` and `assets/js/slider.js`: responsive image carousel, accessible controls, keyboard/swipe, optional pausable autoplay, reduced-motion support and image-error fallback.
4. Add admin-only `admin-slider.php`, linked from the existing admin navigation, with CSRF-protected saving, verified image uploads, editable slides and typography, buttons, appearance, defaults and a live preview. Preview and public page share rendering/styles.
5. Validate PHP/JavaScript, run configuration and public/auth checks, then inspect desktop/mobile and admin save/reload in Chrome where authentication is available. Preserve unrelated settings and back up changed source first.
