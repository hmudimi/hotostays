<?php
/**
 * Template of the module listing
 */
debug_backtrace() || die ('Direct access not permitted');
 
// Action to perform
$action = (isset($_GET['action'])) ? htmlentities($_GET['action'], ENT_QUOTES, 'UTF-8') : '';

if($action != '' && defined('DEMO') && DEMO == 1){
    $action = '';
    $_SESSION['msg_error'][] = 'This action is disabled in the demo mode';
}

// Item ID
$id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : 0;

// current page
if(isset($_GET['offset']) && is_numeric($_GET['offset'])) $offset = $_GET['offset'];
else $offset = 0;

// Items per page
if(isset($_GET['limit']) && is_numeric($_GET['limit'])){
    $limit = $_GET['limit'];
    $offset = 0;
}
else $limit = 20;

$_SESSION['limit'] = $limit;

$_SESSION['offset'] = $offset;

// Inclusions
require_once(SYSBASE.'includes/fn_list.php');

if($db !== false){
    // Initializations
    $total = 0;
    $total_page = 0;
    $q_search = '';
    $result = false;
    $referer = DIR.'index.php?view=list';

    $order = 'rank';

    // Getting languages
    if(MULTILINGUAL){
        $result_lang = $db->query('SELECT id, title FROM pm_lang WHERE id != '.DEFAULT_LANG.' AND checked = 1');
        if($result_lang !== false)
            $total_lang = $db->last_row_count();
    }
    
    if(isset($_POST['search'])){
        $q_search = htmlentities($_POST['q_search'], ENT_QUOTES, 'UTF-8');
        $offset = 0;
    }

    // Getting items in the database
    $condition = '';

    if(MULTILINGUAL) $condition .= ' lang = '.DEFAULT_LANG;
    
    if(!in_array($_SESSION['user']['type'], array('administrator', 'manager', 'editor')) && db_column_exists($db, 'pm_'.MODULE, 'users')){
        if($condition != '') $condition .= ' AND';
        $condition .= ' users REGEXP \'(^|,)'.$_SESSION['user']['id'].'(,|$)\'';
    }
    
    $query_search = db_getRequestSelect($db, 'pm_'.MODULE, getSearchFieldsList($cols), $q_search, $condition, $order.' '.$sort);

    $result_total = $db->query($query_search);
    if($result_total !== false)
        $total = $db->last_row_count();
        
    if($limit > 0) $query_search .= ' LIMIT '.$limit.' OFFSET '.$offset;

    $result = $db->query($query_search);
    if($result !== false)
        $total_page = $db->last_row_count();
        
    if(empty($_SESSION['msg_error'])){
        
        if(in_array('delete', $permissions) || in_array('all', $permissions)){

            // Item deletion
            if($action == 'delete' && $id > 0 && check_token($referer, 'list', 'get'))
                delete_item($db, $id);
        }
    }
}

$_SESSION['module_referer'] = MODULE;
$csrf_token = get_token('list'); ?>
<!DOCTYPE html>
<head>
    <?php include(SYSBASE.'includes/inc_header_list.php'); ?>
