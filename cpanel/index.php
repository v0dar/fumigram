<?php
$ui  = 'overview';
$uis = scandir('cpanel/uis');

unset($uis[0]);
unset($uis[1]);
$cpanel = new cpanel();
if (!empty($_GET['ui'])) {
    $ui = $cpanel::secure($_GET['ui']);
    $cpanel->currp = $ui;
}
if (!in_array($ui, $uis)) {
    header("Location: $site_url/404");
    exit();
}
if (in_array($ui, $uis)) {
   $content = $cpanel->Senction("$ui/content");
}

$notify_count = $db->where('recipient_id',0)->where('admin',1)->where('seen',0)->getValue(T_NOTIF,'COUNT(*)');
$notifications = $db->where('recipient_id',0)->where('admin',1)->where('seen',0)->orderBy('id','DESC')->get(T_NOTIF);
$old_notifications = $db->where('recipient_id',0)->where('admin',1)->where('seen',0,'!=')->orderBy('id','DESC')->get(T_NOTIF,5);

$mode = 'day';
if (!empty($_COOKIE['mode']) && $_COOKIE['mode'] == 'night') {
    $mode = 'night';
}

$context['is_manager']     = (($me['manager'] == 1) ? true : false) || (($me['admin'] == 1) ? true : false);
define('IS_MANAGER', $context['is_manager']);

$context['is_moderator']   = (($me['moderator'] == 1) ? true : false);
define('IS_MODERATOR', $context['is_moderator']);

