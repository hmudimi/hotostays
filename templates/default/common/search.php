<?php
debug_backtrace() || die ('Direct access not permitted');
if(isset($_SESSION['user']) && $_GET['action'] == 'logout') unset($_SESSION['user']);

$max_adults_search = 30;
$max_children_search = 10;
$max_rooms_search = 4;
if(!isset($_SESSION['destination_id'])) $_SESSION['destination_id'] = 0;
if(!isset($destination_name)) $destination_name = '';

if(!isset($_SESSION['num_adults']))
    $_SESSION['num_adults'] = (isset($_SESSION['book']['adults'])) ? $_SESSION['book']['adults'] : 1;
if(!isset($_SESSION['num_children']))
    $_SESSION['num_children'] = (isset($_SESSION['book']['children'])) ? $_SESSION['book']['children'] : 0;
    if(!isset($_SESSION['num_rooms']))
    $_SESSION['num_rooms'] = (isset($_SESSION['book']['room'])) ? $_SESSION['book']['rooms'] : 0;
    if(isset($_POST['num_adults'])) echo $_SESSION['num_adults'] = (int) preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['num_adults']);
    if(isset($_POST['num_children'])) echo $_SESSION['num_children'] = (int) preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['num_children']);
$from_date = (isset($_SESSION['from_date'])) ? $_SESSION['from_date'] : '';
$to_date = (isset($_SESSION['to_date'])) ? $_SESSION['to_date'] : ''; ?>
<style>#myUL{
    display:none;
    margin-top: 2px;
    padding-left: 0px;
}#myUL > li{
    margin-top: 2px;
    float: left;
    list-style: none;
    width: 100%;
    background: #fff;
    padding: 8px;
    cursor:pointer;
}#myUL > li > a{
    color:#000;
}
.room_modal {
    position: absolute;
    top: 50px;
    left: 0;
    min-width: 390px !important;
    height: auto;
    background: #f2f2f2;
    box-shadow: 1px 2px 5px #000;
    z-index: 999;
    color: #000;
    display: none;
}
.pagination {
    margin: 1px 2px;
}
.input-group-addon {
    display: block;
    width: 100%;
}
.room_full {
    width: 100%;
}
</style>
<form action="<?php echo DOCBASE.$sys_pages['booking']['alias']; ?>" method="post" class="booking-search">
    <?php
    if(isset($hotel_id)){ ?>
        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
        <?php
    } ?>
    <div class="row">



        <?php
        $nb_search_destinations = 0;
        $result_search_destination = $db->query('SELECT * FROM pm_destination WHERE checked = 1 AND lang = '.LANG_ID);
        if($result_search_destination !== false){
            $nb_search_destinations = $db->last_row_count();
            if($nb_search_destinations > 0){ ?>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="input-wrapper form-inline">
                        <i class="fas fa-fw fa-map-marker"></i>
                        <div class="input-group">
                            <?php
                            $result_search_destination = $result_search_destination->fetchAll(PDO::FETCH_ASSOC);
                            if(count($result_search_destination) > 10){ ?>
							<label for="destination_id">destination_id</label>
                                <input type="text" name="destination_name" class="form-control liveSearch" data-wrapper="result-destinations" data-target="destination_id" data-url="<?php echo getFromTemplate('common/search_destinations.php'); ?>" value="<?php echo $destination_name; ?>" placeholder="<?php echo $texts['DESTINATION']; ?>">
                                <input type="hidden" class="form-control" name="destination_id" id="destination_id" value="<?php echo $_SESSION['destination_id']; ?>">
                                <?php
                            }else{ ?>
                            <?php
                            $selecteda='';
                                    foreach($result_search_destination as $row){
                                        $selectd = (isset($_SESSION['destination_id']) && $_SESSION['destination_id'] == $row['id']) ? $row['name'] : '';
                                        if($selectd !=''){
                                            $selecteda=$selectd;
                                        }
                                    }
                                    ?>
                            <input type="text" class="form-control" id="myInput" value="<?php echo $selecteda; ?>" onkeyup="myFunction()" placeholder="CITY / AREA " title="Type in a name"   autocomplete="off" >
                                <select name="destination_id" id="destination_id" class="form-control " style="display:none">
                                    <option value="0"><?php echo $texts['DESTINATION']; ?></option>
                                    <?php
                                    foreach($result_search_destination as $row){
                                        $selected = (isset($_SESSION['destination_id']) && $_SESSION['destination_id'] == $row['id']) ? ' selected="selected"' : '';
                                        echo '<option value="'.$row['id'].'"'.$selected.'>'.$row['name'].'</option>';
                                    } ?>
                                </select>
                                <?php
                            } ?>
                        </div>
                    </div>
                    <ul id="myUL">
<?php 
                        foreach($result_search_destination as $row){
                            echo '<li onclick="setdestination('.$row['id'].')"><a id="a'.$row['id'].'">'.$row['name'].'</a></li>';
                        } ?>

</ul>
                </div>
                <?php
           }
        } ?>
        <div class="col-md-<?php echo ($nb_search_destinations > 0) ? 4 : 7; ?> col-sm-<?php echo ($nb_search_destinations > 0) ? 6 : 12; ?> col-xs-12">
            <div class="input-wrapper datepicker-wrapper form-inline">
                <i class="fas fa-fw fa-calendar hidden-xs"></i>
                <div class="input-group from-date">
                    <input type="text" class="form-control text-right" id="from_picker" name="from_date" value="<?php echo $from_date; ?>" placeholder="<?php echo $texts['CHECK_IN']; ?>">
                </div>
                <i class="fas fa-fw fa-long-arrow-alt-right"></i>
                <div class="input-group to-date">
                    <input type="text" class="form-control" id="to_picker" name="to_date" value="<?php echo $to_date; ?>" placeholder="<?php echo $texts['CHECK_OUT']; ?>">
                </div>
            </div>
            <div class="field-notice" rel="dates"></div>
        </div>
        <!-- <div class="col-md-2 col-sm-6 col-xs-6">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon"><?php echo $texts['ADULTS']; ?></div>
                    <select name="num_adults" class="selectpicker form-control">
                    <option value="" disabled>Select Adults</option>
                        <?php
                        // for($i = 1; $i <= $max_adults_search; $i++){
                        //     $select = ($_SESSION['num_adults'] == $i) ? ' selected=\"selected\"' : '';
                        //     echo '<option value=\"'.$i.'\"'.$select.'>'.$i.'</option>';
                        // } ?>
                    </select>
                </div>
            </div>
        </div> -->
        <?php  if(strpos($_SERVER['REQUEST_URI'], '/') !== 1) { ?>
        <div class="col-md-4 col-sm-12 col-xs-12">
            <div class="form-group" style="position:relative">
                <div class="input-group room_full">
                    <div class="input-group-addon">ROOMS & GUESTS</div>
                    <!-- <select name="num_children" class="selectpicker form-control">
                    <option value="" disabled>Select Childrens</option>
                        <?php
                        // for($i = 0; $i <= $max_rooms_search; $i++){
                        //     $select = ($_SESSION['num_children'] == $i) ? ' selected=\"selected\"' : '';
                        //     echo '<option value=\"'.$i.'\"'.$select.'>'.$i.'</option>';
                        // } ?>
                    </select> -->
                    <div id="room_modal" class="room_modal">
                    <p class="text-bold text-default"><?php echo $texts['ADULTS']; ?> (12y+)</p>
                        <nav aria-label="...">
                            <input id="num_adults" type="hidden" name="num_adults" value="" />
                            <ul id="adult_page" class="pagination">
                            <?php
                                for($i = 1; $i <= 10; $i++){
                                    $select = ($_SESSION['num_adults'] == $i) ? 'active' : '';
                                    echo '<li class="page-item '.$select.'"><a class="page-link" href="#">'.$i.'</a></li>';
                                } ?>
                                <!-- <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link">2</a></li>
                                <li class="page-item"><a class="page-link">3</a></li>
                                <li class="page-item"><a class="page-link">4</a></li>
                                <li class="page-item"><a class="page-link">5</a></li>
                                <li class="page-item"><a class="page-link">6</a></li>
                                <li class="page-item"><a class="page-link">7</a></li>
                                <li class="page-item"><a class="page-link">8</a></li>
                                <li class="page-item"><a class="page-link">9</a></li>
                                <li class="page-item"><a class="page-link">10</a></li> -->
                            </ul>
                        </nav>
                        <p class="text-bold text-default"><?php echo $texts['CHILDREN']; ?> (1y-12y)</p>
                        <nav aria-label="...">
                        <input id="num_children" type="hidden" name="num_children" value="" />
                            <ul id="children_page" class="pagination">
                            <?php
                                for($i = 0; $i <= 10; $i++){
                                    $select = ($_SESSION['num_children'] == $i) ? 'active' : '';
                                    echo '<li class="page-item '.$select.'"><a class="page-link" href="#">'.$i.'</a></li>';
                                } ?>
                                <!-- <li class="page-item active"><a class="page-link">0</a></li>
                                <li class="page-item"><a class="page-link">1</a></li>
                                <li class="page-item"><a class="page-link">2</a></li>
                                <li class="page-item"><a class="page-link">3</a></li>
                                <li class="page-item"><a class="page-link">4</a></li>
                                <li class="page-item"><a class="page-link">5</a></li>
                                <li class="page-item"><a class="page-link">6</a></li>
                                <li class="page-item"><a class="page-link">7</a></li>
                                <li class="page-item"><a class="page-link">8</a></li>
                                <li class="page-item"><a class="page-link">9</a></li>
                                <li class="page-item"><a class="page-link">10</a></li> -->
                            </ul>
                        </nav>
                    </div>
                </div>

            </div>
        </div>
      <?php  } ?>
        <div class="col-md-1 col-sm-12 col-xs-12">
            <div class="form-group">
                <button class="myButton" type="submit" name="check_availabilities">Search..</button>
            </div>
        </div>
    </div>
    <?php
    if($page['page_model'] == 'booking'){ ?>
        <div class="row mb5 mt10">
            <?php
            $result_rate = $db->query('SELECT MAX(price) as max_price FROM pm_rate');
            if($result_rate !== false && $db->last_row_count() > 0){
                $row = $result_rate->fetch();
                $max_price = $row['max_price']*($_SESSION['num_children']+$_SESSION['num_adults']);
                if($max_price > 0){
                    if(!isset($price_min) || is_null($price_min)) $price_min = 0;
                    if(!isset($price_max) || is_null($price_max)) $price_max = $max_price; ?>
                    <div class="col-sm-6">
                        <label class="col-sm-3 control-label" for="hotel_class"><?php echo $texts['YOUR_BUDGET']; ?></label>
                        <div class="col-sm-9">
                            <div class="nouislider-wrapper">
                                <div class="nouislider" data-min="0" data-max="<?php echo number_format(ceil($max_price)*CURRENCY_RATE, 0, '.', ''); ?>" data-start="<?php echo '['.number_format(floor($price_min)*CURRENCY_RATE, 0, '.', '').','.number_format(ceil($price_max)*CURRENCY_RATE, 0, '.', '').']'; ?>" data-step="10" data-direction="<?php echo RTL_DIR; ?>" data-input="price_range"></div>
                                <?php echo $texts['PRICE'].' / '.$texts['NIGHT']; ?> : <?php echo CURRENCY_SIGN; ?> <input type="text" name="price_range" class="slider-target" id="price_range" value="" readonly="readonly" size="15">
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            if(!isset($class_min) || is_null($class_min)) $class_min = 0;
            if(!isset($class_max) || is_null($class_max)) $class_max = 5; ?>
            <div class="col-sm-6">
                <label class="col-sm-3 control-label" for="hotel_class"><?php echo $texts['HOTEL_CLASS']; ?></label>
                <div class="col-sm-9">
                    <div class="nouislider-wrapper">
                        <div class="nouislider" data-min="0" data-max="5" data-start="<?php echo '['.$class_min.','.$class_max.']'; ?>" data-step="1" data-direction="<?php echo RTL_DIR; ?>" data-input="class_range"></div>
                        <?php echo $texts['STARS']; ?> : <input type="text" name="class_range" class="slider-target" id="class_range" value="" readonly="readonly" size="5">
                    </div>
                </div>
            </div>
        </div>
        <?php
    } ?>
</form>
<script>
function myFunction() {
    var input, filter, ul, li, a, i, txtValue,myUL;
    input = document.getElementById("myInput");

        myUL = document.getElementById("myInput");

    filter = input.value.toUpperCase();

    if(filter.length > 0){

    document.getElementById("myUL").style.display = 'block';
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}else {
    document.getElementById("myUL").style.display = 'none';
}
}

function  setdestination(v){
    document.getElementById("myUL").style.display = 'none';
    document.getElementById("myInput").value = document.getElementById("a"+v).innerHTML;
    document.getElementById("destination_id").value = v;
}
function selCity(){
    document.getElementById("destination_id").style.display = "block";
}
var show = false;
$(document).ready(function(){
    $('.room_full').click(function(e){
        $('#room_modal').css('display', 'block');
        show = true;
        e.stopPropagation();
    });
    $('body').click(function(e) {
        if(show) $('#room_modal').css('display', 'none');
    });
    $("#adult_page li").click(function(e){
        $('#adult_page').find('li').removeClass("active");
        $(e.currentTarget).addClass("active");
        $('#num_adults').val($(e.currentTarget).find('a').html());
    })
    $("#children_page li").click(function(e){
        $('#children_page').find('li').removeClass("active");
        $(e.currentTarget).addClass("active");
        $('#num_children').val($(e.currentTarget).find('a').html());
    })
})
</script>
