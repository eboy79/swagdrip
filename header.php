<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<!-- Open Graph Meta -->
<meta property="og:title" content="mnrdsgn.com - WordPress Tech Journal">
<meta property="og:description" content="A journal documenting the deployment and optimization of mnrdsgn.com">
<meta property="og:image" content="https://mnrdsgn.com/assets/social-preview.jpg">
<meta property="og:url" content="https://mnrdsgn.com">
<meta property="og:type" content="website">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="mnrdsgn.com - WordPress Tech Journal">
<meta name="twitter:description" content="A journal documenting the deployment and optimization of mnrdsgn.com">
<meta name="twitter:image" content="https://mnrdsgn.com/assets/social-preview.jpg">

    <link rel="icon" type="image/x-icon" href="<?php echo get_template_directory_uri(); ?>/assets/favicon.ico">
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="site" id="page">
<header id="wrapper-navbar" data-barba="wrapper">
    <div class="container">
        <a href="<?php echo home_url(); ?>" class="logo" id="img">
            <svg  xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 50 55" width="50" height="55" shape-rendering="geometricPrecision">
            <path d="M31.1,25.8c-3-7-9.1-12.9-12-22.1c-0.2-0.6-0.8-0.9-1.3-0.7c-0.3,0.1-0.6,0.4-0.7,0.7c-3.9,12.5-13.8,19-13.8,30.3
	c0,8.1,6.5,14.7,14.7,14.8c2.3,0,4.7-0.5,6.7-1.6c-0.7-1.5-1-3.2-1-4.9c0-5,2.5-8.7,4.8-12.4C29.4,28.6,30.2,27.3,31.1,25.8z
	 M14.6,43.3c-3.8-1.4-6.4-5.1-6.4-9.2c0.1-2.7,0.8-5.4,2.1-7.8c-0.2,1-0.3,2-0.3,3.1c0,4.3,2.5,8.1,6.3,10c0.7,0.4,1.2,1.1,1.2,1.9
	c0,1.2-1,2.2-2.2,2.2C15.1,43.4,14.9,43.4,14.6,43.3z M25.8,42.3c0-7.6,6.6-12,9.3-20.4c0.1-0.4,0.5-0.6,0.9-0.5
	c0.2,0.1,0.4,0.2,0.5,0.5c2.4,7.8,8.3,12.1,9.1,18.7c0.8,6.1-3.8,11.7-9.9,11.6C30.2,52.2,25.8,47.8,25.8,42.3z"/>            </svg>
        </a>


        <!-- Navigation Menu -->
        <nav class="nav-menu" id="nav-menu">
        <button class="navbar-toggler hidden" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

        <?php get_template_part( 'topmenu' ); ?>
        </nav>


</header>
</div>

<div class="header-underline"></div>
