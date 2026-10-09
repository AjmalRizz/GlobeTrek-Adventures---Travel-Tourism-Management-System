

<!-- Global Footer -->
<footer class="py-5 mt-auto">
    <div class="container">
        <div class="row g-4">
            <!-- Company Profile -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3"><i class="bi bi-compass-fill me-2 text-primary"></i>GlobeTrek Adventures</h5>
                <p class="small text-muted mb-4">Your trusted travel partner in Negombo, Sri Lanka. Offering customized tours, beachfront resort bookings, and secure transport services across the teardrop island.</p>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <?php $footer_base = (strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/staff/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : ''; ?>
                    <li><a href="<?php echo $footer_base; ?>about.php">About Us</a></li>
                    <li><a href="<?php echo $footer_base; ?>packages.php">Tour Packages</a></li>
                    <li><a href="<?php echo $footer_base; ?>accommodation.php">Accommodations</a></li>
                    <li><a href="<?php echo $footer_base; ?>transportation.php">Transportation</a></li>
                    <li><a href="<?php echo $footer_base; ?>guides.php">Travel Guides</a></li>
                    <li><a href="<?php echo $footer_base; ?>contact.php">Contact Us</a></li>
                </ul>
            </div>
            
            <!-- Contact Details -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-3">Contact Details</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 small">
                    <li class="d-flex gap-2 align-items-start">
                        <i class="bi bi-geo-alt-fill text-primary"></i>
                        <span>123 Lewis Place, Negombo, Sri Lanka</span>
                    </li>
                    <li class="d-flex gap-2 align-items-center">
                        <i class="bi bi-telephone-fill text-primary"></i>
                        <span>+94 31 222 1234</span>
                    </li>
                    <li class="d-flex gap-2 align-items-center">
                        <i class="bi bi-envelope-fill text-primary"></i>
                        <span>info@globetrek.com</span>
                    </li>
                </ul>
            </div>
            
            <!-- OpenStreetMap Location -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-3">Our Location</h5>
                <div class="rounded overflow-hidden border border-secondary shadow-sm" style="height: 150px;">
                    <iframe 
                        src="https://maps.google.com/maps?q=Negombo,%20Sri%20Lanka&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
        
        <hr class="my-4 border-secondary">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small text-muted">
            <span>&copy; <?php echo date('Y'); ?> GlobeTrek Adventures. All Rights Reserved. (Academic Project)</span>
            <div class="d-flex gap-3">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
            </div>
        </div>
    </div>
</footer>

<!-- Custom Vanilla JS Controllers for UI Components (replacing Bootstrap JS) -->
<script>
    (function() {
        // Mobile Navbar Toggle
        var toggler = document.querySelector('.navbar-toggler');
        if (toggler) {
            toggler.addEventListener('click', function(e) {
                e.stopPropagation();
                var targetId = this.getAttribute('data-bs-target');
                var target = document.querySelector(targetId);
                if (target) {
                    target.classList.toggle('show');
                }
            });
        }

        // Scroll listener to activate modern floating pill navbar shape
        var navbar = document.querySelector('.navbar-custom');
        if (navbar) {
            function handleScroll() {
                if (window.scrollY > 40) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll(); // Check initially in case page loaded already scrolled
        }


        // Dropdown Menu Toggle (e.g. Profile Dropdown)
        var dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var menu = this.nextElementSibling;
                if (menu) {
                    // Close other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(openMenu) {
                        if (openMenu !== menu) {
                            openMenu.classList.remove('show');
                        }
                    });
                    menu.classList.toggle('show');
                }
            });
        });

        // Close dropdowns and mobile menus when clicking elsewhere
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
            var navbarCollapse = document.querySelector('.navbar-collapse.show');
            if (navbarCollapse) {
                navbarCollapse.classList.remove('show');
            }
        });

        // Accordion Collapse Toggles (Itinerary)
        var accordionButtons = document.querySelectorAll('.accordion-button');
        accordionButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-bs-target');
                var target = document.querySelector(targetId);
                if (!target) return;

                var accordion = this.closest('.accordion');
                var isAlreadyOpen = target.classList.contains('show');

                // If inside a parent accordion group, close other tabs
                if (accordion) {
                    var parentId = accordion.getAttribute('id');
                    var siblingButtons = accordion.querySelectorAll('.accordion-button');
                    siblingButtons.forEach(function(sBtn) {
                        sBtn.classList.add('collapsed');
                        sBtn.setAttribute('aria-expanded', 'false');
                        var sTarget = document.querySelector(sBtn.getAttribute('data-bs-target'));
                        if (sTarget) sTarget.classList.remove('show');
                    });
                }

                if (!isAlreadyOpen) {
                    this.classList.remove('collapsed');
                    this.setAttribute('aria-expanded', 'true');
                    target.classList.add('show');
                }
            });
        });

        // Modal Popups (e.g. Forgot Password Modal)
        var modalToggles = document.querySelectorAll('[data-bs-toggle="modal"]');
        modalToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-bs-target');
                var modal = document.querySelector(targetId);
                if (modal) {
                    modal.classList.add('show');
                    modal.style.display = 'block';
                    
                    // Create backdrop overlay
                    var backdrop = document.querySelector('.modal-backdrop');
                    if (!backdrop) {
                        backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
                }
            });
        });

        var modalDismisses = document.querySelectorAll('[data-bs-dismiss="modal"]');
        modalDismisses.forEach(function(dismiss) {
            dismiss.addEventListener('click', function(e) {
                e.preventDefault();
                var modal = this.closest('.modal');
                if (modal) {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                }
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            });
        });

        // Alert Dismissal
        var alertDismisses = document.querySelectorAll('[data-bs-dismiss="alert"]');
        alertDismisses.forEach(function(dismiss) {
            dismiss.addEventListener('click', function(e) {
                e.preventDefault();
                var alert = this.closest('.alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(function() {
                        alert.remove();
                    }, 150);
                }
            });
        });
    })();
