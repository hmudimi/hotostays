<?php
/* ==============================================
 * CSS AND JAVASCRIPT USED IN THIS MODEL
 * ==============================================
 */


$javascripts[] = DOCBASE.'js/plugins/live-search/jquery.liveSearch.js';

require(getFromTemplate('common/header.php', false));

{ ?>
        
	   
	   <div id="search-home-wrapper">
            
           
			 <div id="search-home" class="container">
			 <div class="tabs">
  
  <input type="radio" id="tab1" name="tab-control" checked>
  <input type="radio" id="tab2" name="tab-control">
  <input type="radio" id="tab3" name="tab-control">  
  <div id="search-home1-wrapper">
  <div  id="search-home1" class="container1">
  <ul>
    <li title="Hotels"><label for="tab1" role="button"><br><span>Hotels</span></label></li>
    <li title="Banquet Hall"><label for="tab2" role="button"><br><span>Banquet</span></label></li>
    <li title="Lawn"><label for="tab3" role="button"><br><span>Lawn</span></label></li>    </ul></div></div>
<div class="content">
    <section>
      <h2 style="color:#fff">Hotels</h2>
<?php include(getFromTemplate('common/search.php', false)); ?>
</section>
        <section>
          <h2 style="color:#fff">Banquet</h2>
		  <?php include(getFromTemplate('common/searchhall.php', false)); ?></section>
<section>
          <h2 style="color:#fff">Lawn</h2>
         <?php include(getFromTemplate('common/searchlawn.php', false)); ?></section>
  </div>
</div>
                
            </div>
        </div>
	
	
<div id="search-home2-wrapper">
            <div  id="search-home2" class="container2">
			  <center>     <marquee id="scroll_news"><div onMouseOver="document.getElementById('scroll_news').stop();" onMouseOut="document.getElementById('scroll_news').start();"><img src="1.jpg"  height="150" width="250">
			  <img src="1.jpg"  height="150" width="250"> 
			  <img src="2.jpg"  height="150" width="250">
			  <img src="3.jpg"  height="150" width="250">
			  <img src="1.jpg"  height="150" width="250">
			  <img src="2.jpg"  height="150" width="250"> 
			  <img src="3.jpg"  height="150" width="250">
			  <img src="1.jpg"  height="150" width="250">
			  <img src="2.jpg"  height="150" width="250">
			  <img src="3.jpg"  height="150" width="250"> 
			  <img src="1.jpg"  height="150" width="250">
			  <img src="2.jpg"  height="150" width="250">
			  <img src="3.jpg"  height="150" width="250">
			  <img src="1.jpg"  height="150" width="250">
			  <img src="2.jpg"  height="150" width="250">
			  <img src="3.jpg"  height="150" width="250">
			  </div> </marquee>
			 </center>
      </div>  </div>  
	  

	    <div class="scon">

            </div>
			<?php
	}
