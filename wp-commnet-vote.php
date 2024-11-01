<?php
/*
Plugin Name: WP-Comment-vote
Plugin URI: http://fatesinger.com/659
Description: Add an ajax vote for your comment
Version: 0.0.1
Author: Bigfa
Author URI: http://fatesinger.com
*/

define('WCV_VERSION', '0.0.5');
define('WCV_URL', plugins_url('', __FILE__));
define('WCV_PATH', dirname( __FILE__ ));
define('WCV_ADMIN_URL', admin_url());
add_action('wp_ajax_nopriv_do_comment_rate', 'do_comment_rate');
add_action('wp_ajax_do_comment_rate', 'do_comment_rate');
function do_comment_rate(){
    if (!isset($_POST["comment_id"]) || !isset($_POST["event"])) {

        $data = array("status"=>500,"data"=>'?');
        echo json_encode($data);

    } else {

        $comment_id = $_POST["comment_id"];
        $event = $_POST["event"];
        $expire = time() + 99999999;
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost
        //setcookie('comment_rated_'.$comment_id,$comment_id,$expire,'/',$domain,false);
        $_comment_up = get_comment_meta($comment_id,'_comment_up',true);
        $_comment_down = get_comment_meta($comment_id,'_comment_down',true);
        if ($event == "up") {

            if (!$_comment_up) {

                update_comment_meta($comment_id, '_comment_up', 1);

            } else {

                update_comment_meta($comment_id, '_comment_up', ($_comment_up + 1));
            }
        } else {
            if (!$_comment_down || $_comment_down == '' || !is_numeric($_comment_down)) {

                update_comment_meta($comment_id, '_comment_down', 1);

            } else {

                update_comment_meta($comment_id, '_comment_down', ($_comment_down + 1));

            }

        }
        $data = array();
        $_comment_up = get_comment_meta($comment_id,'_comment_up',true);
        $_comment_down = get_comment_meta($comment_id,'_comment_down',true);
        $data = array("status"=>200,"data"=>array("event"=>$event,"_comment_up"=>$_comment_up,"_comment_down"=>$_comment_down));
        echo json_encode($data);
    }
    die;
}
function ludou_comment_add_at( $comment_text, $comment = '') {
    $comment_id = $comment->comment_ID;
    $_comment_up = get_comment_meta($comment_id,'_comment_up',true);
    $_comment_down = get_comment_meta($comment_id,'_comment_down',true);
    if( ($_comment_down - $_comment_up) > 5) {
        $comment_text = '<a class="displayratingcmt" title="点击查看内容" href="javascript:void(0);">**评分过低，内容被隐藏**</a><div class="comment-rating-bad"><p>
'. $comment_text.'</p></div>' ;
    } else if(($_comment_up - $_comment_down ) > 5){
        $comment_text = '<div class="comment-rating-good"><p>'.$comment_text.'</p></div>';
    }else if(($_comment_up - $_comment_down ) < 5 && $_comment_down > 5 && $_comment_up > 5 && ($_comment_down - $_comment_up) < 5){
        $comment_text = '<div class="comment-rating-debated"><p>'.$comment_text.'</p></div>';
    }else{
        $comment_text = $comment_text;
    }

    return $comment_text.comment_rate($comment_id,false);
}
add_filter( 'comment_text' , 'ludou_comment_add_at', 20, 2);

function comment_rate($comment_ID = 0,$echo = true){

    $_comment_up = get_comment_meta($comment_ID,'_comment_up',true) ? get_comment_meta($comment_ID,'_comment_up',true) : 0;
    $_comment_down = get_comment_meta($comment_ID,'_comment_down',true) ? get_comment_meta($comment_ID,'_comment_down',true) : 0 ;
    $done = "";
    if (isset($_COOKIE['comment_rated_'.$comment_ID])) $done = " rated";
    $content = '<p class="comment--like'.$done.'" data-commentid="'.$comment_ID.'"><a href="javascript:;" data-event="up"><i class="cmt-font icon-arrowup"></i><em class="count">'.$_comment_up.'</em></a><a href="javascript:;" data-event="down"><i class="cmt-font icon-arrowdown"></i><em class="count">'.$_comment_down.'</em></a></p>';

    if ($echo) {

        echo $content;

    } else {

        return $content;

    }

}

add_action('delete_comment', 'delete_comment_ratings_fields');
function delete_comment_ratings_fields($comment_ID) {
    global $wpdb;
    delete_comment_meta($comment_ID, '_comment_up');
    delete_comment_meta($comment_ID, '_comment_down');
}

function wcv_css_url($css_url){
    return WCV_URL . "/static/css/{$css_url}.css";
}

function wcv_js_url($js_url){
    return WCV_URL . "/static/js/{$js_url}.js";
}

function wcv_scripts(){
    wp_enqueue_style( 'wcv', wcv_css_url('style'), array(), WCV_VERSION );
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'wcv',  wcv_js_url('index'), array(), WCV_VERSION );
    wp_localize_script( 'wcv', 'wcv_ajax_url', WCV_ADMIN_URL . "admin-ajax.php");
}
add_action('wp_enqueue_scripts', 'wcv_scripts', 20, 1);
?>