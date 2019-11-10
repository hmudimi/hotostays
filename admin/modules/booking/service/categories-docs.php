<?php
/* ==============================================
 * CSS AND JAVASCRIPT USED IN THIS MODEL
 * ==============================================
 */
$stylesheets[] = array('file' => DOCBASE.'js/plugins/isotope/css/style.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/jquery.isotope/1.5.25/jquery.isotope.min.js';
$javascripts[] = DOCBASE.'js/plugins/isotope/jquery.isotope.sloppy-masonry.min.js';

$stylesheets[] = array('file' => DOCBASE.'js/plugins/lazyloader/lazyloader.css', 'media' => 'all');
$javascripts[] = DOCBASE.'js/plugins/lazyloader/lazyloader.js';

$stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/3.5.5/css/star-rating.min.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/3.5.5/js/star-rating.min.js';

$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/html2canvas.min.js';
$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/three.min.js';
$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/pdf.min.js';
$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/3dflipbook.min.js';

require(getFromTemplate('common/send_comment.php', false));

require(getFromTemplate('common/header.php', false)); ?>

<section id="page">
    
    <?php include(getFromTemplate('common/page_header.php', false)); ?>
    
    <div id="content" class="pt30 pb20">
        <div class="container" itemprop="text">

            <div class="alert alert-success" style="display:none;"></div>
            <div class="alert alert-danger" style="display:none;"></div>
            
            <div class="row">
                <?php
                $widgetsLeft = getWidgets('left', $page_id);
                $widgetsRight = getWidgets('right', $page_id);
                
                if(!empty($widgetsLeft)){ ?>
                    <div class="col-sm-3">
                        <?php displayWidgets('left', $page_id); ?>
                    </div>
                    <?php
                } ?>
                
                <div class="col-sm-<?php if(!empty($widgetsLeft) && !empty($widgetsRight)) echo 6; elseif(!empty($widgetsLeft) || !empty($widgetsRight)) echo 9; else echo 12; ?>">
                    <?php echo $page['text']; ?>
                </div>
                
                <?php
                if(!empty($widgetsRight)){ ?>
                    <div class="col-sm-3">
                        <?php displayWidgets('right', $page_id); ?>
                    </div>
                    <?php
                } ?>
            </div>

            <div class="row" id="list-categories">
                <?php
                if(isset($parents[$page_id])){
                    $id = 0;
                    $result_page_file = $db->prepare('SELECT * FROM pm_page_file WHERE id_item = :page_id AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                    $result_page_file->bindParam(':page_id', $id);
                    foreach($parents[$page_id] as $id){ ?>
                        <article class="col-xs-6 col-sm-4 col-md-2 text-center mb15">
                            <a itemprop="url" href="<?php echo DOCBASE.$pages[$id]['alias']; ?>">
                                <?php
                                if($result_page_file->execute() !== false && $db->last_row_count() > 0){
                                    $row = $result_page_file->fetch(PDO::FETCH_ASSOC);
                                    
                                    $file_id = $row['id'];
                                    $filename = $row['file'];
                                    $label = $row['label'];
                                    
                                    $realpath = SYSBASE.'medias/page/big/'.$file_id.'/'.$filename;
                                    $thumbpath = DOCBASE.'medias/page/big/'.$file_id.'/'.$filename;
                                    
                                    if(is_file($realpath)){ ?>
                                        <div class="icon-home-wrapper">
                                            <div class="icon-home">
                                                <img alt="<?php echo $label; ?>" src="<?php echo $thumbpath; ?>" class="img-responsive">
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } ?>
                                <h4><?php echo $pages[$id]['name']; ?></h4>
                            </a>
                        </article>
                        <?php
                    }
                } ?>
                <article class="col-xs-6 col-sm-4 col-md-2 text-center pb15">
                    <?php
                    $id = 11; ?>
                    <a itemprop="url" href="<?php echo DOCBASE.$pages[$id]['alias']; ?>">
                        <?php
                        if($result_page_file->execute() !== false && $db->last_row_count() > 0){
                            $row = $result_page_file->fetch(PDO::FETCH_ASSOC);
                            
                            $file_id = $row['id'];
                            $filename = $row['file'];
                            $label = $row['label'];
                            
                            $realpath = SYSBASE.'medias/page/big/'.$file_id.'/'.$filename;
                            $thumbpath = DOCBASE.'medias/page/big/'.$file_id.'/'.$filename;
                            
                            if(is_file($realpath)){ ?>
                                <div class="icon-home-wrapper">
                                    <div class="icon-home">
                                        <img alt="<?php echo $label; ?>" src="<?php echo $thumbpath; ?>" class="img-responsive">
                                    </div>
                                </div>
                                <?php
                            }
                        } ?>
                        <h4><?php echo $pages[$id]['name']; ?></h4>
                    </a>
                </article>
                <?php
                $result_page_file = $db->query('SELECT * FROM pm_page_file WHERE id_item = 5 AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'other\' AND file LIKE \'%-'.LANG_TAG.'.pdf\' ORDER BY rank LIMIT 1');
                if($result_page_file !== false && $db->last_row_count() > 0){
                    
                    $row = $result_page_file->fetch();
                    
                    $file_id = $row['id'];
                    $filename = $row['file'];
                    
                    $realpath = SYSBASE.'medias/page/other/'.$file_id.'/'.$filename;
                    $docpath = DOCBASE.'medias/page/other/'.$file_id.'/'.$filename;
                    
                    if(is_file($realpath)){?>
                        <article class="col-xs-6 col-sm-4 col-md-2 text-center pb15">
                            <a itemprop="url" href="#pdf-catalogue" class="popup-modal">
                                <div class="icon-home-wrapper">
                                    <div class="icon-home">
                                        <img alt="" src="<?php echo getFromTemplate('images/btn-produits.png', true); ?>" class="img-responsive">
                                    </div>
                                </div>
                                <h4><?php echo $texts['PRODUCT_CATALOG']; ?></h4>
                            </a>
                            <div id="pdf-catalogue" class="pdf-popup popup-block mfp-hide">
                                <div class="flipbook3d" data-pdfsrc="<?php echo $docpath; ?>"></div>
                            </div>
                            
                            
                        </article>
                        <?php
                    }
                } ?>
            </div>
        </div>
    </div>
</section>
