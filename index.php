<?php

if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    include( 'gitgrep.php' );
}


if( !isset($_GET['s']) || ($s=trim($_GET['s'])) == '' ) {
    $s = 'filename:wp-config.php';
} else {
    $init_run = true;
}
if( !isset($_GET['r']) || ($r=trim($_GET['r'])) == '' ) {
    $r = "DB_PASSWORD',\s*'[^']{4,}";
} else {
    $init_run = true;
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>git | grep</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="author" content="Gwendal Le Coguic">
        <meta name="description" content="git|grep  - regexp search over github search">
        <meta name="keywords" content="development,security,researcher,bugbounty,tools,github,regexp,secrets">

        <meta name="twitter:card" content="summary_large_image"/>
        <meta name="twitter:image" content="http://gitgrep.me/img/preview.jpg"/>

        <meta name="twitter:title" content="git|grep"/>
        <meta name="twitter:description" content="git|grep - regexp search over github search"/>
        <meta name="twitter:site" content="@gitgrep"/>
        <meta name="twitter:creator" content="@gwendallecoguic"/>

        <meta property="og:title" content="git|grep" />
        <meta property="og:description" content="git|grep - regexp search over github search" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://gitgrep.me/" />

        <meta property="og:image" content="http://gitgrep.me/img/preview.jpg" />
        <meta property="og:site_name" content="gitgrep" />

        <link rel="stylesheet" href="/css/bootstrap.min.css" />
        <link rel="stylesheet" href="/css/gitgrep.css" />
    </head>

    <body>
        <div id="site-infos" class="card">
            <div class="card-body">
                <p class="card-text">
                    git|grep is a single page website made with love, by hackers for hackers.
                    It helps to find secrets on GitHub using his API.
                    Unfortunately that means that we have to deal with the restrictions in place.
                </p>
                <p>
                    This webservice is my contribution to the security industry, if you like it, you can support my work.
                </p>
                <p class="text-center">
                    <a href="https://www.buymeacoffee.com/gwendallecoguic" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/default-yellow.png" alt="Buy Me A Coffee" style="height: 51px !important;width: 217px !important;" width="217" ></a>
                </p>
                <p>
                    Found a bug? Feel free to open an issue on <a href="https://github.com/gwen001/gitgrep" target="_blank">the official repository</a>.
                </p>
            </div>
        </div>

        <div class="container">
            <div class="row p-4"></div>
                <div class="row justify-content-md-center">
                    <div class="col-9">
                        <div id="logo" class="text-center">
                            <h1>git <span class="text-danger">|</span> grep</h1>
                        </div>
                    </div>
                </div>

                <div class="row p-4"></div>
                <div class="row justify-content-md-center">
                    <div class="col-9">
                        <?php include( 'gitgrep.html' ); ?>
                </div>
            </div>
        </div>

        <script src="/js/jquery-3.4.1.min.js" type="text/javascript"></script>
        <script src="/js/gitgrep.js" type="text/javascript"></script>
        <?php if( isset($init_run) ) { ?>
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#search-form').submit();
                });
            </script>
        <?php } ?>
    </body>
</html>
