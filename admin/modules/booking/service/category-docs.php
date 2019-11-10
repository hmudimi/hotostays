<?php
if($article_alias == '') err404();

$result = $db->query('SELECT * FROM pm_product WHERE checked = 1 AND lang = '.LANG_ID.' AND alias = '.$db->quote($article_alias));
if($result !== false && $db->last_row_count() > 0){
    
    $product = $result->fetch(PDO::FETCH_ASSOC);
    
    $article_id = $product['id'];
    
    $product_id = $article_id;
    $title_tag = $product['title'].' - '.$title_tag;
    $page_title = $product['title'];
    $page_subtitle = '';
    $id_page = $product['id_page'];
    $page_alias = $product['alias'];
    $page_alias = $pages[$id_page]['alias'].'/'.text_format($product['alias']);
    
    $result_product_file = $db->query('SELECT * FROM pm_product_file WHERE id_item = '.$product_id.' AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
    if($result_product_file !== false && $db->last_row_count() > 0){
        
        $row = $result_product_file->fetch();
        
        $file_id = $row['id'];
        $filename = $row['file'];
        
        if(is_file(SYSBASE.'medias/product/medium/'.$file_id.'/'.$filename))
            $page_img = getUrl(true).'/medias/product/medium/'.$file_id.'/'.$filename;
    }
}else err404();

check_URI(DOCBASE.$page_alias);

/* ==============================================
 * CSS AND JAVASCRIPT USED IN THIS MODEL
 * ==============================================
 */
$stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/assets/owl.carousel.min.css', 'media' => 'all');
$stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/assets/owl.theme.default.min.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/owl.carousel.min.js';

$stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/3.5.5/css/star-rating.min.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/3.5.5/js/star-rating.min.js';

$javascripts[] = DOCBASE.'js/plugins/sharrre/jquery.sharrre.min.js';

$stylesheets[] = array('file' => DOCBASE.'js/plugins/royalslider/royalslider.css', 'media' => 'all');
$stylesheets[] = array('file' => DOCBASE.'js/plugins/royalslider/skins/minimal-white/rs-minimal-white.css', 'media' => 'all');
$javascripts[] = DOCBASE.'js/plugins/royalslider/jquery.royalslider.min.js';

$stylesheets[] = array('file' => DOCBASE.'js/plugins/isotope/css/style.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/jquery.isotope/1.5.25/jquery.isotope.min.js';
$javascripts[] = DOCBASE.'js/plugins/isotope/jquery.isotope.sloppy-masonry.min.js';

$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/html2canvas.min.js';
$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/three.min.js';
$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/pdf.min.js';
$javascripts[] = DOCBASE.'js/plugins/3d-flipbook/js/3dflipbook.min.js';

require(getFromTemplate('common/send_comment.php', false));

require(getFromTemplate('common/header.php', false)); ?>

<article id="page">
    <?php include(getFromTemplate('common/page_header.php', false)); ?>
    
    <div id="content" class="pt30 pb30">
            
        <article itemscope itemtype="http://schema.org/Product">
            <div class="container">

                <div class="alert alert-success" style="display:none;"></div>
                <div class="alert alert-danger" style="display:none;"></div>
               
                <div class="row mb15">
                    <div class="col-md-5 mb15">
                        <div class="royalSlider rsMinW clearfix" data-control="thumbnails">
                            <?php
                            $result_product_file = $db->query('SELECT * FROM pm_product_file WHERE id_item = '.$product_id.' AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank');
                            if($result_product_file !== false){
                                
                                foreach($result_product_file as $i => $row){
                                
                                    $file_id = $row['id'];
                                    $filename = $row['file'];
                                    $label = $row['label'];
                                    
                                    $realpath = SYSBASE.'medias/product/small/'.$file_id.'/'.$filename;
                                    $zoompath = DOCBASE.'medias/product/big/'.$file_id.'/'.$filename;
                                    $thumbpath = DOCBASE.'medias/product/small/'.$file_id.'/'.$filename;
                                    
                                    if(is_file($realpath)){
                                        $size = getimagesize($realpath);
                                        $w = $size[0];
                                        $h = $size[1];
                                        if($h > $w){
                                            $mode = 'portrait';
                                            $m_h = ceil((96-($h*96/$w))/2);
                                            $m_w = 0;
                                        }else{
                                            $mode = 'landscape';
                                            $m_h = 0;
                                            $m_w = ceil((96-($w*96/$h))/2);
                                        } ?>
                                        <div class="rsContent" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
                                            <img class="rsImg" src="<?php echo $zoompath; ?>" alt="<?php echo $label; ?>">
                                            <img class="rsTmb <?php echo $mode; ?>" src="<?php echo $thumbpath; ?>" alt="<?php echo $label; ?>" style="margin: <?php echo $m_h.'px '.$m_w.'px'; ?>">
                                        </div>
                                        <?php
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <?php
                        $result_rating = $db->query('SELECT count(*) as count_rating, AVG(rating) as avg_rating FROM pm_comment WHERE item_type = \'product\' AND id_item = '.$product_id.' AND checked = 1 AND rating > 0 AND rating <= 5');
                        if($result_rating !== false && $db->last_row_count() == 1){
                            $row = $result_rating->fetch();
                            $rating = round($row['avg_rating'], 1);
                            $count_rating = $row['count_rating'];
                            
                            if($rating > 0 && $rating <= 5){ ?>
                            
                                <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                                    <meta itemprop="ratingValue" content="<?php echo $rating; ?>">
                                    <meta itemprop="ratingCount" content="<?php echo $count_rating; ?>">
                                    <input type="hidden" class="rating" value="<?php echo $rating; ?>" data-size="xs" readonly="true" data-default-caption="<?php echo $rating.'/5 - '.$count_rating.' '.mb_strtolower($texts['RATINGS']); ?>" data-show-caption="true">
                                </div>
                                <?php
                            }
                        } ?>
                        <?php
                        $nb_comments = 0;
                        $item_type = 'product';
                        $item_id = $product_id;
                        $allow_comment = $page['comment'];
                        $allow_rating = $page['rating'];
                        if($allow_comment == 1){
                            $result_comment = $db->query('SELECT * FROM pm_comment WHERE id_item = '.$item_id.' AND item_type = \''.$item_type.'\' AND checked = 1 ORDER BY add_date DESC');
                            if($result_comment !== false)
                                $nb_comments = $db->last_row_count();
                        } ?>
                        <!--<div class="mb10 mt10 labels" dir="ltr">
                            <span class="label label-default"><i class="fa fa-comment"></i> <?php echo $nb_comments.' '.mb_strtolower($texts['COMMENTS'], 'UTF-8'); ?></span>
                        </div>-->
                        
                        <div itemprop="description" class="mb10">
                            <h2>
                                <?php echo $product['title']; ?><br>
                                <small>
                                    <?php
                                    echo $product['subtitle'];
                                    if($product['new'] == 1) echo ' <span class="label label-warning">'.$texts['NEW'].'</span>'; ?>
                                </small>
                            </h2>
                            <?php
                            echo $product['short_descr']; ?>
                        </div>
                        <meta itemprop="brand manufacturer" content="<?php echo SITE_TITLE; ?>">
                        
                        <div class="row">
                            <?php
                            $query_product_file = 'SELECT * FROM pm_product_file WHERE id_item = '.$product_id.' AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'other\'';
                            if(LANG_ID != DEFAULT_LANG) $query_product_file .= ' AND file LIKE \'%-'.LANG_TAG.'.pdf\'';
                            else $query_product_file .= ' AND file LIKE \'%.pdf\'';
                            $query_product_file .= ' ORDER BY rank LIMIT 1';
                            $result_product_file = $db->query($query_product_file);
                            if($result_product_file !== false && $db->last_row_count() > 0){
                                
                                $row = $result_product_file->fetch(PDO::FETCH_ASSOC);
                                
                                $file_id = $row['id'];
                                $filename = $row['file'];
                                $label = $row['label'];
                                
                                $realpath = SYSBASE.'medias/product/other/'.$file_id.'/'.$filename;
                                $docpath = DOCBASE.'medias/product/other/'.$file_id.'/'.$filename;
                                
                                if(is_file($realpath)){ ?>
                                    
                                    <a itemprop="url" href="<?php echo $docpath; ?>" target="_blank">
                                        <div class="col-sm-4">
                                            <div class="product-action doc-action">
                                                <i class="fa fa-download"></i><br>
                                                <?php echo $texts['DOWNLOAD_DOC']; ?>
                                            </div>
                                        </div>
                                    </a>
                                    <!--<div id="pdf-doc-product" class="pdf-popup popup-block mfp-hide">
                                        <div class="flipbook3d" data-pdfsrc="<?php echo $docpath; ?>"></div>
                                    </div>-->
                                    <?php
                                }
                            }
                            if($product['mad'] == 1){ ?>
                                <a href="<?php echo DOCBASE.$pages[25]['alias']; ?>">
                                    <div class="col-sm-4">
                                        <div class="product-action mad-action">
                                            <i class="fa fa-thumbs-up"></i><br>
                                            <?php echo $texts['MAD']; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php
                            } ?>
                            <a href="<?php echo DOCBASE.$pages[24]['alias'].'?id='.$product_id; ?>">
                                <div class="col-sm-4">
                                    <div class="product-action contact-action">
                                        <i class="fa fa-envelope"></i><br>
                                        <?php echo $texts['INFORMATION_REQUEST']; ?>
                                    </div>
                                </div>
                            </a>
                            <?php
                            $result_gallery = $db->query('SELECT * FROM pm_article WHERE checked = 1 AND lang = '.LANG_ID.' AND products REGEXP \'(^|,)'.$product_id.'(,|$)\' ORDER BY rank LIMIT 1');
                            if($result_gallery !== false && $db->last_row_count() > 0){
                                $row = $result_gallery->fetch(); ?>
                                
                                <a href="<?php echo DOCBASE.$pages[31]['alias'].'/'.text_format($row['alias']); ?>">
                                    <div class="col-sm-4">
                                        <div class="product-action">
                                            <i class="fas fa-images"></i><br>
                                            <?php echo $texts['VIEW_PHOTOS_GALLERY']; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php
                            }
                            $result_formation = $db->query('SELECT * FROM pm_formation WHERE id_page = '.$page_id.' AND checked = 1 LIMIT 1');
                            if($result_formation !== false && $db->last_row_count() > 0){
                                $row = $result_formation->fetch(PDO::FETCH_ASSOC);
                                $formation_alias = DOCBASE.$pages[7]['alias'].'/'.text_format($row['alias']); ?>
                                <a href="<?php echo $formation_alias; ?>">
                                    <div class="col-sm-4">
                                        <div class="product-action">
                                            <i class="fa fa-user-md"></i><br>
                                            <?php echo $texts['TRAINING_SCHEDULE']; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php
                            } ?>
                            <a href="#product-details">
                                <div class="col-sm-4">
                                    <div class="product-action">
                                        <i class="fa fa-plus-circle"></i><br>
                                        <b><?php echo $texts['USE_VIDEO_REVIEWS']; ?></b>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div id="product-details">
                <div class="panel panel-default">
                    <div class="container">
                        <ul class="nav nav-tabs">
                            <?php
                            if($product['presentation'] != ''){ ?>
                                <li<?php if(!isset($msg_error) || $msg_error == '') echo ' class="active"'; ?>><a data-toggle="tab" href="#presentation-prod"><?php echo $texts['PRESENTATION']; ?></a></li>
                                <?php
                            }
                            if($product['utilisation'] != ''){ ?>
                                <li<?php if((!isset($msg_error) || $msg_error == '') && $product['presentation'] == '') echo ' class="active"'; ?>><a data-toggle="tab" href="#utilisation-prod"><?php echo $texts['USE']; ?></a></li>
                                <?php
                            }
                            if($product['fiche'] != ''){ ?>
                                <li<?php if((!isset($msg_error) || $msg_error == '') && $product['presentation'] == '' && $product['utilisation'] == '') echo ' class="active"'; ?>><a data-toggle="tab" href="#technique-prod"><?php echo $texts['TECHNICAL_SHEET']; ?></a></li>
                                <?php
                            } ?>
                            <li<?php if((isset($msg_error) && $msg_error == '') || ($product['fiche'] == '' && $product['presentation'] == '' && $product['utilisation'] == '')) echo ' class="active"'; ?>><a data-toggle="tab" href="#reviews-prod"><?php echo $texts['REVIEWS']; ?></a></li>
                        </ul>
                    </div>
                    <hr id="divider">
                    <div id="product-details-bg"<?php if(empty($product['videos'])) echo ' class="noVideo"'; ?>>
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="panel-body">
                                        <div class="tab-content">
                                            <div id="presentation-prod" class="tab-pane fade<?php if((!isset($msg_error) || $msg_error == '') && $product['presentation'] != '') echo ' in active'; ?>">
                                                <?php echo $product['presentation']; ?>
                                            </div>
                                            <div id="utilisation-prod" class="tab-pane fade<?php if((!isset($msg_error) || $msg_error == '') && $product['utilisation'] != '' && $product['presentation'] == '') echo ' in active'; ?>">
                                                <?php echo $product['utilisation']; ?>
                                            </div>
                                            <div id="technique-prod" class="tab-pane fade<?php if((!isset($msg_error) || $msg_error == '') && $product['fiche'] != '' && $product['presentation'] == '' && $product['utilisation'] == '') echo ' in active'; ?>">
                                                <?php echo $product['fiche']; ?>
                                            </div>
                                            <div id="reviews-prod" class="tab-pane fade<?php if((isset($msg_error) && $msg_error == '') || ($product['fiche'] == '' && $product['presentation'] == '' && $product['utilisation'] == '')) echo ' in active'; ?>">
                                                <?php include(getFromTemplate('common/comments.php', false)); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <?php
                                    if(!empty($product['videos'])){ ?>
                                        <h3 class="mt15 mb15"><?php echo $product['title']; ?> <strong><?php echo $texts['IN_VIDEO']; ?></strong></h3>
                                        <?php
                                        $videos = explode("\n", $product['videos']); ?>
                                        <div class="owl-carousel owlWrapper" data-items="1" data-autoplay="false" data-dots="true" data-nav="false" data-rtl="<?php echo (RTL_DIR) ? "true" : "false"; ?>">
                                            <?php
                                            foreach($videos as $video){ ?>
                                                <div class="video-container">
                                                    <iframe src="<?php echo $video; ?>" frameborder="0" allowfullscreen></iframe>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                        <?php
                                    }/*else{
                                        $result_product_file = $db->query('SELECT * FROM pm_product_file WHERE id_item = '.$product_id.' AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                                        if($result_product_file !== false && $db->last_row_count() > 0){
                                            
                                            $row = $result_product_file->fetch(PDO::FETCH_ASSOC);
                                            
                                            $file_id = $row['id'];
                                            $filename = $row['file'];
                                            $label = $row['label'];
                                            
                                            $realpath = SYSBASE.'medias/product/big/'.$file_id.'/'.$filename;
                                            $zoompath = DOCBASE.'medias/product/big/'.$file_id.'/'.$filename;
                                            $thumbpath = DOCBASE.'medias/product/big/'.$file_id.'/'.$filename;
                                            
                                            if(is_file($realpath)){ ?>
                                                <img class="img-responsive" src="<?php echo $thumbpath; ?>" alt="<?php echo $label; ?>">
                                                <?php
                                            }
                                        }
                                    }*/ ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <section class="mt30">
            <div class="container">
                <h2 class="underline"><?php echo $texts['DISCOVER_ALSO']; ?></h2>
                <div class="row">
                    <div class="isotopeWrapper isotope clearfix">
                        <?php
                        $query_product = 'SELECT * FROM pm_product WHERE checked = 1 AND lang = '.LANG_ID.' AND id != '.$product_id.' AND (';
                        if(!empty($product['products'])) $query_product .= 'id IN('.$product['products'].') OR ';
                        $query_product .= 'products REGEXP \'(^|,)'.$product_id.'(,|$)\') ORDER BY rank LIMIT 3';
                        $result_product = $db->query($query_product);
                        
                        $nb_products = $db->last_row_count();
                        
                        $id_product = 0;
                        $result_product_file = $db->prepare('SELECT * FROM pm_product_file WHERE id_item = :id_product AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                        $result_product_file->bindParam(':id_product', $id_product);

                        foreach($result_product as $i => $row){
                                                    
                            $id_product = $row['id'];
                            $product_title = $row['title'];
                            $product_alias = $row['alias'];
                            $product_new = $row['new'];
                            $product_descr = strtrunc(strip_tags($row['short_descr']),170);
                            
                            $product_alias = DOCBASE.$page['alias'].'/'.text_format($product_alias);
                            
                            echo '
                            <article class="col-xs-12 col-sm-4 isotopeItem" itemscope itemtype="http://schema.org/Article">
                                <div class="isotopeInner">
                                    <a itemprop="url" href="'.$product_alias.'">
                                        <figure class="more-link img-thumb">';
                                            if($result_product_file->execute() !== false && $db->last_row_count() == 1){
                                                $row = $result_product_file->fetch(PDO::FETCH_ASSOC);
                                                
                                                $file_id = $row['id'];
                                                $filename = $row['file'];
                                                $label = $row['label'];
                                                
                                                $realpath = SYSBASE.'medias/product/big/'.$file_id.'/'.$filename;
                                                $thumbpath = DOCBASE.'medias/product/big/'.$file_id.'/'.$filename;
                                                $zoompath = DOCBASE.'medias/product/big/'.$file_id.'/'.$filename;
                                                
                                                if(is_file($realpath)){
                                                    echo '<img alt="'.$label.'" src="'.$thumbpath.'">';
                                                }
                                            }
                                            echo '
                                            <span class="more-action">
                                                <span class="more-icon"></span>
                                            </span>';
                                            if($product_new == 1) echo '<span class="corner-ribbon">'.$texts['NEW'].'</span>';
                                            echo '
                                        </figure>
                                        <div class="isotopeContent">
                                            <div class="text-overflow text-center">
                                            <h3 itemprop="name">'.$product_title.'</h3>';
                                            /*if($product_descr != '') echo '<p>'.$product_descr.'</p>';
                                            echo '
                                            <div class="more-btn">
                                                <span class="btn btn-primary">'.$texts['DISCOVER'].'</span>
                                            </div>*/
                                            echo '
                                        </div>
                                    </a>
                                </div>
                            </article>';
                        }
                        $lz_offset = 1;
                        $lz_limit = 3-$nb_products;
                        if($lz_limit > 0)
                            include(getFromTemplate('common/get_products.php', false)); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</article>