$context['IS_DEV']   = (($me['admin'] == 1) ? true : false);
define('IS_DEV', $context['IS_DEV']); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="robots" content="noindex">
        <meta name="googlebot" content="noindex">
        <title><?php echo $config['site_name']; ?> | Secret Cpanel (By <?php echo $config['site_name']; ?>)</title>
        <!-- Load Favicon-->
        <link href="<?php echo $config['site_url']; ?>/media/img/icon.<?php echo($config['logo_extension']) ?>" rel="shortcut icon" type="image/x-icon" />
        <!-- Load Material Icons-->
        <link href="<?php echo(cpanel('si/css/material.css')) ?>" rel="stylesheet" type="text/css">
        <!-- Load Simple DataTables Stylesheet-->
        <link href="<?php echo(cpanel('si/js/dataTable/datatables.min.css')) ?>" rel="stylesheet" />
        <!-- Daterangepicker -->
        <link rel="stylesheet" href="<?php echo(cpanel('si/js/datepicker/daterangepicker.css')) ?>" type="text/css">
        <!-- Viga Fonts-->
        <link href="<?php echo(cpanel('si/font/Viga/viga.css')) ?>" rel="stylesheet" />
        <!-- Load main stylesheet-->
        <link href="<?php echo(cpanel('si/css/styles.css')) ?>" rel="stylesheet" />
        <!-- Load Notification stylesheet-->
        <link href="<?php echo(cpanel('si/css/notify.css')) ?>" rel="stylesheet" />
        <!-- Load jquery bundle-->
        <script src="<?php echo(cpanel('si/js/jquery.js')) ?>"></script>
        <!-- Load Bootstrap JS bundle-->
        <script src="<?php echo cpanel('si/js/bootstrap/bootstrap.bundle.min.js');?>"></script>
        <!-- jquery form Script-->
        <script src="<?php echo(cpanel('si/js/jquery.form.min.js')) ?>"></script>
        <!-- Daterangepicker -->
        <script src="<?php echo(cpanel('si/js/datepicker/daterangepicker.js')) ?>"></script>
        <!-- Apex chart -->
        <script src="<?php echo(cpanel('si/js/charts/apex/apexcharts.js')) ?>"></script>
        <!-- Load DataTables Scripts-->
        <script src="<?php echo(cpanel('si/js/dataTable/datatables.min.js')) ?>"></script>
        <!-- Load Magnific Popup stylesheet-->
        <link href="<?php echo(cpanel('si/js/lightbox/popup.css')) ?>" rel="stylesheet">
        <!-- Load Magnific Popup Script-->
        <script src="<?php echo(cpanel('si/js/lightbox/popup.min.js')) ?>"></script>
        <!-- AJAX The Cpanel Link-->
        <?php if (IS_DEV) { ?>
            <script>function cpanel_ajax_url(path) { return '<?php echo($config['site_url']); ?>/aj/cpanel/' + path; }</script>
        <?php } ?>
        <?php if (IS_MANAGER || IS_MODERATOR) { ?>
            <script>function calling(path) { return '<?php echo($config['site_url']); ?>/aj/cpanel/' + path; }</script>
        <?php } ?>
        <script type="text/javascript">
        $(function() {
            $(document).on('click', 'a[data-ajax]', function(e) {
                $(document).off('click', '.ranges ul li');
                $(document).off('click', '.applyBtn');
                e.preventDefault();
                if (($(this)[0].hasAttribute("data-sent") && $(this).attr('data-sent') == '0') || !$(this)[0].hasAttribute("data-sent")) {
                    if (!$(this)[0].hasAttribute("data-sent")) {
                        $('.navbar-nav').find('a').removeClass('active');
                        $('#drawerAccordion').find('a').removeClass('active');
                        $(this).addClass('active');
                    }
                    window.history.pushState({state:'new'},'', $(this).attr('href'));
                    $(".bar_loading").css("display","block");
                    if ($(this)[0].hasAttribute("data-sent")) {
                        $(this).attr('data-sent', "1");
                    }
                    var url = $(this).attr('data-ajax');
                    $.post("<?php echo($config['site_url']) ?>/cp_load.php" + url, {url:url}, function (data) {
                        $(".bar_loading").css("display","none");
                        if ($('#redirect_link')[0].hasAttribute("data-sent")) {
                            $('#redirect_link').attr('data-sent', "0");
                        }
                        json_data = JSON.parse($(data).filter('#json-data').val());
                        $('.ui-content').html(data);
                        setTimeout(function () {
                            $(".ui-content").getNiceScroll().resize()
                        }, 500);
                        $(".ui-content").animate({ scrollTop: 0 }, "slow");
                    });
                }
            });
            $(window).on("popstate", function (e) {location.reload();});
            });
        </script>
    </head>
    <body <?php echo ($mode == 'night' ? 'class="nav-fixed dark bg-dark"' : 'class="nav-fixed bg-light"'); ?>>
        <div class="bar_loading cpanel_nation"></div>
        <a id="redirect_link" href="" data-ajax="" data-sent="0"></a>
        <nav class="top-app-bar navbar navbar-expand <?php echo ($mode == 'night' ? 'navbar-dark nbg-dark' : 'navbar-light nav-bg-light'); ?>">
            <div class="container-fluid px-4">
                <!-- Drawer toggle button-->
                <button class="btn btn-lg btn-icon order-1 order-lg-0" id="drawerToggle" href="javascript:void(0);"><i class="material-icons">menu</i></button>
                <!-- Navbar brand-->
                <a class="navbar-brand me-auto" href="<?php echo $config['site_url'] ?>">
                <?php if ($mode == 'night') { ?>
                    <img src="<?php echo cpanel('si/img/logo/logo-night.png');?>" alt="..." style="height: 2.5rem" />         
                <?php }else{ ?>
                    <img src="<?php echo cpanel('si/img/logo/logo-day.png');?>" alt="..." style="height: 2.5rem" />
                <?php } ?>
                </a>
                <!-- Navbar items-->
                <div class="d-flex align-items-center mx-3 me-lg-0">
                    <!-- Navbar-->
                    <ul class="navbar-nav d-none d-lg-flex">
                        <li class="nav-item"><a class="nav-link" href="<?php echo $config['site_url']; ?>/help" target="_blank">Help Center</a></li>
                    </ul>
                    <!-- Navbar buttons-->
                    <div class="d-flex">
                        <!-- Notifications and alerts dropdown-->
                        <div class="dropdown dropdown-notifications d-none d-sm-block">
                            <button class="btn btn-lg btn-icon dropdown-toggle me-3" id="dropdownMenuNotifications" type="button" data-bs-toggle="dropdown" aria-expanded="false"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21M19.75,3.19L18.33,4.61C20.04,6.3 21,8.6 21,11H23C23,8.07 21.84,5.25 19.75,3.19M1,11H3C3,8.6 3.96,6.3 5.67,4.61L4.25,3.19C2.16,5.25 1,8.07 1,11Z"></path></svg></button>
                            <ul class="dropdown-menu dropdown-menu-end me-3 mt-3 py-0 overflow-hidden" aria-labelledby="dropdownMenuNotifications">
                                <li><h6 class="dropdown-header bg-<?php echo $me['dash_color']; ?> text-white fw-500 py-3">Notifications</h6></li>
                                <li><hr class="dropdown-divider my-0" /></li>
                                <li>
                                    <a class="dropdown-item unread">
                                        <i class="material-icons leading-icon">notifications_active</i>
                                        <div class="dropdown-item-content me-2">
                                            <div class="dropdown-item-content-text">Notifications feature will be coming out soon. That way we will recieve a notification whenever a user request for verification or other activities done by our users!</div>
                                            <div class="dropdown-item-content-subtext">July 17, 2022 · Feature Notification</div>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-0" /></li>
                                <li>
                                    <a class="dropdown-item py-3">
                                        <div class="d-flex align-items-center w-100 justify-content-end text-primary">
                                            <div class="fst-button small"> By Zero</div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- User profile dropdown-->
                        <div class="dropdown">
                            <button class="btn btn-lg btn-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,8.39C13.57,9.4 15.42,10 17.42,10C18.2,10 18.95,9.91 19.67,9.74C19.88,10.45 20,11.21 20,12C20,16.41 16.41,20 12,20C9,20 6.39,18.34 5,15.89L6.75,14V13A1.25,1.25 0 0,1 8,11.75A1.25,1.25 0 0,1 9.25,13V14H12M16,11.75A1.25,1.25 0 0,0 14.75,13A1.25,1.25 0 0,0 16,14.25A1.25,1.25 0 0,0 17.25,13A1.25,1.25 0 0,0 16,11.75Z"></path></svg></button>
                            <ul class="dropdown-menu ultimate-menu dropdown-menu-end mt-3">
                                <li>
									<a class="menu-option pointer" href="<?php echo $me['url']; ?>" target="_blank">
                                    <i class="material-icons leading-icon">account_circle</i>&nbsp;&nbsp;&nbsp;
										<div>
											<b>Profile</b>
											<p>My main profile</p>
										</div>
                                    </a>
								</li>
                                <?php if ($mode == 'night') { ?>
                                    <li class="turn-mode" onclick="Mode('day')">
                                        <a href="javascript:void(0)" class="menu-option pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 512 512" fill="currentColor"><g><g><path d="m256,432.3c-11.3,0-20.4,9.1-20.4,20.4v27.9c0,11.3 9.1,20.4 20.4,20.4s20.4-9.1 20.4-20.4v-27.9c0-11.3-9.1-20.4-20.4-20.4z"></path><path d="m256,102.5c-84.6,0-153.5,68.8-153.5,153.5 0,84.6 68.8,153.5 153.5,153.5 84.6,0 153.5-68.8 153.5-153.5 0-84.6-68.9-153.5-153.5-153.5zm0,266.1c-62.1,0-112.6-50.5-112.6-112.6 0-62.1 50.5-112.6 112.6-112.6s112.6,50.5 112.6,112.6c0,62.1-50.5,112.6-112.6,112.6z"></path><path d="M256,79.7c11.3,0,20.4-9.1,20.4-20.4V31.4c0-11.3-9.1-20.4-20.4-20.4s-20.4,9.1-20.4,20.4v27.9    C235.6,70.6,244.7,79.7,256,79.7z"></path><path d="m480.6,235.6h-27.9c-11.3,0-20.4,9.1-20.4,20.4 0,11.3 9.1,20.4 20.4,20.4h27.9c11.3,0 20.4-9.1 20.4-20.4 0-11.3-9.1-20.4-20.4-20.4z"></path><path d="m59.3,235.6h-27.9c-11.3,0-20.4,9.1-20.4,20.4 0,11.3 9.1,20.4 20.4,20.4h27.9c11.3,0 20.4-9.1 20.4-20.4 1.42109e-14-11.3-9.1-20.4-20.4-20.4z"></path><path d="m409.5,131.4l19.7-19.7c8-8 8-20.9 0-28.9-8-8-20.9-8-28.9,0l-19.7,19.7c-8,8-8,20.9 0,28.9s20.9,7.9 28.9,0z"></path><path d="m102.5,380.6l-19.7,19.7c-8,8-8,20.9 0,28.9 8,8 20.9,8 28.9,0l19.7-19.7c8-8 8-20.9 0-28.9s-20.9-7.9-28.9,0z"></path><path d="m409.5,380.6c-8-8-20.9-8-28.9,0-8,8-8,20.9 0,28.9l19.7,19.7c8,8 20.9,8 28.9,0 8-8 8-20.9 0-28.9l-19.7-19.7z"></path><path d="m102.5,131.4c8,8 20.9,8 28.9,0 8-8 8-20.9 0-28.9l-19.7-19.7c-8-8-20.9-8-28.9,0-8,8-8,20.9 0,28.9l19.7,19.7z"></path></g></g></svg>&nbsp;&nbsp;&nbsp;
                                            <div>
                                                <b>Day Mode</b>
                                                <p>Turn on the lights</p>
                                            </div>
                                        </a>
                                    </li>
                                <?php } else { ?>
                                    <li class="turn-mode" onclick="Mode('night')">
                                        <a href="javascript:void(0)" class="menu-option pointer">
                                            <svg class="feather feather-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>&nbsp;&nbsp;&nbsp;
                                            <div>
                                                <b>Night Mode</b>
                                                <p>Turn off the lights</p>
                                            </div>
                                        </a>
                                    </li>
                                <?php } ?>
                                <li>
									<a class="menu-option pointer" href="<?php echo cpanel('my-profile'); ?>" data-ajax="?path=my-profile">
                                    <i class="material-icons leading-icon">settings</i>&nbsp;&nbsp;&nbsp;
										<div>
											<b>Settings</b>
											<p>My account settings</p>
										</div>
                                    </a>
								</li>
                                <li>
									<a class="menu-option pointer" href="<?php echo $config['site_url'].'/signout'; ?>">
                                    <i class="material-icons leading-icon">logout</i>&nbsp;&nbsp;&nbsp;
										<div>
											<b>Logout</b>
											<p>Signout from my account</p>
										</div>
                                    </a>
								</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Layout wrapper-->
        <div id="layoutDrawer">
            <!-- Layout navigation-->
            <div id="layoutDrawer_nav">
                <nav class="drawer accordion <?php echo ($mode == 'night' ? 'drawer-dark bg-dark' : 'drawer-light bg-white'); ?>" id="drawerAccordion">
                    <div class="drawer-menu">
                        <div class="nav">
                            <div class="drawer-menu-heading d-sm-none">Account</div>
                            <a class="nav-link d-sm-none" href="#!">
                                <div class="nav-link-icon"><i class="material-icons">notifications</i></div>Notifications
                            </a>
                            <a class="nav-link d-sm-none" href="#!">
                                <div class="nav-link-icon"><i class="material-icons">mail</i></div>Messages
                            </a>
                            <div class="drawer-menu-divider d-sm-none"></div>
                            <div class="drawer-menu-heading">Interface</div>
                            <a class="nav-link <?php echo $cpanel->activeMenu('overview'); ?>" href="<?php echo cpanel('overview');?>" data-ajax="?path=overview">
                                <div class="nav-link-icon"><i class="material-icons">view_compact</i></div>Overview
                            </a>
                            <a class="nav-link <?php echo $cpanel->activeMenu('dashboard'); ?>" href="<?php echo cpanel('dashboard');?>" data-ajax="?path=dashboard">
                                <div class="nav-link-icon"><i class="material-icons">dashboard</i></div>Dashboard
                            </a>
                            <hr>
                            <?php if (IS_MANAGER) { ?>
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#settingsPages" aria-expanded="false" aria-controls="settingsPages">
                                <div class="nav-link-icon"><i class="material-icons">settings</i></div>Settings
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'general-settings' || $ui == 'site-settings' || $ui == 'setup-emails' || $ui == 'storage-settings' || $ui == 'social-login' || $ui == 'upload-settings' || $ui == 'points-system' || $ui == 'credits-system') ? 'show' : ''; ?>" id="settingsPages" aria-labelledby="headingOne" data-bs-parent="#drawerAccordionPages">
                               <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <a class="nav-link <?php echo $cpanel->activeMenu('general-settings'); ?>" href="<?php echo cpanel('general-settings'); ?>" data-ajax="?path=general-settings">General</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('site-settings'); ?>" href="<?php echo cpanel('site-settings'); ?>" data-ajax="?path=site-settings">Information</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('social-login'); ?>" href="<?php echo cpanel('social-login'); ?>" data-ajax="?path=social-login">Social Login</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('setup-emails'); ?>" href="<?php echo cpanel('setup-emails'); ?>" data-ajax="?path=setup-emails">Setup Emails</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('points-system'); ?>" href="<?php echo cpanel('points-system'); ?>" data-ajax="?path=points-system">Points System</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('credits-system'); ?>" href="<?php echo cpanel('credits-system'); ?>" data-ajax="?path=credits-system">Credits System</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('storage-settings'); ?>" href="<?php echo cpanel('storage-settings'); ?>" data-ajax="?path=storage-settings">Storage Settings</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('upload-settings'); ?>" href="<?php echo cpanel('upload-settings'); ?>" data-ajax="?path=upload-settings">File Upload System</a>
                                </nav>
                            </div>
                            <?php } ?>
                            <!-- Drawer link (Users)-->
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#UsersPages" aria-expanded="false" aria-controls="UsersPages">
                                <div class="nav-link-icon"><i class="material-icons">account_circle</i></div>Members
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'manage-users' || $ui == 'online-users' || $ui == 'blacklist' || $ui == 'inactive-users' || $ui == 'business-members' || $ui == 'verification-requests' ) ? 'show' : ''; ?>" id="UsersPages" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <a class="nav-link <?php echo $cpanel->activeMenu('online-users'); ?>" href="<?php echo cpanel('online-users'); ?>" data-ajax="?path=online-users">Online Users</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('manage-users'); ?>" href="<?php echo cpanel('manage-users'); ?>" data-ajax="?path=manage-users">Manage Users</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('inactive-users'); ?>" href="<?php echo cpanel('inactive-users'); ?>" data-ajax="?path=inactive-users">Inactive Users</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('blacklist'); ?>" href="<?php echo cpanel('blacklist'); ?>" data-ajax="?path=blacklist">Blacklist Members</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('verification-requests'); ?>" href="<?php echo cpanel('verification-requests'); ?>" data-ajax="?path=verification-requests">Verification Requests</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('business-members'); ?>" href="<?php echo cpanel('business-members'); ?>" data-ajax="?path=business-members">Manage Business Users</a>
                                </nav>
                            </div>
                            <?php if (IS_MANAGER) { ?>
                            <!-- Drawer link (Language)-->
                            <a class="nav-link <?php echo $cpanel->activeMenu('manage-langs'); ?><?php echo $cpanel->activeMenu('edit-language'); ?>" href="<?php echo cpanel('manage-langs'); ?>" data-ajax="?path=manage-langs">
                                <div class="nav-link-icon"><i class="material-icons">translate</i></div>Languages
                            </a>
                            <?php } ?>
                            <!-- Drawer link (Payments & ads)-->
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#PaymentsPages" aria-expanded="false" aria-controls="PaymentsPages">
                                <div class="nav-link-icon"><i class="material-icons">payment</i></div>Payments & Ads
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'earnings' || $ui == 'payment-settings' || $ui == 'ads-settings' || $ui == 'manage-user-ads' || $ui == 'manage-site-ads') ? 'show' : ''; ?>" id="PaymentsPages" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <?php if (IS_MANAGER) { ?>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('earnings'); ?>" href="<?php echo cpanel('earnings'); ?>" data-ajax="?path=earnings">Earnings</a>
                                    <?php } ?>
                                    <?php if (IS_MANAGER) { ?>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('manage-site-ads'); ?>" href="<?php echo cpanel('manage-site-ads'); ?>" data-ajax="?path=manage-site-ads">Block Ads</a>
                                    <?php } ?>
                                    <?php if (IS_MANAGER) { ?>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('ads-settings'); ?>" href="<?php echo cpanel('ads-settings'); ?>" data-ajax="?path=ads-settings">Campaign Settings</a>
                                    <?php } ?>
                                    <?php if($config['campaign'] == 'on') { ?>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('manage-user-ads'); ?>" href="<?php echo cpanel('manage-user-ads'); ?>" data-ajax="?path=manage-user-ads">Members Campaign</a>
                                    <?php } ?>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('payment-settings'); ?>" href="<?php echo cpanel('payment-settings'); ?>" data-ajax="?path=payment-settings">Payment Configuration</a>
                                </nav>
                            </div>
                            <hr>
                            <!-- Drawer link (Badges)-->
                            <a class="nav-link <?php echo $cpanel->activeMenu('badges'); ?>" href="<?php echo cpanel('badges'); ?>" data-ajax="?path=badges">
                                <div class="nav-link-icon"><i class="material-icons">verified</i></div>Badges
                            </a>
                            <!-- Drawer link (Posts)-->
                            <a class="nav-link <?php echo $cpanel->activeMenu('manage-posts'); ?>" href="<?php echo cpanel('manage-posts'); ?>" data-ajax="?path=manage-posts">
                                <div class="nav-link-icon"><i class="material-icons">pages</i></div>Manage Posts
                            </a>
                            <!-- Drawer link (Story)-->
                            <?php if ($config['story_system'] == 'on') { ?>
                            <a class="nav-link <?php echo $cpanel->activeMenu('manage-stories'); ?>" href="<?php echo cpanel('manage-stories'); ?>" data-ajax="?path=manage-stories">
                                <div class="nav-link-icon"><i class="material-icons">update</i></div>Manage Stories
                            </a>
                            <?php } ?>
                             <!-- Drawer link (Pro)-->
                             <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#ProSystem" aria-expanded="false" aria-controls="ProSystem">
                                <div class="nav-link-icon"><i class="material-icons">stars</i></div>Premium System
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'premium-settings' || $ui == 'premium-users') ? 'show' : ''; ?>" id="ProSystem" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <?php if (IS_MANAGER) { ?>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('premium-settings'); ?>" href="<?php echo cpanel('premium-settings'); ?>" data-ajax="?path=premium-settings">Premium Settings</a>
                                    <?php } ?>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('premium-users'); ?>" href="<?php echo cpanel('premium-users'); ?>" data-ajax="?path=premium-users">Premium Members</a>
                                </nav>
                            </div>
                              <!-- Drawer link (Comments)-->
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#ManageComments" aria-expanded="false" aria-controls="BlogPages">
                                <div class="nav-link-icon"><i class="material-icons">mode_comment</i></div>Manage Comments
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'post-comments' || $ui == 'comments-reply') ? 'show' : ''; ?>" id="ManageComments" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <a class="nav-link <?php echo $cpanel->activeMenu('post-comments'); ?>" href="<?php echo cpanel('post-comments'); ?>" data-ajax="?path=post-comments">Post Comments</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('comments-reply'); ?>" href="<?php echo cpanel('comments-reply'); ?>" data-ajax="?path=comments-reply">Post Comments Replies</a>
                                </nav>
                            </div>
                            <hr>
                            <!-- Drawer link (Design)-->
                            <?php if (IS_MANAGER) { ?>
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#DesignPages" aria-expanded="false" aria-controls="DesignPages">
                                <div class="nav-link-icon"><i class="material-icons">color_lens</i></div>Design
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'themes' || $ui == 'site-design') ? 'show' : ''; ?>" id="DesignPages" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <a class="nav-link <?php echo $cpanel->activeMenu('themes'); ?>" href="<?php echo cpanel('themes'); ?>" data-ajax="?path=themes">Themes</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('site-design'); ?>" href="<?php echo cpanel('site-design'); ?>" data-ajax="?path=site-design">Change Site Design</a>
                                </nav>
                            </div>
                            <?php } ?>
                            <!-- Drawer link (Reports)-->
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#ReportsPages" aria-expanded="false" aria-controls="ReportsPages">
                                <div class="nav-link-icon"><i class="material-icons">flag</i></div>Reports
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'post-reports' || $ui == 'profile-reports') ? 'show' : ''; ?>" id="ReportsPages" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <a class="nav-link <?php echo $cpanel->activeMenu('post-reports'); ?>" href="<?php echo cpanel('post-reports'); ?>" data-ajax="?path=post-reports">Post reports</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('profile-reports'); ?>" href="<?php echo cpanel('profile-reports'); ?>" data-ajax="?path=profile-reports">Profile reports</a>
                                </nav>
                            </div>
                            <!-- Drawer link (Affiliates)-->
                            <a class="nav-link <?php echo $cpanel->activeMenu('affiliates-settings'); ?>" href="<?php echo cpanel('affiliates-settings'); ?>" data-ajax="?path=affiliates-settings">
                                <div class="nav-link-icon"><i class="material-icons">co_present</i></div>Affiliates
                            </a>
                            <!-- Drawer link (Blogs)-->
                            <?php if($config['blog_system'] == 'on') { ?>
                            <!-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#BlogPages" aria-expanded="false" aria-controls="BlogPages">
                                <div class="nav-link-icon"><i class="material-icons">view_carousel</i></div>Manage Blogs
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'add-new-article' || $ui == 'manage-articles' || $ui == 'blog-categories' || $ui == 'edit-article') ? 'show' : ''; ?>" id="BlogPages" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <a class="nav-link <?php echo $cpanel->activeMenu('add-new-article'); ?>" href="<?php echo cpanel('add-new-article'); ?>" data-ajax="?path=add-new-article">Add new article</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('manage-articles'); ?><?php echo $cpanel->activeMenu('edit-article'); ?>" href="<?php echo cpanel('manage-articles'); ?>" data-ajax="?path=manage-articles">Manage articles</a>
                                    <a class="nav-link <?php echo $cpanel->activeMenu('blog-categories'); ?>" href="<?php echo cpanel('blog-categories'); ?>" data-ajax="?path=blog-categories">Blog categories</a>
                                </nav>
                            </div> -->
                            <?php } ?>
                            <!-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#BlogPages" aria-expanded="false" aria-controls="BlogPages">
                                <div class="nav-link-icon"><i class="material-icons">compare_arrows</i></div>API Settings
                                <div class="drawer-collapse-arrow"><i class="material-icons">expand_more</i></div>
                            </a>
                            <div class="collapse <?php echo ($ui == 'push-notifications') ? 'show' : ''; ?>" id="BlogPages" aria-labelledby="headingTwo" data-bs-parent="#drawerAccordion">
                                <nav class="drawer-menu-nested nav accordion" id="drawerAccordionPages">
                                    <a class="nav-link <?php echo $cpanel->activeMenu('push-notifications'); ?>" href="<?php echo cpanel('push-notifications'); ?>" data-ajax="?path=push-notifications">Push Notifications</a>
                                </nav>
                            </div> -->
                            <a class="nav-link <?php echo $cpanel->activeMenu('system-backup'); ?>" href="<?php echo cpanel('system-backup'); ?>" data-ajax="?path=system-backup">
                                <div class="nav-link-icon"><i class="material-icons">backup</i></div>System Backup
                            </a>
                        </div>
                    </div>
                    <!-- Drawer footer -->
                    <div class="drawer-footer border-top">
                        <div class="d-flex align-items-center">
                        <img src="<?php echo $me['avatar']; ?>" class="img-fluid rounded-circle text-muted" alt="image" width="40" height="40">
                            <div class="ms-3">
                                <div class="caption">Administration</div>
                                <div class="small fw-500"><?php echo $me['name']; ?></div> 
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
            <!-- Layout content-->
            <div id="layoutDrawer_content">
                <main>
                    <!-- Main cpanel content-->
                    <div class="ui-content container-xl p-4 <?php echo ($mode == 'night' ? 'dark drawer-dark bg-dark' : ''); ?>">
                        <?php echo $content; ?>
                    </div>
                </main>
                <!-- Footer-->
                <footer class="footbar py-4 mt-auto border-top <?php echo ($mode == 'night' ? 'drawer-dark bg-dark' : ''); ?>" style="min-height: 74px">
                    <div class="container-xl px-5">
                        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between small">
                            <div class="me-sm-2 text-uppercase"><?php echo $config['site_name']; ?> SECRET PANEL &nbsp;©&nbsp; 2022</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <!-- Load global scripts-->
        <script src="<?php echo(cpanel('si/js/prism.js')) ?>"></script>
        <script src="<?php echo(cpanel('si/js/notify.js')) ?>"></script>
        <script src="<?php echo(cpanel('si/js/scripts.js')) ?>"></script>
        <script src="<?php echo(cpanel('si/js/material.js')) ?>"></script>
        <script src="<?php echo(cpanel('si/js/tippy/popper.min.js')) ?>"></script>
        <script src="<?php echo cpanel('si/js/tinymce/tinymce.min.js'); ?>"></script>
        <script src="<?php echo(cpanel('si/js/tippy/tippy-bundle.umd.min.js')) ?>"></script>
        <style>
        .pagination > li > a .material-icons, .pagination > li > a .material-icons {vertical-align: middle;line-height: 26px;}
        .pagination > li > a, .pagination > li > a {min-width: 32px;border-radius: 16px;padding: 5px 4px !important;background: rgb(0 0 0 / 7%);display: block;text-align: center;margin: 0 2px;}
        .pagination li.active a {color: #fff;}
        .pagination .page-item.active .page-link {background: #e882a3;border-color: transparent;}
        .pagination .page-item .page-link:hover, .pagination .page-item .page-link:focus {text-decoration: none;}
        .pagination.pagination-rounded .page-item {margin: 0 5px;}
        .pagination.pagination-rounded .page-item .page-link {border-radius: 50%;padding: 0;display: flex;align-items: center;justify-content: center;height: 40px;width: 40px;}
        .pagination.pagination-rounded.pagination-sm .page-link {height: 30px;width: 30px;}
        .pagination.pagination-rounded.pagination-lg .page-link {height: 60px;width: 60px;}
        .pager li > a {-webkit-border-radius: 0;-moz-border-radius: 0;-ms-border-radius: 0;border-radius: 0;border: none;background-color: transparent;color: #222;font-weight: bold; }
        .pager li a:focus, .pager li a:active {background-color: transparent; }
        .pagination .disabled a, .pagination .disabled a:hover, .pagination .disabled a:focus, .pagination .disabled a:active {color: #bbb; }
        .pagination li.active a { color: #fff; background-color: var(--bs-fumigram); }
        .pagination li {-webkit-border-radius: 0;-moz-border-radius: 0;-ms-border-radius: 0;border-radius: 0; }.pagination li a:focus,.pagination li a:active {background-color: var(--bs-fumigram);color: #fff; }
        .no-data-found {margin-bottom: 20px;text-align: center;}
        .pagination > li > a {border: none;font-weight: bold;color: #555; }
        .pagination > li > a, .pagination > li > a {width: auto;height: 32px;-webkit-border-radius: 0;-moz-border-radius: 0;-ms-border-radius: 0;border-radius: 0; }.pagination > li > a .material-icons,.pagination > li > a .material-icons {position: relative;bottom: 2px; }
        .pagination-sm > li:first-child > a, .pagination-sm > li:last-child > a {width: 28px;height: 28px; }.pagination-sm > li:first-child > a .material-icons,.pagination-sm > li:last-child > a .material-icons {position: relative;top: -1px;left: -6px;font-size: 20px; }
        .pagination-lg > li:first-child > a, .pagination-lg > li:last-child > a {width: 44px;height: 44px; }.pagination-lg > li:first-child > a .material-icons,.pagination-lg > li:last-child > a .material-icons {font-size: 30px;position: relative;top: -3px;left: -10px; }
        nav ul:not(.list-unstyled) li a {padding: 10px;}
        .hidden{display: none;}
        </style>
        <script>
        tippy('.lastpage', {arrow: false,content: 'Last Page'});
        tippy('.nextpage', {arrow: false,content: 'Next Page'});
        tippy('.previouspage', {arrow: false,content: 'Previous Page'});
        tippy('.firstpage', {arrow: false,content: 'First Page'});
        tippy('#verify', {arrow: false,content: 'Verified'});
        tippy('.facebook', {arrow: false,content: 'Facebook Login'});
        tippy('.google', {arrow: false,content: 'Google Login'});
        tippy('.vkontakte', {arrow: false,content: 'Vkontakte Login'});
        tippy('.twitter', {arrow: false,content: 'Twitter Login'});
        tippy('.linkedIn', {arrow: false,content: 'linkedIn Login'});
        tippy('.instagram', {arrow: false,content: 'Instagram Login'});
        tippy('.discord', {arrow: false,content: 'Discord Login'});
        tippy('.referrer', {arrow: false,content: 'Referrer Login'});
        tippy('.credits', {arrow: false,content: 'Credits'});
        tippy('.fumigram', {arrow: false,content: '<?php echo $config['site_name']; ?> Login'});
        $("body").on("contextmenu", "img", function(e) { return false; });        
        /****************************
        * @param Change Mode
        ****************************/
        function Mode(mode) {
            if (mode == 'day') {
                $('.nav-fixed').removeClass('dark');
                $('.nav-fixed').addClass('bg-light');
                $('.nav-fixed').removeClass('bg-dark');

                $('.ui-content').removeClass('dark');
                $('.ui-content').removeClass('bg-dark');
                $('.ui-content').removeClass('drawer-dark');

                $('.drawer').removeClass('bg-dark');
                $('.drawer').removeClass('drawer-dark');

                $('.drawer').addClass('bg-white');
                $('.drawer').addClass('drawer-light');

                $('.footbar').removeClass('bg-dark');
                $('.footbar').removeClass('drawer-dark');

                $('.top-app-bar').addClass('navbar-light');
                $('.top-app-bar').addClass('nav-bg-light');
                $('.top-app-bar').removeClass('nbg-dark');
                $('.top-app-bar').removeClass('navbar-dark');

                $('.turn-mode').attr('onclick', "Mode('night')");
                $('.navbar-brand').html('<img src="<?php echo cpanel('si/img/logo/logo-day.png');?>" alt="..." style="height: 2.5rem" />');
                $('.turn-mode').html('<a href="javascript:void(0)" class="menu-option pointer"><svg class="feather feather-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>&nbsp;&nbsp;&nbsp;<div><b>Night Mode</b><p>Turn off the lights</p></div></a>');
            } else {
                $('.nav-fixed').addClass('dark');
                $('.nav-fixed').addClass('bg-dark');
                $('.nav-fixed').removeClass('bg-light');

                $('.ui-content').addClass('dark');
                $('.ui-content').addClass('bg-dark');
                $('.ui-content').addClass('drawer-dark');

                $('.drawer').addClass('bg-dark');
                $('.drawer').addClass('drawer-dark');

                $('.footbar').addClass('bg-dark');
                $('.footbar').addClass('drawer-dark');

                $('.top-app-bar').addClass('nbg-dark');
                $('.top-app-bar').addClass('navbar-dark');
                $('.top-app-bar').removeClass('navbar-light');
                $('.top-app-bar').removeClass('nav-bg-light');

                $('.turn-mode').attr('onclick', "Mode('day')");
                $('.navbar-brand').html('<img src="<?php echo cpanel('si/img/logo/logo-night.png');?>" alt="..." style="height: 2.5rem" />');
                $('.turn-mode').html(' <a href="javascript:void(0)" class="menu-option pointer"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 512 512" fill="currentColor"><g><g><path d="m256,432.3c-11.3,0-20.4,9.1-20.4,20.4v27.9c0,11.3 9.1,20.4 20.4,20.4s20.4-9.1 20.4-20.4v-27.9c0-11.3-9.1-20.4-20.4-20.4z"></path><path d="m256,102.5c-84.6,0-153.5,68.8-153.5,153.5 0,84.6 68.8,153.5 153.5,153.5 84.6,0 153.5-68.8 153.5-153.5 0-84.6-68.9-153.5-153.5-153.5zm0,266.1c-62.1,0-112.6-50.5-112.6-112.6 0-62.1 50.5-112.6 112.6-112.6s112.6,50.5 112.6,112.6c0,62.1-50.5,112.6-112.6,112.6z"></path><path d="M256,79.7c11.3,0,20.4-9.1,20.4-20.4V31.4c0-11.3-9.1-20.4-20.4-20.4s-20.4,9.1-20.4,20.4v27.9    C235.6,70.6,244.7,79.7,256,79.7z"></path><path d="m480.6,235.6h-27.9c-11.3,0-20.4,9.1-20.4,20.4 0,11.3 9.1,20.4 20.4,20.4h27.9c11.3,0 20.4-9.1 20.4-20.4 0-11.3-9.1-20.4-20.4-20.4z"></path><path d="m59.3,235.6h-27.9c-11.3,0-20.4,9.1-20.4,20.4 0,11.3 9.1,20.4 20.4,20.4h27.9c11.3,0 20.4-9.1 20.4-20.4 1.42109e-14-11.3-9.1-20.4-20.4-20.4z"></path><path d="m409.5,131.4l19.7-19.7c8-8 8-20.9 0-28.9-8-8-20.9-8-28.9,0l-19.7,19.7c-8,8-8,20.9 0,28.9s20.9,7.9 28.9,0z"></path><path d="m102.5,380.6l-19.7,19.7c-8,8-8,20.9 0,28.9 8,8 20.9,8 28.9,0l19.7-19.7c8-8 8-20.9 0-28.9s-20.9-7.9-28.9,0z"></path><path d="m409.5,380.6c-8-8-20.9-8-28.9,0-8,8-8,20.9 0,28.9l19.7,19.7c8,8 20.9,8 28.9,0 8-8 8-20.9 0-28.9l-19.7-19.7z"></path><path d="m102.5,131.4c8,8 20.9,8 28.9,0 8-8 8-20.9 0-28.9l-19.7-19.7c-8-8-20.9-8-28.9,0-8,8-8,20.9 0,28.9l19.7,19.7z"></path></g></g></svg>&nbsp;&nbsp;&nbsp;<div><b>Day Mode</b><p>Turn on the lights</p></div></a>');
            }
            hash_id = $('#hash_id').val();
            $.get("<?php echo($config['site_url']) ?>/aj/main/change-mode" ,{hash_id: hash_id}, function(data) {
                if(data.status == 200){
                    $('#redirect_link').attr('href', "<?php echo cpanel('overview'); ?>");
                    $('#redirect_link').attr('data-ajax', "?path=overview");
                    $('#redirect_link').click();
                }
             });
            }
        </script>
</body>
</html>
