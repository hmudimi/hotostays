<?php debug_backtrace() || die ("Direct access not permitted"); ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
    HOTO CMS
    <?php
    echo TITLE_ELEMENT;
    if(defined("SITE_TITLE")) echo " | ".SITE_TITLE; ?>
</title>

<?php
if(defined("TEMPLATE")){ ?>
    <link rel="icon" type="image/png" href="<?php echo DOCBASE; ?>templates/<?php echo TEMPLATE; ?>/images/favicon.png">
    <?php
} ?>
    
<link rel="stylesheet" href="<?php echo DOCBASE.ADMIN_FOLDER; ?>/main.css" >

<script>
    $(function(){
        <?php
        if(isset($_SESSION['msg_error']) && isset($_SESSION['msg_success']) && isset($_SESSION['msg_notice'])){
            
            if(!is_array($_SESSION['msg_error'])) $_SESSION['msg_error'] = array();
            if(!is_array($_SESSION['msg_success'])) $_SESSION['msg_success'] = array();
            if(!is_array($_SESSION['msg_notice'])) $_SESSION['msg_notice'] = array();
            
            $_SESSION['msg_error'] = array_unique($_SESSION['msg_error']);
            $_SESSION['msg_success'] = array_unique($_SESSION['msg_success']);
            $_SESSION['msg_notice'] = array_unique($_SESSION['msg_notice']); ?>
            
            var msg_error = '<?php echo str_replace(addslashes("\n"), "\n", addslashes(implode("<br>", $_SESSION['msg_error']))); ?>';
            var msg_success = '<?php echo str_replace(addslashes("\n"), "\n", addslashes(implode("<br>", $_SESSION['msg_success']))); ?>';
            var msg_notice = '<?php echo str_replace(addslashes("\n"), "\n", addslashes(implode("<br>", $_SESSION['msg_notice']))); ?>';
            
            var button_close = '<button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>';
            if(msg_error != '') $('.alert-container .alert-danger').html(msg_error+button_close).show();
            if(msg_success != '') $('.alert-container .alert-success').html(msg_success+button_close).show();
            if(msg_notice != '') $('.alert-container .alert-warning').html(msg_notice+button_close).show();
            <?php
        } ?>
        
        $('[data-toggle="tooltip"]').tooltip();
        
        $('select[data-filter]').each(function(){
            var target = $(this);
            var currval = $(this).val();
            var curropt = $('option[value="'+currval+'"]', target);
            var input = $('select').filter('<?php if(defined("MODULE")) : ?>[name^="<?php echo MODULE; ?>_'+target.attr('data-filter')+'"],<?php endif; ?>[name="'+target.attr('data-filter')+'"]');
            input.on('change', function(){
                var val = $(this).val();
                $('option[value!=""]', target).hide().prop('selected', false);
                $('option[rel="'+val+'"]', target).show();
                if(curropt.attr('rel') == val) curropt.prop('selected', true);
            });
            input.trigger('change');
        });
        
        $(window).on('resize', function(){
            var h = $(this).height() - 50;
            $('.side-nav').css('max-height', h);
        });
        $(window).trigger('resize');
    })
</script>
