<html>
    <head>
        <meta charset="utf-8">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="mobile-web-app-status-bar-style" content="black" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>Fumigram | Installation</title>
        <link href="assets/img/logo.png" rel="shortcut icon" type="image/png"/>
        <link href="assets/fonts/Viga/viga.css" rel="stylesheet">
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/css/style.css" rel="stylesheet">
    </head>
    <body class="installer">
        <div class="d-lg-flex half wrapper">
            <div class="bg order-1 order-md-1 background">
                <div><img src="assets/img/intro.jpg" /></div>
            </div>
            <div class="install-box contents order-2 order-md-2">
                <div class="container">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-md-9 box-content">
                            <div class="logo"><img src="assets/img/logo.png" width="100" height="100"/></div> 
                            <h3>Fumigram Installer</h3>
                            <p class="mb-4">
                                Thanks for purchasing our script. <br>
                                Please feel free to reach us via fumigram.app@gmail.com <br> if you have any inquiries.
                                Or email me personally at '_____'if you want to chat!
                            </p>
                            <?php include('form.php'); ?>
                            <?php include('requirements.php'); ?>
                            <div class="installed" style="display: none;">
                                <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                                    <img src="assets/img/tick.png" width="38" height="38" class="flex-shrink-0" />
                                    <div class="d-flex gap-2 w-100 justify-content-between">
                                        <div>
                                            <h6 class="mb-0 title">Installed</h6>
                                            <p class="mb-10 description"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info" role="alert">
                                    <p><b>NOTICE</b>: Please delete the install folder to prevent others from reinstalling your application.</p>
                                </div>
                                <a class="link" id="ready" href="">
                                    <span><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" style="margin-top:-3px"><path fill="currentColor" d="M12,21.35L10.55,20.03C5.4,15.36 2,12.27 2,8.5C2,5.41 4.42,3 7.5,3C9.24,3 10.91,3.81 12,5.08C13.09,3.81 14.76,3 16.5,3C19.58,3 22,5.41 22,8.5C22,12.27 18.6,15.36 13.45,20.03L12,21.35Z"></path></svg> Let's Get Started</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/jquery-min.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/script.js"></script>
    </body>
</html>