<?php
$ui  = 'home';
$uis = scandir('help/uis');
unset($uis[0]);
unset($uis[1]);
$help = new help();
if (!empty($_GET['ui'])) {
    $ui = $help::secure($_GET['ui']);
    $help->currp = $ui;
}
if (!in_array($ui, $uis)) {
    header("Location: $site_url/404");
    exit();
}
if (in_array($ui, $uis)) {
   $content = $help->Senction("$ui/content");
}
$mode = 'day';
if (!empty($_COOKIE['mode']) && $_COOKIE['mode'] == 'night') {
    $mode = 'night';
} ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo $config['site_name']; ?> | Help Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $config['site_name']; ?>" name="author" />
    <meta content="<?php echo $config['site_desc']; ?>" name="description" />
    <meta name="keywords" content="<?php echo $config['meta_keywords']; ?>"/>

    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo $config['site_url']; ?>/media/img/icon.<?php echo($config['logo_extension']) ?>">
    <!-- Viga Fonts-->
    <link href="<?php echo(help('st/fonts/Viga/viga.css')) ?>" rel="stylesheet" />
    <!-- Bootstrap Css -->
    <link href="<?php echo help('st/css/bootstrap.min.css');?>" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo help('st/css/icons.min.css');?>" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo help('st/css/styles.css');?>" id="app-style" rel="stylesheet" type="text/css" />
      <!-- Bundle js-->
    <script src="<?php echo help('st/libs/bootstrap/js/bundle.js');?>"></script>
    <script type="text/javascript">
        $(function() {
            $(document).on('click', 'a[data-ajax]', function(e) {
                e.preventDefault();
                if (($(this)[0].hasAttribute("data-sent") && $(this).attr('data-sent') == '0') || !$(this)[0].hasAttribute("data-sent")) {
                    if (!$(this)[0].hasAttribute("data-sent")) {
                        $('.nav-menu-body').find('a').removeClass('active');
                        $(this).addClass('active');
                    }
                    window.history.pushState({state:'new'},'', $(this).attr('href'));
                    $(".barloading").css("display","block");
                    if ($(this)[0].hasAttribute("data-sent")) {
                        $(this).attr('data-sent', "1");
                    }
                    var url = $(this).attr('data-ajax');
                    $.post("<?php echo($config['site_url']) ?>/hp_load.php" + url, {url:url}, function (data) {
                        $(".barloading").css("display","none");
                        if ($('#redirect_link')[0].hasAttribute("data-sent")) {
                            $('#redirect_link').attr('data-sent', "0");
                        }
                        json_data = JSON.parse($(data).filter('#json-data').val());
                        $('.page-content').html(data);
                        setTimeout(function () {
                        $(".page-content").getNiceScroll().resize()
                        }, 500);
                        $(".page-content").animate({ scrollTop: 0 }, "slow");
                    });
                }
            });
            $(window).on("popstate", function (e) {
                location.reload();
            });
            $("body").on("contextmenu", "img", function(e) {
		        return false;
		    });
        });
    </script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8462913288699097" crossorigin="anonymous"></script>
</head>

