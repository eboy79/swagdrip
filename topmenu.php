<div id="header">
<div class="menu-container">
        <div class="clip-box">
            <span class="menu-text">MENU</span>
        </div>
        <div class="navburger-circle-wrap">
            <div class="navburger-circle">
                <div class="TheNavigationBurger-line top"></div>
                <div class="TheNavigationBurger-line bottom"></div>
            </div>
        </div>
    </div>
        <div id="menu-overlay" class="menu-overlay">
            <div class="menu-content text-start">
                <nav>
                    <?php
                        wp_nav_menu(array(
                            'theme_location' => 'main-menu',
                            'menu_class' => 'primary-menu',
                            'menu_id' => 'main-menu',
                            'container' => false,
                        ));
                    ?>
                </nav>
                <div class="container contact-info text-start">
                    <div class="row">
                        <div class="col">
                            <p class="contact-item">MNRDSGN</p>
                            <p class="contact-item">261 Hicks St.</p>
                            <p class="contact-item">Brooklyn, N.Y, 11201</p>
                            <p class="contact-item">Email: contact@mnrdsgn.com [917] 417-5280</p>                          </p>
                        </div>
                        <div class="col submenu text-end">
                            <a href="https://instagram.com" class="submenu-item">Instagram</a>
                            <a href="https://linkedin.com" class="submenu-item">LinkedIn</a>
                            <a href="https://twitter.com" class="submenu-item">Twitter</a>
                            <a href="https://facebook.com" class="submenu-item">Facebook</a>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
        
                    <!-- Include your logo SVG here -->
                       <div id="svg-container">
        <div id="wide-container"></div>
    </div>
                </div>
            </div>
        </div>
    </div>
