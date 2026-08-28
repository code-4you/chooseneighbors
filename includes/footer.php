<?php
/**
 * Shared page footer.
 * Set before including:
 *   $page_id      - JS options.pageId for this page (required)
 *   $footer_extra - optional extra HTML/scripts before </body>
 */
$page_id      = isset($page_id) ? $page_id : 'main';
$footer_extra = isset($footer_extra) ? $footer_extra : '';
?>
    
    <div class="modal modal-form fade" id="info-box" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title placeholder-title" id="info-box-title">Choose Neighbors</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>

                <div class="modal-body">

                    <div id="info-box-message" class="error-summary alert alert-danger"></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="langModal" tabindex="-1" role="dialog" aria-labelledby="langModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="langModal">Choose your language</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <a onclick="App.setLanguage('en'); return false;" class="box-menu-item " href="javascript:void(0)">English</a><a onclick="App.setLanguage('ru'); return false;" class="box-menu-item " href="javascript:void(0)">Русский</a>                </div>
                <div class="modal-footer">
                    <button type="button" class="btn blue" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls " style="display: none;">
        <div class="slides" style="width: 2048px;"></div>
        <h3 class="title hidden"></h3>
        <a class="prev text-light">‹</a>
        <a class="next text-light">›</a>
        <a class="close text-light">×</a>
        <a class="play-pause"></a>
        <ol class="indicator"></ol>
    </div>

    <div id="modal-section"></div>

    <div id="main-footer">

        <div class="wrap">

            <ul id="footer-nav">

                <li><a href="/create">create</a></li>
                <li><a href="/listings">listings</a></li>
                <li><a href="/users">people</a></li>
                <li><a href="/groups">groups</a></li>
                <li><a href="/interest">register interest</a></li>
                <li><a href="http://blog.chooseneighbors.com">blog</a></li>
                <li><a href="/about">about</a></li>
                <li><a href="/terms">terms</a></li>
                <li><a href="/privacy">privacy policy</a></li>
                <li><a href="/gdpr">GDPR</a></li>
                <li><a href="/support">support</a></li>
                <li><a class="lang_link" href="javascript:void(0)"  data-toggle="modal" data-target="#langModal"><i class="fa fa-globe"></i> English</a></li>


              <li id="footer-copyright">
    Copyright © 2014-<?php echo date('Y'); ?> - Choose Neighbors · <a href="/license" style="color:inherit;text-decoration:underline">License &amp; copyright</a></li>

            </ul>

        </div>
    </div>

    <script type="text/javascript" src="/js/jquery-3.3.1.min.js?x=2"></script>

    <script type="text/javascript" src="/js/my.js?x=345"></script>

    <script type="text/javascript" src="/js/jquery.colorbox.js?x=30"></script>
    <script type="text/javascript" src="/js/jquery.autosize.js?x=30"></script>
    <script type="text/javascript" src="/js/jquery.cookie.js?x=30"></script>
    <script type="text/javascript" src="/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="/js/load-image.all.min.js"></script>
    <script type="text/javascript" src="/js/jquery.ui.widget.js"></script>
    <script type="text/javascript" src="/js/jquery.iframe-transport.js"></script>
    <script type="text/javascript" src="/js/jquery.fileupload.js"></script>
    <script type="text/javascript" src="/js/jquery.fileupload-process.js"></script>
    <script type="text/javascript" src="/js/jquery.fileupload-image.js"></script>
    <script type="text/javascript" src="/js/jquery.fileupload-validate.js"></script>
    <script type="text/javascript" src="/js/blueimp-gallery.js"></script>
    <script type="text/javascript" src="/js/sidenav.min.js"></script>
    <script type="text/javascript" src="/js/bootstrap-slider.min.js"></script>


    <script type="text/javascript">

        var options = {

            pageId: "<?php echo $page_id; ?>",
            api_version: "v2",
            post_max_images: 7        };

        var constants = {
            MAX_FILE_SIZE: 3145728,
            VIDEO_FILE_MAX_SIZE: 7340035,
            GOOGLE_CLIENT_ID: "983057115541-arjv7to5i9vj7berk1ng8csedh9sr4i5.apps.googleusercontent.com"
        };

        var account = {

            id: "0",
            username: "undefined",
            accessToken: "undefined"
        };

        var strings = {

            sz_action_follow: "Follow",
            sz_action_unfollow: "Unfollow",
            sz_action_login: "Log in",
            sz_action_signup: "Sign up",
            sz_action_block: "Block",
            sz_action_unblock: "UnBlock",
            sz_action_close: "Close",
            sz_action_report: "Report",
            sz_report_reason_1: "This is spam.",
            sz_report_reason_2: "Hate Speech or violence.",
            sz_report_reason_3: "Nudity or Pornography.",
            sz_report_reason_4: "Fake profile.",
            sz_action_remove_from_friends: "Remove from friends",
            sz_action_cancel_friends_request: "Cancel Friend Request",
            sz_action_add_to_friends: "Add to friends",
            sz_message_prompt_like: "You have to be a registered user to like posts.",
            sz_message_prompt_title: "create account or login",
            sz_message_empty_list: "List is empty.",
            sz_action_pin: "Pin",
            sz_action_unpin: "Unpin"
        };

    </script>

    <script type="text/javascript">

        var lang_prompt_box = "create account or login";
    </script>

    <script type="text/javascript" src="/js/common.js?x44"></script>

<?php echo $footer_extra; ?>

</body>
</html>