<body <?php echo ($mode == 'night' ? 'class="dark"' : ''); ?> data-spy="scroll" data-target=".right-side-nav" data-offset="175">
<div class="barloading"></div>
<a id="redirect_link" href="" data-ajax="" data-sent="0"></a>

    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="<?php echo help('');?>" class="logo logo-light" data-ajax="?path=">
                            <span class="logo-sm">
                                <img src="<?php echo $config['site_url']; ?>/media/img/icon.<?php echo($config['logo_extension']) ?>" alt="" height="40">
                            </span>
                            <span class="logo-lg">
                                <img src="<?php echo $config['site_url']; ?>/media/img/icon.<?php echo($config['logo_extension']) ?>" alt="" height="40"> <strong class="text-white">&nbsp;<?php echo $config['site_name']; ?></strong><span class="badge badge-soft-info ml-2">Help Center</span>
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect d-lg-none" id="vertical-menu-btn">
                        <i class="mdi mdi-menu"></i>
                    </button>
                </div>
                <div class="d-flex">
                    <div class="d-inline-block">
                        <a href="<?php echo $config['site_url'] ?>/<?php echo $me['username']; ?>" class="header-item" target="__blank">
                            <img src="<?php echo $me['avatar']; ?>" alt="" height="35" class="avatar">&nbsp;
                            <?php echo $me['name']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <!--- Sidemenu -->
                <div class="nav-menu-body" id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">Home</li>

                        <li>
                            <a href="<?php echo help('home'); ?>" class="waves-effect <?php echo $help->activeMenu('home'); ?>" data-ajax="?path=home">
                                <i class="mdi mdi-home"></i>
                                <span>Home</span>
                            </a>
                        </li>

                        <li class="menu-title">Joining</li>

                        <li>
                            <a href="<?php echo help('login'); ?>" class="waves-effect <?php echo $help->activeMenu('login'); ?>" data-ajax="?path=login">
                                <i class="mdi mdi-account-check"></i>
                                <span>Login</span>
                            </a>
                        </li>

                        <li>
                        <a href="<?php echo help('signup'); ?>" class="waves-effect <?php echo $help->activeMenu('signup'); ?>" data-ajax="?path=signup">
                                <i class="mdi mdi-account-arrow-left"></i>
                                <span>Sign up</span>
                            </a>
                        </li>          

                        <li class="menu-title">Account</li>

                        <li>
                        <a href="<?php echo help('account-delection'); ?>" class="waves-effect <?php echo $help->activeMenu('account-delection'); ?>" data-ajax="?path=account-delection">
                                <i class="mdi mdi-account-remove"></i>
                                <span>Delection</span>
                            </a>
                        </li>

                        <li class="menu-title">information</li>

                        <li>
                        <a href="<?php echo help('about-us'); ?>" class="waves-effect <?php echo $help->activeMenu('about-us'); ?>" data-ajax="?path=about-us">
                                <i class="mdi mdi-help-circle"></i>
                                <span>About</span>
                            </a>
                        </li>

                        <li>
                        <a href="<?php echo help('terms-of-use'); ?>" class="waves-effect <?php echo $help->activeMenu('terms-of-use'); ?>" data-ajax="?path=terms-of-use">
                                <i class="mdi mdi-information"></i>
                                <span>Terms</span>
                            </a>
                        </li>

                        <li>
                        <a href="<?php echo $config['site_url']; ?>/contact_us" class="waves-effect" target="__blank">
                                <i class="mdi mdi-bread-slice"></i>
                                <span>Contact</span>
                            </a>
                        </li>

                        <li>
                        <a href="<?php echo help('privacy-policy'); ?>" class="waves-effect <?php echo $help->activeMenu('privacy-policy'); ?>" data-ajax="?path=privacy-policy">
                                <i class="mdi mdi-briefcase-minus"></i>
                                <span>Privacy & policy</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- Start right Content here -->
        <div class="main-content">
            
            <div class="page-content">
                <?php echo $content; ?>
            </div>
            <!-- End Page-content -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>document.write(new Date().getFullYear())</script> © <?php echo $config['site_name']; ?>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-right d-none d-sm-block">
                                <i class="mdi mdi-heart text-danger"></i>
                                <i class="mdi mdi-heart text-danger"></i>
                                <i class="mdi mdi-heart text-danger"></i>
                                <i class="mdi mdi-heart text-danger"></i>
                                <i class="mdi mdi-heart text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="<?php echo help('st/libs/jquery/jquery.min.js');?>"></script>
    <script src="<?php echo help('st/libs/simplebar/simplebar.min.js');?>"></script>
    <script src="<?php echo help('st/libs/jquery.easing/jquery.easing.min.js');?>"></script>
    <script src="<?php echo help('st/js/app.js');?>"></script>
</body>
</html>