?>
<section id="content" class="pt20 pb30">
    <div class="container mt10">
        <div class="row">
		                    
            <div class="col-md-12 text-center mb30">
                <h1 itemprop="name">
                    
                </h1>
    
            </div>
        </div>
        <div class="row mb10">
		<h2 class="text-center mt5 mb10">BEST HOTELS</h2><br>
            <?php
            $result_hotel = $db->query('SELECT * FROM pm_hotel WHERE lang = '.LANG_ID.' AND checked = 1 AND home = 1 ORDER BY rank');
            if($result_hotel !== false){
                $nb_hotels = $db->last_row_count();
                
                $hotel_id = 0;
                
                $result_hotel_file = $db->prepare('SELECT * FROM pm_hotel_file WHERE id_item = :hotel_id AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                $result_hotel_file->bindParam(':hotel_id',$hotel_id);
                
                $result_rate = $db->prepare('SELECT MIN(price) as min_price FROM pm_rate WHERE id_hotel = :hotel_id');
                $result_rate->bindParam(':hotel_id', $hotel_id);
                
                foreach($result_hotel as $i => $row){
                    $hotel_id = $row['id'];
                    $hotel_title = $row['title'];
                    $hotel_subtitle = $row['subtitle'];
                    
                    $hotel_alias = DOCBASE.$pages[9]['alias'].'/'.text_format($row['alias']);
                    
                    $min_price = 0;
                    if($result_rate->execute() !== false && $db->last_row_count() > 0){
                        $row = $result_rate->fetch();
                        $price = $row['min_price'];
                        if($price > 0) $min_price = $price;
                    } ?>
                    
                    <article class="col-sm-4 mb20" itemscope itemtype="http://schema.org/LodgingBusiness">
                        <a itemprop="url" href="<?php echo $hotel_alias; ?>" class="moreLink">
                            <?php
                            if($result_hotel_file->execute() !== false && $db->last_row_count() == 1){
                                $row = $result_hotel_file->fetch(PDO::FETCH_ASSOC);
                                
                                $file_id = $row['id'];
                                $filename = $row['file'];
                                $label = $row['label'];
                                
                                $realpath = SYSBASE.'medias/hotel/small/'.$file_id.'/'.$filename;
                                $thumbpath = DOCBASE.'medias/hotel/small/'.$file_id.'/'.$filename;
                                $zoompath = DOCBASE.'medias/hotel/big/'.$file_id.'/'.$filename;
                                
                                if(is_file($realpath)){
                                    $s = getimagesize($realpath); ?>
                                    <figure class="more-link">
                                        <div class="img-container lazyload md">
                                            <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                        </div>
                                        <div class="more-content">
                                            <h3 itemprop="name"><?php echo $hotel_title; ?></h3>
                                            <?php
                                            if($min_price > 0){ ?>
                                                <div class="more-descr">
                                                    <div class="price">
                                                        <?php echo $texts['FROM_PRICE']; ?>
                                                        <span itemprop="priceRange">
                                                            <?php echo formatPrice($min_price*CURRENCY_RATE); ?>
                                                        </span>
                                                    </div>
                                                    <small><?php echo $texts['PRICE'].' / '.$texts['NIGHT']; ?></small>
                                                </div>
                                                <?php
                                            } ?>
                                        </div>
                                        <div class="more-action">
                                            <div class="more-icon">
                                                <i class="fa fa-building"></i>
                                            </div>
                                        </div>
                                    </figure>
                                    <?php
                                }
                            } ?>
                        </a> 
                    </article>
                    <?php
                }
            } ?>
        </div>
    </div>
    <?php
    $activity_id = 0;
    $result_activity = $db->query('SELECT * FROM pm_activity WHERE lang = '.LANG_ID.' AND checked = 1 AND home = 1 ORDER BY rank');
    if($result_activity !== false){
        $nb_activities = $db->last_row_count();
        if($nb_activities > 0){ ?>
            <div class="hotBox mb30 mt5">
                <div class="container-fluid">
                    <div class="row">
                        <h2 class="text-center mt5 mb10"><?php echo $texts['FIND_ACTIVITIES_AND_TOURS']; ?></h2>
                        <?php
                        $activity_id = 0;
                        $result_activity_file = $db->prepare('SELECT * FROM pm_activity_file WHERE id_item = :activity_id AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                        $result_activity_file->bindParam(':activity_id',$activity_id);
                        foreach($result_activity as $i => $row){
                            $activity_id = $row['id'];
                            $activity_title = $row['title'];
                            $activity_alias = $row['title'];
                            $activity_subtitle = $row['subtitle'];
                            $min_price = $row['price'];
                            
                            $activity_alias = DOCBASE.$sys_pages['activities']['alias'].'/'.text_format($row['alias']); ?>
                            
                            <article class="col-sm-3 mb20" itemscope itemtype="http://schema.org/LodgingBusiness">
                                <a itemprop="url" href="<?php echo $activity_alias; ?>" class="moreLink">
                                    <?php
                                    if($result_activity_file->execute() !== false && $db->last_row_count() > 0){
                                        $row = $result_activity_file->fetch(PDO::FETCH_ASSOC);
                                        
                                        $file_id = $row['id'];
                                        $filename = $row['file'];
                                        $label = $row['label'];
                                        
                                        $realpath = SYSBASE.'medias/activity/small/'.$file_id.'/'.$filename;
                                        $thumbpath = DOCBASE.'medias/activity/small/'.$file_id.'/'.$filename;
                                        $zoompath = DOCBASE.'medias/activity/big/'.$file_id.'/'.$filename;
                                        
                                        if(is_file($realpath)){
                                            $s = getimagesize($realpath); ?>
                                            <figure class="more-link">
                                                <div class="img-container lazyload md">
                                                    <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                                </div>
                                                <div class="more-content">
                                                    <h3 itemprop="name"><?php echo $activity_title; ?></h3>
                                                </div>
                                                <div class="more-action">
                                                    <div class="more-icon">
                                                        <i class="fa fa-link"></i>
                                                    </div>
                                                </div>
                                            </figure>
                                            <?php
                                        }
                                    } ?>
                                </a> 
                            </article>
                            <?php
                        } ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    $result_destination = $db->query('SELECT * FROM pm_destination WHERE lang = '.LANG_ID.' AND checked = 1 AND home = 1 ORDER BY rank');
    if($result_destination !== false){
        $nb_destinations = $db->last_row_count();
        
        if($nb_destinations > 0){ ?>
                    
            <div class="container">
                <div class="row mb10">
                    <h2 class="text-center mt5 mb10"><?php echo $texts['TOP_DESTINATIONS']; ?></h2>
                    <?php
                    $destination_id = 0;
                    
                    $result_destination_file = $db->prepare('SELECT * FROM pm_destination_file WHERE id_item = :destination_id AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                    $result_destination_file->bindParam(':destination_id',$destination_id);
                    
                    $result_rate = $db->prepare('
                        SELECT MIN(ra.price) as min_price
                        FROM pm_rate as ra, pm_hotel as h, pm_destination as d
                        WHERE id_hotel = h.id
                            AND id_destination = d.id
                            AND id_destination = :destination_id');
                    $result_rate->bindParam(':destination_id', $destination_id);
                    
                    foreach($result_destination as $i => $row){
                        $destination_id = $row['id'];
                        $destination_name = $row['name'];
                        $destination_subtitle = $row['subtitle'];
                        
                        $destination_alias = DOCBASE.$sys_pages['booking']['alias'].'/'.text_format($row['alias']);
                        
                        $min_price = 0;
                        if($result_rate->execute() !== false && $db->last_row_count() > 0){
                            $row = $result_rate->fetch();
                            $price = $row['min_price'];
                            if($price > 0) $min_price = $price;
                        } ?>
                        
                        <article class="col-sm-4 mb20" itemscope itemtype="http://schema.org/LodgingBusiness">
                            <a itemprop="url" href="<?php echo $destination_alias; ?>" class="moreLink">
                                <?php
                                if($result_destination_file->execute() !== false && $db->last_row_count() == 1){
                                    $row = $result_destination_file->fetch(PDO::FETCH_ASSOC);
                                    
                                    $file_id = $row['id'];
                                    $filename = $row['file'];
                                    $label = $row['label'];
                                    
                                    $realpath = SYSBASE.'medias/destination/small/'.$file_id.'/'.$filename;
                                    $thumbpath = DOCBASE.'medias/destination/small/'.$file_id.'/'.$filename;
                                    $zoompath = DOCBASE.'medias/destination/big/'.$file_id.'/'.$filename;
                                    
                                    if(is_file($realpath)){
                                        $s = getimagesize($realpath); ?>
                                        <figure class="more-link">
                                            <div class="img-container lazyload md">
                                                <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                            </div>
                                            <div class="more-content">
                                                <h3 itemprop="name"><?php echo $destination_name; ?></h3>
                                                <?php
                                                if($min_price > 0){ ?>
                                                    <div class="more-descr">
                                                        <div class="price">
                                                            <?php echo $texts['FROM_PRICE']; ?>
                                                            <span itemprop="priceRange">
                                                                <?php echo formatPrice($min_price*CURRENCY_RATE); ?>
                                                            </span>
                                                        </div>
                                                        <small><?php echo $texts['PRICE'].' / '.$texts['NIGHT']; ?></small>
                                                    </div>
                                                    <?php
                                                } ?>
                                            </div>
                                            <div class="more-action">
                                                <div class="more-icon">
                                                    <i class="fa fa-link"></i>
                                                </div>
                                            </div>
                                        </figure>
                                        <?php
                                    }
                                } ?>
                            </a> 
                        </article>
                        <?php
                    } ?>
                </div>
            </div>
            <?php
        }
    }
    $result_article = $db->query('SELECT * FROM pm_article WHERE (id_page = '.$page_id.' OR home = 1) AND checked = 1 AND (publish_date IS NULL || publish_date <= '.time().') AND (unpublish_date IS NULL || unpublish_date > '.time().') AND lang = '.LANG_ID.' ORDER BY rank');
    if($result_article !== false){
        $nb_articles = $db->last_row_count();
        
        if($nb_articles > 0){ ?>
            <div class="container mt10">
                <div class="row">
                    <div class="clearfix">
                        <?php
                        $article_id = 0;
                        $result_article_file = $db->prepare('SELECT * FROM pm_article_file WHERE id_item = :article_id AND checked = 1 AND lang = '.DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY rank LIMIT 1');
                        $result_article_file->bindParam(':article_id', $article_id);
                        foreach($result_article as $i => $row){
                            $article_id = $row['id'];
                            $article_title = $row['title'];
                            $article_alias = $row['alias'];
                            $char_limit = ($i == 0) ? 1200 : 500;
                            $article_text = strtrunc(strip_tags($row['text'], '<p><br>'), $char_limit, true, '');
                            $article_page = $row['id_page'];
                            
                            if(isset($pages[$article_page])){
                            
                                $article_alias = DOCBASE.$pages[$article_page]['alias'].'/'.text_format($article_alias); ?>
                                
                                <article id="article-<?php echo $article_id; ?>" class="col-sm-<?php echo ($i == 0) ? 12 : 4; ?>" itemscope itemtype="http://schema.org/Article">
                                    <div class="row">
                                        <a itemprop="url" href="<?php echo $article_alias; ?>" class="moreLink">
                                            <div class="col-sm-<?php echo ($i == 0) ? 8 : 12; ?> mb20">
                                                <?php
                                                if($result_article_file->execute() !== false && $db->last_row_count() == 1){
                                                    $row = $result_article_file->fetch(PDO::FETCH_ASSOC);
                                                    
                                                    $file_id = $row['id'];
                                                    $filename = $row['file'];
                                                    $label = $row['label'];
                                                    
                                                    $realpath = SYSBASE.'medias/article/big/'.$file_id.'/'.$filename;
                                                    $thumbpath = DOCBASE.'medias/article/big/'.$file_id.'/'.$filename;
                                                    $zoompath = DOCBASE.'medias/article/big/'.$file_id.'/'.$filename;
                                                    
                                                    if(is_file($realpath)){
                                                        $s = getimagesize($realpath); ?>
                                                        <figure class="more-link">
                                                            <div class="img-container lazyload xl">
                                                                <img alt="<?php echo $label; ?>" data-src="<?php echo $thumbpath; ?>" itemprop="photo" width="<?php echo $s[0]; ?>" height="<?php echo $s[1]; ?>">
                                                            </div>
                                                            <div class="more-action">
                                                                <div class="more-icon">
                                                                    <i class="fa fa-link"></i>
                                                                </div>
                                                            </div>
                                                        </figure>
                                                        <?php
                                                    }
                                                } ?>
                                            </div>
                                            <div class="col-sm-<?php echo ($i == 0) ? 4 : 12; ?>">
                                                <div class="text-overflow">
                                                    <h3 itemprop="name"><?php echo $article_title; ?></h3>
                                                    <?php echo $article_text; ?>
                                                    <div class="more-btn">
                                                        <span class="btn btn-primary"><?php echo $texts['READMORE']; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </article>
                                <?php
                            }
                        } ?>
                    </div>
                </div>
            </div>
            <?php
        }
    } ?>
</section>