</script>
<!-- Theme Toggle Script -->
<script>
    // Script is at end of body — DOM is already ready, no DOMContentLoaded needed
    (function() {
        var themeToggleBtn  = document.getElementById('themeToggleBtn');
        var themeToggleIcon = document.getElementById('themeToggleIcon');

        function updateThemeUI(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                if (themeToggleIcon) {
                    themeToggleIcon.className = 'bi bi-sun-fill';
                }
                if (themeToggleBtn) {
                    themeToggleBtn.style.color = '#f59e0b';
                    themeToggleBtn.style.backgroundColor = 'rgba(245,158,11,0.15)';
                }
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                if (themeToggleIcon) {
                    themeToggleIcon.className = 'bi bi-moon-fill';
                }
                if (themeToggleBtn) {
                    themeToggleBtn.style.color = '#0ea5e9';
                    themeToggleBtn.style.backgroundColor = '#e0f2fe';
                }
            }
        }

        // Apply saved theme on load
        var savedTheme = localStorage.getItem('theme') || 'light';
        updateThemeUI(savedTheme);

        // Toggle on click
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                var current = document.documentElement.getAttribute('data-theme') || 'light';
                var next = (current === 'dark') ? 'light' : 'dark';
                localStorage.setItem('theme', next);
                updateThemeUI(next);
            });
        }
    })();
</script>
<!-- Currency Converter Controller -->
<script>
    (function() {
        const EXCHANGE_RATE = 300; // 1 USD = 300 LKR (fixed demo rate)

        /* ── helpers ── */
        window.formatPrice = function(usdAmount) {
            const currency = localStorage.getItem('site_currency') || 'USD';
            if (currency === 'LKR') {
                const lkr = usdAmount * EXCHANGE_RATE;
                return '₨\u00a0' + lkr.toLocaleString('en-LK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
            return '$' + parseFloat(usdAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        window.updateAllPrices = function() {
            const currency = localStorage.getItem('site_currency') || 'USD';

            // update every price element that carries data-usd
            document.querySelectorAll('.price-display').forEach(function(el) {
                const usd = parseFloat(el.getAttribute('data-usd'));
                if (!isNaN(usd)) {
                    el.textContent = window.formatPrice(usd);
                }
            });

            // update currency suffix badges (USD / LKR)
            document.querySelectorAll('.currency-suffix').forEach(function(el) {
                el.textContent = currency === 'LKR' ? 'LKR' : 'USD';
            });

            // sync select widget
            const sel = document.getElementById('currencySelect');
            if (sel) sel.value = currency;

            // re-calc booking total if the helper exists (package_details.php)
            if (typeof calcTotal === 'function') calcTotal();
        };

        /* ── initialise select widget ── */
        var savedCurrency = localStorage.getItem('site_currency') || 'USD';
        var currencySelect = document.getElementById('currencySelect');
        if (currencySelect) {
            currencySelect.value = savedCurrency;

            // Restyle based on current theme
            function styleCurrencySelect() {
                var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                currencySelect.style.backgroundColor = isDark
                    ? 'rgba(14,165,233,0.15)'
                    : 'var(--primary-light, #e0f2fe)';
                currencySelect.style.color = 'var(--primary, #0e71e9)';
                currencySelect.style.border = 'none';
            }
            styleCurrencySelect();

            currencySelect.addEventListener('change', function() {
                localStorage.setItem('site_currency', this.value);
                window.updateAllPrices();
            });
        }

        /*  run on every page load */
        window.updateAllPrices();
        window.addEventListener('load', window.updateAllPrices);
    })();
</script>
</body>
</html>