</head>
<body>
    <div id="wrapper">
        <?php include(SYSBASE.'includes/inc_top.php'); ?>
        <div id="page-wrapper">
            <div class="page-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 clearfix">
                            <h1 class="pull-left"><i class="fas fa-fw fa-<?php echo ICON; ?>"></i> <?php echo TITLE_ELEMENT; ?></h1>
                            <div class="pull-left text-right">
                                &nbsp;&nbsp;
                                <?php
                                if(in_array('add', $permissions) || in_array('all', $permissions)){ ?>
                                    <a href="index.php?view=form&id=0" class="btn btn-primary mt15 mb15">
                                        <i class="fas fa-fw fa-plus-circle"></i> <?php echo $texts['NEW']; ?>
                                    </a>
                                    <?php
                                }
                                if(is_file('custom_nav.php')) include('custom_nav.php'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="alert-container">
                    <div class="alert alert-success alert-dismissable"></div>
                    <div class="alert alert-warning alert-dismissable"></div>
                    <div class="alert alert-danger alert-dismissable"></div>
                </div>
                <?php
                if($db !== false){
                    if(!in_array('no_access', $permissions)){ ?>
                        <form id="form" action="index.php?view=list" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"/>
                            <div class="panel panel-default">
                                <div class="panel-heading form-inline clearfix">
                                    <div class="row">
                                        <div class="col-md-6 text-left">
                                            <div class="form-inline">
                                                <input type="text" name="q_search" value="<?php echo $q_search; ?>" class="form-control input-sm" placeholder="<?php echo $texts['SEARCH']; ?>..."/>
                                                <?php //displayFilters($filters); ?>
                                                <button class="btn btn-default btn-sm" type="submit" id="search" name="search"><i class="fas fa-fw fa-search"></i> <?php echo $texts['SEARCH']; ?></button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="fas fa-fw fa-th-list"></i> <?php echo $texts['DISPLAY']; ?></div>
                                                <select class="select-url form-control input-sm">
                                                    <?php
                                                    echo ($limit != 20) ? '<option value="index.php?view=list&limit=20">20</option>' : '<option selected="selected">20</option>';
                                                    echo ($limit != 50) ? '<option value="index.php?view=list&limit=50">50</option>' : '<option selected="selected">50</option>';
                                                    echo ($limit != 100) ? '<option value="index.php?view=list&limit=100">100</option>' : '<option selected="selected">100</option>'; ?>
                                                </select>
                                            </div>
                                            <?php
                                            if($limit > 0){
                                                $nb_pages = ceil($total/$limit);
                                                if($nb_pages > 1){ ?>
                                                    <div class="input-group">
                                                        <div class="input-group-addon"><?php echo $texts['PAGE']; ?></div>
                                                        <select class="select-url form-control input-sm">
                                                            <?php

                                                            for($i = 1; $i <= $nb_pages; $i++){
                                                                $offset2 = ($i-1)*$limit;
                                                                
                                                                if($offset2 == $offset)
                                                                    echo '<option value="" selected="selected">'.$i.'</option>';
                                                                else
                                                                    echo '<option value="index.php?view=list&offset='.$offset2.'">'.$i.'</option>';
                                                            } ?>
                                                        </select>
                                                    </div>
                                                    <?php
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped" id="listing_base">
                                            <thead>
                                                <tr class="nodrop nodrag">
                                                    <?php
                                                    foreach($cols as $col){ ?>
                                                        <th>
                                                            <a href="index.php?view=list&order=<?php echo $col->getName(); ?>&sort=<?php echo ($order == $col->getName()) ? $rsort : 'asc'; ?>">
                                                                <?php echo $col->getLabel(); ?>
                                                                <i class="fas fa-fw fa-sort<?php if($order == $col->getName()) echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <?php
                                                    }
                                                    if(count($cols) == 0){
                                                        $type_module = 'file';
                                                        if(NB_FILES > 0){ ?>
                                                            <th><?php echo $texts['FILE']; ?></th>
                                                            <th><?php echo $texts['LABEL']; ?></th>
                                                            <?php
                                                        }
                                                    }
                                                    if(DATES){ ?>
                                                        <th width="160">
                                                            <a href="index.php?view=list&order=add_date&sort=<?php echo ($order == 'add_date') ? $rsort : 'asc'; ?>">
                                                                <?php echo $texts['ADDED_ON']; ?> <i class="fas fa-fw fa-sort<?php if($order == 'add_date') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <th width="160">
                                                            <a href="index.php?view=list&order=edit_date&sort=<?php echo ($order == 'edit_date') ? $rsort : 'asc'; ?>">
                                                                <?php echo $texts['UPDATED_ON']; ?> <i class="fas fa-fw fa-sort<?php if($order == 'edit_date') echo '-'.$sort_class; ?>"></i>
                                                            </a>
                                                        </th>
                                                        <?php
                                                    } ?>
                                                    <th width="130"><?php echo $texts['ACTIONS']; ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if($result !== false){
                                                    foreach($result as $i => $row){
                                                        
                                                        $id = $row['id'];
                                                        $cols = getColsValues($db, $row, $i, $cols); ?>
                                                        
                                                        <tr id="item_<?php echo $id ?>">
                                                            
                                                            <?php
                                                            foreach($cols as $col){
                                                                echo '<td';
                                                                $type = $col->getType();
                                                                if($type == 'date' || $type == 'date') echo ' class="text-center"';
                                                                if($type == 'price') echo ' class="text-right"';
                                                                echo '>'.$col->getValue($i).'</td>';
                                                            }
                                                            if(DATES){
                                                                $add_date = (is_null($row['add_date'])) ? '-' : strftime(DATE_FORMAT.' '.TIME_FORMAT, $row['add_date']);
                                                                $edit_date = (is_null($row['edit_date'])) ? '-' : strftime(DATE_FORMAT.' '.TIME_FORMAT, $row['edit_date']); ?>
                                                                <td class="text-center">
                                                                    <?php echo $add_date; ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php echo $edit_date; ?>
                                                                </td>
                                                                <?php
                                                            } ?>
                                                            <td class="text-center">
                                                                <?php
                                                                if(in_array('edit', $permissions) || in_array('all', $permissions) || in_array('view', $permissions)){ ?>
                                                                    <a class="tips" href="index.php?view=form&id=<?php echo $id; ?>" title="<?php echo $texts['EDIT']; ?>"><i class="fas fa-fw fa-edit"></i></a>
                                                                    <?php
                                                                }
                                                                if(in_array('delete', $permissions) || in_array('all', $permissions)){ ?>
                                                                    <a class="tips" href="javascript:if(confirm('<?php echo $texts['DELETE_CONFIRM2']; ?>')) window.location = 'index.php?view=list&id=<?php echo $id; ?>&csrf_token=<?php echo $csrf_token; ?>&action=delete';" title="<?php echo $texts['DELETE']; ?>"><i class="fas fa-fw fa-trash-alt text-danger"></i></a>
                                                                    <?php
                                                                } ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
                                    if($total == 0){ ?>
                                        <div class="text-center mt20 mb20">- <?php echo $texts['NO_ELEMENT']; ?> -</div>
                                        <?php
                                    } ?>
                                </div>
                            </div>
                            <?php
                            if(is_file('custom_list.php')) include('custom_list.php'); ?>
                        </form>
                        <?php
                    }else echo '<p>'.$texts['ACCESS_DENIED'].'</p>';
                } ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$_SESSION['redirect'] = false;
$_SESSION['msg_error'] = array();
$_SESSION['msg_success'] = array();
$_SESSION['msg_notice'] = array(); ?>
