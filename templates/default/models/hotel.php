<?php

if($article_alias == '') err404();


$result = $db->query('SELECT * FROM pm_hotel WHERE checked = 1 AND lang = '.LANG_ID.' AND alias = '.$db->quote($article_alias));

if($result !== false && $db->last_row_count() == 1){
    
    $hotel = $result->fetch(PDO::FETCH_ASSOC);
    
    $hotel_id = $hotel['id'];
    $article_id = $hotel_id;
    $title_tag = $hotel['title'].' - '.$title_tag;
    $page_title = $hotel['title'];
    $page_subtitle = '';
    $page_alias = $pages[$page_id]['alias'].'/'.text_format($hotel['alias']);
    
    $result_hotel_file = $db->query('SELECT * FROM pm_hotel_file WHERE id_item = '.$hotel_id.' AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
    if($result_hotel_file !== false && $db->last_row_count() > 0){
        
        $row = $result_hotel_file->fetch();
        
        $file_id = $row['id'];
        $filename = $row['file'];
        
        if(is_file(SYSBASE.'medias/hotel/medium/'.$file_id.'/'.$filename))
            $page_img = getUrl(true).DOCBASE.'medias/hotel/medium/'.$file_id.'/'.$filename;
    }
    
}else err404();

check_URI(DOCBASE.$page_alias);

/* ==============================================
 * CSS AND JAVASCRIPT USED IN THIS MODEL
 * ==============================================
 */
$javascripts[] = DOCBASE.'js/plugins/sharrre/jquery.sharrre.min.js';

$javascripts[] = DOCBASE.'js/plugins/jquery.event.calendar/js/jquery.event.calendar.js';
$javascripts[] = DOCBASE.'js/plugins/jquery.event.calendar/js/languages/jquery.event.calendar.'.LANG_TAG.'.js';
$stylesheets[] = array('file' => DOCBASE.'js/plugins/jquery.event.calendar/css/jquery.event.calendar.css', 'media' => 'all');

$stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/assets/owl.carousel.min.css', 'media' => 'all');
$stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/assets/owl.theme.default.min.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/owl.carousel.min.js';

$stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/3.5.5/css/star-rating.min.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/3.5.5/js/star-rating.min.js';

$stylesheets[] = array('file' => DOCBASE.'js/plugins/isotope/css/style.css', 'media' => 'all');
$javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/jquery.isotope/1.5.25/jquery.isotope.min.js';
$javascripts[] = DOCBASE.'js/plugins/isotope/jquery.isotope.sloppy-masonry.min.js';

$stylesheets[] = array('file' => DOCBASE.'js/plugins/lazyloader/lazyloader.css', 'media' => 'all');
$javascripts[] = DOCBASE.'js/plugins/lazyloader/lazyloader.js';

$javascripts[] = DOCBASE.'js/plugins/live-search/jquery.liveSearch.js';

require(getFromTemplate('common/send_comment.php', false));

require(getFromTemplate('common/header.php', false));
$field_notice = array();

$num_people = $_SESSION['num_adults']+$_SESSION['num_children'];

if(!is_numeric($_SESSION['num_adults'])) $field_notice['num_adults'] = $texts['REQUIRED_FIELD'];
if(!is_numeric($_SESSION['num_children'])) $field_notice['num_children'] = $texts['REQUIRED_FIELD'];

if($_SESSION['from_date'] == '') $field_notice['dates'] = $texts['REQUIRED_FIELD'];
else{
    $time = explode('/', $_SESSION['from_date']);
    if(count($time) == 3) $time = gm_strtotime($time[2].'-'.$time[1].'-'.$time[0].' 00:00:00');
    if(!is_numeric($time)) $field_notice['dates'] = $texts['REQUIRED_FIELD'];
    else $from_time = $time;
}
if($_SESSION['to_date'] == '') $field_notice['dates'] = $texts['REQUIRED_FIELD'];
else{
    $time = explode('/', $_SESSION['to_date']);
    if(count($time) == 3) $time = gm_strtotime($time[2].'-'.$time[1].'-'.$time[0].' 00:00:00');
    if(!is_numeric($time)) $field_notice['dates'] = $texts['REQUIRED_FIELD'];
    else $to_time = $time;
}

$today = gm_strtotime(gmdate('Y').'-'.gmdate('n').'-'.gmdate('j').' 00:00:00');

if($from_time < $today || $to_time < $today || $to_time <= $from_time){
    $from_time = $today;
    $to_time = $today+86400;
    $_SESSION['from_date'] = gmdate('d/m/Y', $from_time);
    $_SESSION['to_date'] = gmdate('d/m/Y', $to_time);
}

if(is_numeric($from_time) && is_numeric($to_time)){
    $num_nights = ($to_time-$from_time)/86400;
}else
    $num_nights = 0;

$hotel_ids = array();
$room_ids = array();

if(count($field_notice) == 0){

    if($num_nights <= 0) $msg_error .= $texts['NO_AVAILABILITY'];
    else{
        require_once(getFromTemplate('common/functions.php', false));
        $res_hotel = getRoomsResult($from_time, $to_time, $_SESSION['num_adults'], $_SESSION['num_children']);

        if(empty($res_hotel)) $msg_error .= $texts['NO_AVAILABILITY'];
        else $_SESSION['res_hotel'] = $res_hotel;
    }
}



$id_room = 0;

$result_room_rate = $db->prepare('SELECT MIN(price) as min_price FROM pm_rate WHERE id_room = :id_room');
$result_room_rate->bindParam(':id_room', $id_room);

$id_hotel = 0;

$result_budget_room = $db->prepare('SELECT * FROM pm_room WHERE id_hotel = :id_hotel AND checked = 1  AND  lang = '.LANG_ID);
$result_budget_room->bindParam(':id_hotel', $id_hotel);

$hidden_hotels = array();
$hidden_rooms = array();
$room_prices = array();
$hotel_prices = array();
$result_budget_hotel = $db->query('SELECT * FROM pm_hotel WHERE checked = 1 AND lang = '.LANG_ID);
if($result_budget_hotel !== false){
    foreach($result_budget_hotel as $i => $row){
        $id_hotel = $row['id'];
        $hotel_min_price = 0;
        $hotel_max_price = 0;
        $result_budget_room->execute();
        if($result_budget_room !== false){
            foreach($result_budget_room as $row){
                
                $id_room = $row['id'];
                $room_price = $row['price'];
                $max_people = $row['max_people'];
                $min_people = $row['min_people'];
                $max_adults = $row['max_adults'];
                $max_children = $row['max_children'];
                
                if(!isset($res_hotel[$id_hotel][$id_room])
                || isset($res_hotel[$id_hotel][$id_room]['error'])
                || ($_SESSION['num_adults']+$_SESSION['num_children'] > $max_people)
                || ($_SESSION['num_adults']+$_SESSION['num_children'] < $min_people)
                || ($_SESSION['num_adults'] > $max_adults)
                || ($_SESSION['num_children'] > $max_children)){
                    $amount = $room_price;
                    $result_room_rate->execute();
                    if($result_room_rate !== false && $db->last_row_count() > 0){
                        $row = $result_room_rate->fetch();
                        if($row['min_price'] > 0) $amount = $row['min_price'];
                    }
                    $full_price = 0;
                    $type = $texts['NIGHT'];
                    $price_night = $amount;
                }else{
                    $amount = $res_hotel[$id_hotel][$id_room]['amount']+$res_hotel[$id_hotel][$id_room]['fixed_sup'];
                    $full_price = $res_hotel[$id_hotel][$id_room]['full_price']+$res_hotel[$id_hotel][$id_room]['fixed_sup'];
                    $type = $num_nights.' '.$texts['NIGHTS'];
                    $price_night = $amount/$num_nights;
                }
                
                if((!empty($price_min) && $price_night < $price_min) || (!empty($price_max) && $price_night > $price_max)) $hidden_rooms[] = $id_room;
                else{
                    $room_prices[$id_room]['amount'] = $amount;
                    $room_prices[$id_room]['full_price'] = $full_price;
                    $room_prices[$id_room]['type'] = $type;
                }
                if(empty($hotel_min_price) || $price_night < $hotel_min_price) $hotel_min_price = $price_night;
                if(empty($hotel_max_price) || $price_night > $hotel_max_price) $hotel_max_price = $price_night;
            }
        } 
        if((!empty($price_min) && $hotel_max_price < $price_min) || (!empty($price_max) && $hotel_min_price > $price_max)) $hidden_hotels[] = $id_hotel;
        $hotel_prices[$id_hotel] = $hotel_min_price;
    }
}

$result_rating = $db->prepare('SELECT AVG(rating) as avg_rating FROM pm_comment WHERE item_type = \'hotel\' AND id_item = :id_hotel AND checked = 1 AND rating > 0 AND rating <= 5');
$result_rating->bindParam(':id_hotel', $id_hotel);
                
$id_facility = 0;
$result_facility_file = $db->prepare('SELECT * FROM pm_facility_file WHERE id_item = :id_facility AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
$result_facility_file->bindParam(':id_facility', $id_facility);

$room_facilities = '0';
$result_room_facilities = $db->prepare('SELECT * FROM pm_facility WHERE lang = '.LANG_ID.' AND FIND_IN_SET(id, :room_facilities) ORDER BY rank LIMIT 18');
$result_room_facilities->bindParam(':room_facilities', $room_facilities);

$hotel_facilities = '0';
$result_hotel_facilities = $db->prepare('SELECT * FROM pm_facility WHERE lang = '.LANG_ID.' AND FIND_IN_SET(id, :hotel_facilities) ORDER BY rank LIMIT 8');
$result_hotel_facilities->bindParam(':hotel_facilities', $hotel_facilities);

$query_room = 'SELECT * FROM pm_room WHERE id_hotel = :id_hotel AND checked = 1 AND lang = '.LANG_ID;
if(!empty($hidden_rooms)) $query_room .= ' AND id NOT IN('.implode(',', $hidden_rooms).')';
$query_room .= ' ORDER BY';
if(!empty($room_ids)) $query_room .= ' CASE WHEN id IN('.implode(',', $room_ids).') THEN 3 ELSE 4 END,';
$query_room .= ' rank';
$result_room = $db->prepare($query_room);
$result_room->bindParam(':id_hotel', $id_hotel);



?>
<style>
   .img-container.sm{
    left:0;
   } 
</style>
<section id="page">
    
    <?php include(getFromTemplate('common/page_header.php', false)); ?>
    
    <div id="content" class="pb30">
    
        <div id="search-page" class="mb30">
            <div class="container">
                <?php include(getFromTemplate('common/search.php', false)); ?>
            </div>
        </div>
        
        <div class="container">
            <div class="alert alert-success" style="display:none;"></div>
            <div class="alert alert-danger" style="display:none;"></div>
        </div>
    
        <article class="container">
            <div class="row"> <form action="<?php echo DOCBASE.$sys_pages['booking']['alias']; ?>" method="post" class="ajax-form">
                <div class="col-md-8 mb20">
                    <div class="row mb10">
                        <div class="col-sm-8">
                            <h1 class="mb0">
                                <?php echo  $hotel_title = $hotel['title'];   ?>
                                <small>
                                    <?php
                                    if(!empty($hotel['class'])){
                                        for($j = 0; $j < $hotel['class']; $j++) echo '<i class=\"fas fa-fw fa-star\"></i>';
                                    } ?>
                                </small>
                                <br><small><?php echo $hotel['subtitle']; ?></small>
                            </h1>
                            <?php
                            $result_rating = $db->query('SELECT count(*) as count_rating, AVG(rating) as avg_rating FROM pm_comment WHERE item_type = \'hotel\' AND id_item = '.$hotel_id.' AND checked = 1 AND rating > 0 AND rating <= 5');
                            if($result_rating !== false && $db->last_row_count() == 1){
                                $row = $result_rating->fetch();
                                $hotel_rating = $row['avg_rating'];
                                $count_rating = $row['count_rating'];
                                
                                if($hotel_rating > 0 && $hotel_rating <= 5){ ?>
                                
                                    <input type="hidden" class="rating pull-left" value="<?php echo $hotel_rating; ?>" data-rtl="<?php echo (RTL_DIR) ? true : false; ?>" data-size="xs" readonly="true" data-default-caption="<?php echo $count_rating.' '.$texts['RATINGS']; ?>" data-show-caption="true" data-show-clear="false">
                                    <?php
                                }
                            } ?>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <div class="price text-primary">
                                <?php
                                $min_price = 0;
                                $result_rate = $db->query('
                                    SELECT MIN(ra.price) as min_price
                                    FROM pm_rate as ra, pm_room as ro
                                    WHERE ro.id = id_room AND ro.id_hotel = '.$hotel_id);
                                if($result_rate !== false && $db->last_row_count() > 0){
                                    $row = $result_rate->fetch();
                                    $price = $row['min_price'];
                                    if($price > 0) $min_price = $price;
                                }
                                if($min_price > 0){
                                    echo $texts['FROM_PRICE']; ?>
                                    <span itemprop="priceRange">
                                        <?php echo formatPrice($min_price*CURRENCY_RATE); ?>
                                    </span>
                                    / <?php echo $texts['NIGHT'];
                                } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mb10">
                        <div class="col-sm-12">
                            <?php
                            $result_facility = $db->query('SELECT * FROM pm_facility WHERE lang = '.LANG_ID.' AND id IN('.$hotel['facilities'].') ORDER BY id',PDO::FETCH_ASSOC);
                            if($result_facility !== false && $db->last_row_count() > 0){
                                foreach($result_facility as $i => $row){
                                    $facility_id    = $row['id'];
                                    $facility_name  = $row['name'];
                                    
                                    $result_facility_file = $db->query('SELECT * FROM pm_facility_file WHERE id_item = '.$facility_id.' AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1',PDO::FETCH_ASSOC);
                                    if($result_facility_file !== false && $db->last_row_count() == 1){
                                        $row = $result_facility_file->fetch();
                                        
                                        $file_id    = $row['id'];
                                        $filename   = $row['file'];
                                        $label      = $row['label'];
                                        
                                        $realpath   = SYSBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                        $thumbpath  = DOCBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                            
                                        if(is_file($realpath)){ ?>
                                            <span class="facility-icon">
                                                <img alt="<?php echo $facility_name; ?>" title="<?php echo $facility_name; ?>" src="<?php echo $thumbpath; ?>" class="tips">
                                            </span>
                                            <?php
                                        }
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                    <div class="row mb10">
                        <div class="col-md-12">
                            <div class="owl-carousel owlWrapper" data-items="1" data-autoplay="true" data-dots="true" data-nav="false" data-rtl="<?php echo (RTL_DIR) ? 'true' : 'false'; ?>">
                                <?php
                                $result_hotel_file = $db->query('SELECT * FROM pm_hotel_file WHERE id_item = '.$hotel_id.' AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank');
                                if($result_hotel_file !== false){
                                    
                                    foreach($result_hotel_file as $i => $row){
                                    
                                        $file_id = $row['id'];
                                        $filename = $row['file'];
                                        $label = $row['label'];
                                        
                                        $realpath = SYSBASE.'medias/hotel/big/'.$file_id.'/'.$filename;
                                        $thumbpath = DOCBASE.'medias/hotel/big/'.$file_id.'/'.$filename;
                                        
                                        if(is_file($realpath)){ ?>
                                            <img alt="<?php echo $label; ?>" src="<?php echo $thumbpath; ?>" class="img-responsive" style="max-height:600px;"/>
                                            <?php
                                        }
                                    }
                                } ?>
                            </div>
                        </div>
                    </div>
                
                    
                    
                <?php
                        $id_facility = 0;
                        $result_facility_file = $db->prepare('SELECT * FROM pm_facility_file WHERE id_item = :id_facility AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                        $result_facility_file->bindParam(':id_facility', $id_facility);

                        $room_facilities = '0';
                        $result_facility = $db->prepare('SELECT * FROM pm_facility WHERE lang = '.LANG_ID.' AND FIND_IN_SET(id, :room_facilities) ORDER BY rank LIMIT 8');
                        $result_facility->bindParam(':room_facilities', $room_facilities);
            
                        $id_room = 0;
                        $result_rate = $db->prepare('SELECT MIN(price) as price FROM pm_rate WHERE id_room = :id_room');
                        $result_rate->bindParam(':id_room', $id_room);
                        
                        $result_room_file = $db->prepare('SELECT * FROM pm_room_file WHERE id_item = :id_room AND checked = 1 AND lang = '.LANG_ID.' AND type = \'image\' AND file != \'\' ORDER BY rank');
                        $result_room_file->bindParam(':id_room', $id_room, PDO::PARAM_STR);
                
                        $result_room = $db->query('SELECT * FROM pm_room WHERE id_hotel = '.$hotel_id.' AND checked = 1 AND lang = '.LANG_ID.' ORDER BY rank', PDO::FETCH_ASSOC);

                        if($result_room !== false && $db->last_row_count() > 0){ ?>
                            <p class="widget-title"><?php echo $texts['ROOMS']; ?></p>
                           <div class="boxed" style="display: none;"> 
                             

                            <?php
                            foreach($result_room as $i => $row){
                                $id_room = $row['id'];
                                $room_title = $row['title'];
                                $room_subtitle = $row['subtitle'];
                                $room_descr = $row['descr'];
                                $room_alias = $row['alias'];
                                $room_facilities = $row['facilities'];
                                $max_people = $row['max_people'];
                                $room_price = $row['price']; ?>
                                
                                <a class="popup-modal" href="#room-<?php echo $id_room; ?>">
                                    <div class="row">
                                        <div class="col-xs-4 mb20">
                                            <?php
                                            $result_room_file->execute();
                                            if($result_room_file !== false && $db->last_row_count() > 0){
                                                $row = $result_room_file->fetch(PDO::FETCH_ASSOC);
                                                
                                                $file_id = $row['id'];
                                                $filename = $row['file'];
                                                $label = $row['label'];
                                                
                                                $realpath = SYSBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                                $thumbpath = DOCBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                                    
                                                if(is_file($realpath)){ ?>
                                                    <div class="img-container sm">
                                                        <img alt="" src="<?php echo $thumbpath; ?>">
                                                    </div>
                                                    <?php
                                                }
                                            } ?>
                                        </div>
                                        <div class="col-xs-8">
                                            <h3 class="mb0"><?php echo $room_title; ?></h3>
                                            <h4 class="mb0"><?php echo $room_subtitle; ?></h4>
                                            <?php
                                            $min_price = $room_price;
                                            if($result_rate->execute() !== false && $db->last_row_count() > 0){
                                                $row = $result_rate->fetch();
                                                $price = $row['price'];
                                                if($price > 0) $min_price = $price;
                                            } ?>
                                            <div class="price text-primary">
                                                <?php echo $texts['FROM_PRICE']; ?>
                                                <span itemprop="priceRange">
                                                    <?php echo formatPrice($min_price*CURRENCY_RATE); ?>
                                                </span>
                                                / <?php echo $texts['NIGHT']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <div id="room-<?php echo $id_room; ?>" class="white-popup-block mfp-hide">
                                    <div class="fluid-container">
                                        <div class="row">
                                            <div class="col-xs-12 mb20">
                                                <div class="owl-carousel" data-items="1" data-autoplay="true" data-dots="true" data-nav="false" data-rtl="<?php echo (RTL_DIR) ? 'true' : 'false'; ?>">
                                                    <?php
                                                    $result_room_file->execute();
                                                    if($result_room_file !== false){
                                                        foreach($result_room_file as $i => $row){
                                    
                                                            $file_id = $row['id'];
                                                            $filename = $row['file'];
                                                            $label = $row['label'];
                                                            
                                                            $realpath = SYSBASE.'medias/room/medium/'.$file_id.'/'.$filename;
                                                            $thumbpath = DOCBASE.'medias/room/medium/'.$file_id.'/'.$filename;
                                                            
                                                            if(is_file($realpath)){ ?>
                                                                <div><img alt="<?php echo $label; ?>" src="<?php echo $thumbpath; ?>" class="img-responsive" style="max-height:600px;"></div>
                                                                <?php
                                                            }
                                                        }
                                                    } ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <h3 class="mb0"><?php echo $room_title; ?></h3>
                                                <h4 class="mb0"><?php echo $room_subtitle; ?></h4>
                                            </div>
                                            <div class="col-sm-4 text-right">
                                                <?php
                                                $min_price = $room_price;
                                                if($result_rate->execute() !== false && $db->last_row_count() > 0){
                                                    $row = $result_rate->fetch();
                                                    $price = $row['price'];
                                                    if($price > 0) $min_price = $price;
                                                }
                                                $type = $texts['NIGHT']; ?>
                                                <div class="price text-primary">
                                                    <?php echo $texts['FROM_PRICE']; ?>
                                                    <span itemprop="priceRange">
                                                        <?php echo formatPrice($min_price*CURRENCY_RATE); ?>
                                                    </span>
                                                    / <?php echo $type; ?>
                                                </div>
                                                <p>
                                                    <?php echo $texts['CAPACITY']; ?> : <i class="fas fa-fw fa-male"></i>x<?php echo $max_people; ?>
                                                </p>
                                            </div>
                                            <div class="col-xs-12">
                                                <div class="clearfix mb5">
                                                    <?php
                                                    $result_facility->execute();
                                                    if($result_facility !== false && $db->last_row_count() > 0){
                                                        foreach($result_facility as $row){
                                                            $id_facility = $row['id'];
                                                            $facility_name = $row['name'];
                                                            
                                                            $result_facility_file->execute();
                                                            if($result_facility_file !== false && $db->last_row_count() == 1){
                                                                $row = $result_facility_file->fetch();
                                                                
                                                                $file_id = $row['id'];
                                                                $filename = $row['file'];
                                                                $label = $row['label'];
                                                                
                                                                $realpath = SYSBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                                                $thumbpath = DOCBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                                                    
                                                                if(is_file($realpath)){ ?>
                                                                    <span class="facility-icon">
                                                                        <img alt="<?php echo $facility_name; ?>" title="<?php echo $facility_name; ?>" src="<?php echo $thumbpath; ?>" class="tips">
                                                                    </span>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    } ?>
                                                </div>
                                                <?php echo $room_descr; ?>
                                            </div>
                                          
                                                           
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } ?>
                                   </div>




 <div class="row">
                                        <div class="">
                                             
                                <input type="hidden" name="from_time" value="<?php echo $from_time; ?>">
                                <input type="hidden" name="to_time" value="<?php echo $to_time; ?>">
                                <input type="hidden" name="nights" value="<?php echo $num_nights; ?>">
                                <input type="hidden" name="id_hotel" value="<?php echo $id_hotel; ?>">
                                <input type="hidden" name="hotel" value="<?php echo $hotel_title; ?>">
                                            <div class="boxed">
                                                <?php
                                                $result_room->execute();
                                                if($result_room !== false){
                                                    foreach($result_room as $row){
                                                        
                                                        $id_room = $row['id'];
                                                        $room_title = $row['title'];
                                                        $room_alias = $row['alias'];
                                                        $room_subtitle = $row['subtitle'];
                                                        $room_descr = $row['descr'];
                                                        $room_price = $row['price'];
                                                        $room_stock = $row['stock'];
                                                        $max_adults = $row['max_adults'];
                                                        $max_children = $row['max_children'];
                                                        $max_people = $row['max_people'];
                                                        $min_people = $row['min_people'];
                                                        $room_facilities = $row['facilities'];
                        
                                                        $room_stock = isset($res_hotel[$id_hotel][$id_room]['room_stock']) ? $res_hotel[$id_hotel][$id_room]['room_stock'] : $row['stock'];
                                                
                                                        $amount = $room_prices[$id_room]['amount'];
                                                        $full_price = $room_prices[$id_room]['full_price'];
                                                        $type = $room_prices[$id_room]['type']; ?>

                                                        <input type="hidden" name="rooms[]"  value="<?php echo $id_room; ?>">
                                                        <input type="hidden" name="room_<?php echo $id_room; ?>" value="<?php echo $room_title; ?>">
                                                            
                                                        <div class="row room-result">
                                                            <div class="col-lg-3 hidden-sm hidden-xs" >
                                                                <?php
                                                                $result_room_file->execute();
                                                                if($result_room_file !== false && $db->last_row_count() > 0){
                                                                    $row = $result_room_file->fetch(PDO::FETCH_ASSOC);

                                                                    $file_id = $row['id'];
                                                                    $filename = $row['file'];
                                                                    $label = $row['label'];

                                                                    $realpath = SYSBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                                                    $thumbpath = DOCBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                                                    $zoompath = DOCBASE.'medias/room/big/'.$file_id.'/'.$filename;

                                                                    if(is_file($realpath)){
                                                                        $s = getimagesize($realpath); ?>
                                                                        <div class="img-container lazyload md">
                                                                            <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            </div>
                                                            <div class="col-sm-4 col-md-5 col-lg-4">
                                                                <h4><?php echo $room_title; ?></h4>
                                                                <p><?php echo $room_subtitle; ?></p>
                                                                <?php echo strtrunc(strip_tags($room_descr), 100); ?>
                                                                <div class="clearfix mt10">
                                                                    <?php
                                                                    $result_room_facilities->execute();
                                                                    if($result_room_facilities !== false && $db->last_row_count() > 0){
                                                                        foreach($result_room_facilities as $row){
                                                                            $id_facility = $row['id'];
                                                                            $facility_name = $row['name'];
                                                                            
                                                                            $result_facility_file->execute();
                                                                            if($result_facility_file !== false && $db->last_row_count() > 0){
                                                                                $row = $result_facility_file->fetch();
                                                                                
                                                                                $file_id = $row['id'];
                                                                                $filename = $row['file'];
                                                                                $label = $row['label'];
                                                                                
                                                                                $realpath = SYSBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                                                                $thumbpath = DOCBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                                                                    
                                                                                if(is_file($realpath)){ ?>
                                                                                    <span class="facility-icon">
                                                                                        <img alt="<?php echo $facility_name; ?>" title="<?php echo $facility_name; ?>" src="<?php echo $thumbpath; ?>" class="tips">
                                                                                    </span>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                    } ?>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-5 col-md-3 col-sm-3 text-center sep">
                                                                <div class="price">
                                                                    <span itemprop="priceRange"><?php echo formatPrice($amount*CURRENCY_RATE); ?></span>
                                                                    <?php
                                                                    if($full_price > 0 && $full_price > $amount){ ?>
                                                                        <br><s class="text-warning"><?php echo formatPrice($full_price*CURRENCY_RATE); ?></s>
                                                                        <?php
                                                                    } ?>
                                                                </div>
                                                                <div class="mb10 text-muted"><?php echo $texts['PRICE'].' / '.$type; ?></div>
                                                                <?php echo $texts['CAPACITY']; ?> : <i class="fas fa-fw fa-male"></i>x<?php echo $max_people; ?>
                                                                
                                                                <?php
                                                                if($room_stock > 0){ ?>
                                                                    <div class="pt10 form-inline">
                                                                        <i class="fas fa-fw fa-tags"></i> <?php echo $texts['SELECT_ROOMS']; ?> &nbsp;
                                                                        <select name="num_rooms[<?php echo $id_room; ?>]" class="form-control btn-group-sm sendAjaxForm selectpicker" width="50%" data-target="#room-options-<?php echo $id_room; ?>" data-extratarget="#booking-amount_<?php echo $id_hotel; ?>" data-action="<?php echo getFromTemplate('common/change_num_rooms.php'); ?>?room=<?php echo $id_room; ?>">
                                                                            <?php
                                                                            for($i = 0; $i <= $room_stock; $i++){ ?>
                                                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                                                <?php
                                                                            } ?>
                                                                        </select>
                                                                    </div>
                                                                    <?php
                                                                }else{ ?>
                                                                    <div class="mt10 btn btn-danger btn-block" disabled="disabled"><?php echo $texts['NO_ROOM']; ?></div>
                                                                    <?php
                                                                } ?>
                                                
                                                                <p class="lead">
                                                                    <span class="clearfix"></span>
                                                                    <a class="btn btn-primary mt10 btn-block ajax-popup-link btn-sm" href="<?php echo getFromTemplate('common/room-popup.php', true); ?>" data-params="room=<?php echo $id_room; ?>">
                                                                        <i class="fas fa-fw fa-plus-circle"></i>
                                                                        <?php echo $texts['READMORE']; ?>
                                                                    </a>
                                                                </p>
                                                            </div>
                                                           
                                                            <div class="clearfix"></div>
                                                            <div id="room-options-<?php echo $id_room; ?>" class="room-options"></div>
                                                        </div>
                                                        <hr>
                                                        <?php
                                                    }
                                                } ?>

                                                                              <div class="row mb10">
                        <div class="col-md-12" itemprop="description">
                            <?php
                            echo $hotel['descr'];
                            
                            $short_text = strtrunc(strip_tags($hotel['descr']), 100);
                            $site_url = getUrl(); ?>
                  
                        </div>
                    </div>
                             


                                            </div>
                                        </div>
                        </div>

                                    
                         <div class="row mb10">
                        <div class="col-md-12" itemprop="description">
                            <?php
                       
                            
                            $short_text = strtrunc(strip_tags($hotel['descr']), 100);
                            $site_url = getUrl(); ?>
                           
                            <div id="twitter" data-url="<?php echo $site_url; ?>" data-text="<?php echo $short_text; ?>" data-title="Tweet"></div>
                            <div id="facebook" data-url="<?php echo $site_url; ?>" data-text="<?php echo $short_text; ?>" data-title="Like"></div>
                            <div id="googleplus" data-url="<?php echo $site_url; ?>" data-curl="<?php echo DOCBASE.'js/plugins/sharrre/sharrre.php'; ?>" data-text="<?php echo $short_text; ?>" data-title="+1"></div>
                        </div>
                    </div>
                    
                 
                    <div class="row mt30">
                        <?php
                        $lz_offset = 1;
                        $lz_limit = 9;
                        $lz_pages = 0;
                        $num_records = 0;
                        $result = $db->query('SELECT count(*) FROM pm_activity WHERE hotels REGEXP \'(^|,)'.$hotel_id.'(,|$)\' AND checked = 1 AND lang = '.LANG_ID);
                        if($result !== false){
                            $num_records = $result->fetchColumn(0);
                            $lz_pages = ceil($num_records/$lz_limit);
                        }
                        if($num_records > 0){ ?>
                            <h3><?php echo $texts['FIND_ACTIVITIES_AND_TOURS']; ?></h3>
                            <div class="isotopeWrapper clearfix isotope lazy-wrapper" data-loader="<?php echo getFromTemplate('common/get_activities.php'); ?>" data-mode="click" data-limit="<?php echo $lz_limit; ?>" data-pages="<?php echo $lz_pages; ?>" data-is_isotope="true" data-variables="page_id=<?php echo $sys_pages['activities']['id']; ?>&page_alias=<?php echo $sys_pages['activities']['alias']; ?>&hotel=<?php echo $hotel_id; ?>">
                                <?php include(getFromTemplate('common/get_activities.php', false)); ?>
                            </div>
                            <?php
                        } ?>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            $nb_comments = 0;
                            $item_type = 'hotel';
                            $item_id = $hotel_id;
                            $allow_comment = ALLOW_COMMENTS;
                            $allow_rating = ALLOW_RATINGS;
                            if($allow_comment == 1){
                                $result_comment = $db->query('SELECT * FROM pm_comment WHERE id_item = '.$item_id.' AND item_type = \''.$item_type.'\' AND checked = 1 ORDER BY add_date DESC');
                                if($result_comment !== false)
                                    $nb_comments = $db->last_row_count();
                            }
                         //   include(getFromTemplate('../common/comments.php', false)); ?>
                        </div>
                    </div>
                    
                    
                    
                </div>
                <aside class="col-md-4 mb20">
                    <div class="boxed">
                        <div itemscope itemtype="http://schema.org/Corporation">
                            <h3 itemprop="name"><?php echo $hotel['title']; ?></h3>
                            <address>
                                <p>
                                    <i class="fas fa-fw fa-map-marker"></i> <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><?php echo $hotel['address']; ?></span><br>
                                    <?php if($hotel['phone'] != '') : ?><i class="fas fa-fw fa-phone"></i> <span itemprop="telephone" dir="ltr"><?php echo $hotel['phone']; ?></span><br><?php endif; ?>
                                    <?php if($hotel['email'] != '') : ?><i class="fas fa-fw fa-envelope"></i> <a itemprop="email" dir="ltr" href="mailto:<?php echo $hotel['email']; ?>"><?php echo $hotel['email']; ?></a><br><?php endif; ?>
                                    <?php if($hotel['web'] != '') : ?><i class="fas fa-fw fa-globe"></i> <a dir="ltr" href="<?php echo strpos($hotel['web'], 'http') === false ? 'http://'.$hotel['web'] : $hotel['web']; ?>" target="_blank"><?php echo $hotel['web']; ?></a><?php endif; ?>
                                </p>
                            </address>
                        </div>
                        <script type="text/javascript">
                            var locations = [
                                ['<?php echo $hotel['title']; ?>', '<?php echo $hotel['address']; ?>', '<?php echo $hotel['lat']; ?>', '<?php echo $hotel['lng']; ?>']
                            ];
                        </script>
                        <div id="mapWrapper" class="mb10" data-marker="<?php echo getFromTemplate('images/marker.png'); ?>" data-api_key="<?php echo GMAPS_API_KEY; ?>"></div>

                          <div class="row">
                                        <div class="col-md-12">
                                            <div class="boxed mt10 booking-summary">
                                                <p class="lead mb0"><?php echo '<big><i class="fas fa-fw fa-calendar"></i> <b>'.gmstrftime(DATE_FORMAT, $from_time).'</b></big> <big><i class="fas fa-fw fa-arrow-right"></i> <b>'.gmstrftime(DATE_FORMAT, $to_time).'</b></big>'; ?></p>
                                                <span id="booking-amount_<?php echo $id_hotel; ?>">
                                                    <?php
                                                    $room_stock = 0;
                                                    $result_room->execute();
                                                    if($result_room !== false){
                                                        foreach($result_room as $row){
                                                            $id_room = $row['id'];
                                                            $room_stock += isset($res_hotel[$id_hotel][$id_room]['room_stock']) ? $res_hotel[$id_hotel][$id_room]['room_stock'] : $row['stock'];
                                                        }
                                                    }
                                                    
                                                    if($num_nights <= 0 || (empty($res_hotel[$id_hotel]) && $room_stock > 0) || (!empty($res_hotel[$id_hotel]) && $room_stock <= 0)){
                                                        echo '
                                                        <input type="hidden" name="adults" value="'.$_SESSION['num_adults'].'">
                                                        <input type="hidden" name="children" value="'.$_SESSION['num_children'].'">
                                                        ';
                                                    } ?>
                                                
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                        </form>
                    </div>
                </aside>
            </div>
        </article>
    </div>
</section>
<script>
    $(function(){
        $('.room-options').on('change', '[name^="num_children"]', function(){
             console.log('trig');
            var extraTarget = $(this).parents('.booking-result').find('[id^="booking-amount_"]').attr('id');
            console.log(extraTarget);
            var attr = $(this).attr('name').match(/\[(\d+)\]\[(\d+)\]/);
            var target = $('#children-options-'+attr[1]+'-'+attr[2]);
            var num = $(this).val();
            var html = '<?php echo $texts['CHILDREN_AGE']; ?>:<br>';
            for(var i = 0; i < num; i++){
                html +=
                '<div class="input-group input-group-sm">'+
                    '<div class="input-group-addon">Child '+(i+1)+'</div>'+
                        '<select name="child_age['+attr[1]+']['+attr[2]+']['+i+']" class="form-control sendAjaxForm selectpicker" data-extratarget="#'+extraTarget+'" data-action="<?php echo getFromTemplate('common/change_num_people.php'); ?>?index='+attr[2]+'&id_room='+attr[1]+'" data-target="#room-result-'+attr[1]+'-'+attr[2]+'" style="display: none;">'+
                            '<option value="">-</option>';
                            for(var j = 0; j < 18; j++) html += '<option value="'+j+'">'+j+'</option>';
                            html +=
                        '</select>'+
                    '</div>'+
                '</div>';
            }
            target.html(html);
            $('.selectpicker').selectpicker('refresh');
        });
    });
</script>